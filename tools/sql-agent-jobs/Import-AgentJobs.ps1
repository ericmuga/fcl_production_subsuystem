<#
.SYNOPSIS
  Recreate SQL Server Agent jobs exported by Export-AgentJobs.ps1 on another server.

.DESCRIPTION
  1. Preflight on the target: SQL Agent running, owner login, linked servers, databases, proxies, operators, and
     old server / database names still inside the step commands.
  2. Creates each job in its own transaction through the msdb procedures (sp_add_job, sp_add_jobstep,
     sp_add_jobschedule, sp_add_jobserver). Values are passed as parameters, so step text is copied exactly.
  3. Writes import-<server>-<time>.sql (what was or would be run) and import-<server>-<time>.log.

  -WhatIf runs the preflight and writes the script only. Jobs are created DISABLED unless -KeepEnabled is given,
  so the source and the target never run the same job at the same time; enable them at cutover.

.PARAMETER Map
  Text replacements applied to step commands, step server, database names and output files, e.g.
  @{ 'fcl-main20' = 'fcl-main30'; '[FCL-WMS]' = '[FCL-WMS2]' }. Plain text, case-insensitive.

.PARAMETER CreateLinkedServers
  Create linked servers the jobs need that are missing on the target, from linked-servers.json. Mappings that use the
  caller's own credentials are created; mappings with a remote login are listed for a DBA (passwords are not exported).

.EXAMPLE
  .\Import-AgentJobs.ps1 -Server fcl-main30 -InDir D:\agent-jobs\fcl-main20 -Owner 'FARMERSCHOICE\svc_sqlagent' -WhatIf
.EXAMPLE
  .\Import-AgentJobs.ps1 -Server fcl-main30 -InDir D:\agent-jobs\fcl-main20 -Job 'Import*' -Owner 'FARMERSCHOICE\svc_sqlagent' -CreateLinkedServers
#>
[CmdletBinding(SupportsShouldProcess)]
param(
    [Parameter(Mandatory)][string]$Server,
    [Parameter(Mandatory)][string]$InDir,
    [string[]]$Job = @('*'),
    [string]$Owner,
    [hashtable]$Map = @{},
    [switch]$KeepEnabled,
    [switch]$Overwrite,
    [switch]$CreateLinkedServers,
    [switch]$Force,
    [hashtable]$ProviderMap = @{ 'SQLNCLI' = 'MSOLEDBSQL'; 'SQLNCLI10' = 'MSOLEDBSQL'; 'SQLNCLI11' = 'MSOLEDBSQL' },
    [pscredential]$Credential
)
$ErrorActionPreference = 'Stop'
# -WhatIf is handled here (preflight + script only); built-in cmdlets must still run normally.
$DryRun = [bool]$WhatIfPreference
$WhatIfPreference = $false
. (Join-Path $PSScriptRoot 'AgentJobCommon.ps1')

$stamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$safeServer = $Server -replace '[\\/:*?"<>|]', '_'
$scriptFile = Join-Path $InDir "import-$safeServer-$stamp.sql"
$logFile = Join-Path $InDir "import-$safeServer-$stamp.log"
$script = New-Object System.Collections.Generic.List[string]
function Log([string]$m, [string]$level = 'INFO') { $line = "$(Get-Date -Format 'HH:mm:ss') [$level] $m"; Add-Content -Path $logFile -Value $line; if ($level -eq 'ERROR') { Write-Host $line -ForegroundColor Red } elseif ($level -eq 'WARN') { Write-Host $line -ForegroundColor Yellow } else { Write-Host $line } }

function Apply-Map([string]$text) {
    if ($null -eq $text) { return $null }
    foreach ($k in $Map.Keys) { $text = [regex]::Replace($text, [regex]::Escape($k), ($Map[$k] -replace '\$', '$$$$'), 'IgnoreCase') }
    return $text
}

# Execute a msdb procedure with parameters, and append its T-SQL rendering to the review script.
function Exec-Proc($conn, $tx, [string]$proc, [System.Collections.Specialized.OrderedDictionary]$p, [string]$outParam) {
    $render = "EXEC $proc " + (($p.Keys | ForEach-Object { if ($_ -eq $outParam) { "@$_ = @jobId OUTPUT" } else { "@$_ = $(ConvertTo-SqlLiteral $p[$_])" } }) -join ', ') + ';'
    $script.Add($render)
    if ($DryRun) { return $null }
    $cmd = $conn.CreateCommand(); $cmd.Transaction = $tx; $cmd.CommandType = 'StoredProcedure'; $cmd.CommandText = $proc; $cmd.CommandTimeout = 120
    foreach ($k in $p.Keys) {
        if ($k -eq $outParam) { $o = $cmd.Parameters.Add("@$k", [System.Data.SqlDbType]::UniqueIdentifier); $o.Direction = 'Output'; continue }
        [void]$cmd.Parameters.AddWithValue("@$k", $(if ($null -eq $p[$k]) { [DBNull]::Value } else { $p[$k] }))
    }
    [void]$cmd.ExecuteNonQuery()
    if ($outParam) { return $cmd.Parameters["@$outParam"].Value }
}

$files = Get-ChildItem (Join-Path $InDir 'jobs') -Filter *.json
$docs = @($files | ForEach-Object { Get-Content $_.FullName -Raw -Encoding UTF8 | ConvertFrom-Json } | Where-Object { $n = $_.job.name; ($Job | Where-Object { $n -like $_ }).Count -gt 0 })
if (-not $docs) { throw "No exported jobs in $InDir match $($Job -join ', ')." }
$lsDoc = $null
if (Test-Path (Join-Path $InDir 'linked-servers.json')) { $lsDoc = Get-Content (Join-Path $InDir 'linked-servers.json') -Raw -Encoding UTF8 | ConvertFrom-Json }
Log "Import of $($docs.Count) job(s) from $($docs[0].exportedFrom) into $Server$(if ($DryRun) { ' [WhatIf: preflight + script only]' })"

$conn = New-SqlConnection -Server $Server -Credential $Credential
try {
    # ---------------- preflight ----------------
    $problems = 0
    $agent = Invoke-SqlRows $conn "SELECT status_desc FROM sys.dm_server_services WHERE servicename LIKE 'SQL Server Agent%'"
    if ($agent.Rows.Count -and $agent.Rows[0].status_desc -ne 'Running') { Log "SQL Server Agent on $Server is $($agent.Rows[0].status_desc): jobs will be created but will not run until it is started." 'WARN' }
    $tLogins = @((ConvertTo-PlainRows (Invoke-SqlRows $conn 'SELECT name FROM sys.server_principals WHERE type IN (''S'',''U'',''G'')')) | ForEach-Object name)
    $tLinked = @((ConvertTo-PlainRows (Invoke-SqlRows $conn 'SELECT name FROM sys.servers WHERE is_linked = 1')) | ForEach-Object name)
    $tDbs = @((ConvertTo-PlainRows (Invoke-SqlRows $conn 'SELECT name FROM sys.databases')) | ForEach-Object name)
    $tProxies = @((ConvertTo-PlainRows (Invoke-SqlRows $conn 'SELECT name FROM msdb.dbo.sysproxies')) | ForEach-Object name)
    $tOps = @((ConvertTo-PlainRows (Invoke-SqlRows $conn 'SELECT name FROM msdb.dbo.sysoperators')) | ForEach-Object name)
    $tJobs = @((ConvertTo-PlainRows (Invoke-SqlRows $conn 'SELECT name FROM msdb.dbo.sysjobs')) | ForEach-Object name)
    $tCats = @((ConvertTo-PlainRows (Invoke-SqlRows $conn 'SELECT name FROM msdb.dbo.syscategories WHERE category_class = 1')) | ForEach-Object name)
    $providers = @()
    try { $providers = @((ConvertTo-PlainRows (Invoke-SqlRows $conn 'EXEC master.dbo.xp_enum_oledb_providers')) | ForEach-Object 'Provider Name') } catch { Log 'Cannot list OLE DB providers on the target (needs sysadmin); provider check skipped.' 'WARN' }

    $needLs = @{}
    foreach ($d in $docs) {
        $n = $d.job.name
        $owner = if ($Owner) { $Owner } else { $d.job.owner_login_name }
        if ($owner -notin $tLogins) { Log "$n : owner login '$owner' does not exist on $Server (create it or pass -Owner)." 'ERROR'; $problems++ }
        foreach ($ls in @($d.dependencies.linkedServers)) {
            if (-not $ls) { continue }
            $mapped = Apply-Map $ls
            if ($mapped -notin $tLinked) { $needLs[$ls] = $mapped; if (-not $CreateLinkedServers) { Log "$n : linked server '$mapped' is missing on $Server (use -CreateLinkedServers or create it)." 'ERROR'; $problems++ } }
        }
        foreach ($db in @($d.dependencies.databases)) {
            if (-not $db) { continue }
            $mapped = Apply-Map $db
            if ($mapped -notin $tDbs) { Log "$n : database '$mapped' is not on $Server; fine only if every reference goes through a linked server." 'WARN' }
        }
        foreach ($px in @($d.dependencies.proxies)) { if ($px -and $px -notin $tProxies) { Log "$n : proxy '$px' is missing on $Server." 'ERROR'; $problems++ } }
        foreach ($op in @($d.dependencies.operators)) { if ($op -and $op -notin $tOps) { Log "$n : operator '$op' is missing; notifications for this job will be dropped." 'WARN' } }
        if ($n -in $tJobs -and -not $Overwrite) { Log "$n : already exists on $Server; skipped (use -Overwrite to replace)." 'WARN' }
        $src = $d.exportedFrom
        $leftover = @($d.steps | Where-Object { (Apply-Map "$($_.command) $($_.server)") -match [regex]::Escape(($src -split '\\')[0]) })
        if ($leftover) { Log "$n : step(s) $(($leftover | ForEach-Object step_id) -join ',') still name the source server '$src'; add it to -Map if it should change." 'WARN' }
    }
    if ($problems -and -not $Force) { Log "$problems blocking problem(s): nothing imported. Fix them or pass -Force." 'ERROR'; return }

    # ---------------- linked servers ----------------
    if ($CreateLinkedServers -and $needLs.Count) {
        foreach ($srcName in $needLs.Keys) {
            $def = $lsDoc.linkedServers | Where-Object name -eq $srcName
            if (-not $def) { Log "No definition for linked server '$srcName' in linked-servers.json." 'ERROR'; continue }
            $name = $needLs[$srcName]
            $prov = $def.provider
            if ($ProviderMap.ContainsKey($prov) -and ($providers.Count -eq 0 -or $prov -notin $providers)) { Log "Linked server '$name': provider $prov -> $($ProviderMap[$prov])."; $prov = $ProviderMap[$prov] }
            $script.Add("-- linked server $name")
            $dataSource = Apply-Map $def.data_source
            if ($def.product -eq 'SQL Server' -and $name -eq $dataSource) {
                # Named after its own address: the SQL Server product form uses the target's default SQL provider
                $p = [ordered]@{ server = $name; srvproduct = 'SQL Server' }
            } else {
                $p = [ordered]@{ server = $name; srvproduct = $(if ($def.product -eq 'SQL Server') { '' } else { $def.product }); provider = $prov; datasrc = $dataSource }
                if ($def.location) { $p['location'] = $def.location }
                if ($def.provider_string) { $p['provstr'] = $def.provider_string }
                if ($def.catalog) { $p['catalog'] = $def.catalog }
            }
            if ($prov -eq 'MSOLEDBSQL') { Log "Linked server '$name' uses MSOLEDBSQL: version 19 encrypts by default, so the remote server needs a trusted certificate (or a provider string with Encrypt=Optional, agreed with security)." 'WARN' }
            if ($true) {
                $tx = $conn.BeginTransaction()
                try {
                    [void](Exec-Proc $conn $tx 'master.dbo.sp_addlinkedserver' $p $null)
                    foreach ($opt in @(@('rpc out', $def.is_rpc_out_enabled), @('data access', $def.is_data_access_enabled), @('collation compatible', $def.is_collation_compatible), @('use remote collation', $def.uses_remote_collation), @('remote proc transaction promotion', $def.is_remote_proc_transaction_promotion_enabled))) {
                        [void](Exec-Proc $conn $tx 'master.dbo.sp_serveroption' ([ordered]@{ server = $name; optname = $opt[0]; optvalue = $(if ($opt[1]) { 'true' } else { 'false' }) }) $null)
                    }
                    [void](Exec-Proc $conn $tx 'master.dbo.sp_droplinkedsrvlogin' ([ordered]@{ rmtsrvname = $name; locallogin = $null }) $null)
                    foreach ($m in @($lsDoc.logins | Where-Object linked_server -eq $srcName)) {
                        if ($m.local_login -and $m.local_login -notin $tLogins) { Log "Linked server '$name': login mapping for '$($m.local_login)' skipped, that login does not exist on $Server." 'WARN'; continue }
                        if ($m.uses_self_credential) {
                            [void](Exec-Proc $conn $tx 'master.dbo.sp_addlinkedsrvlogin' ([ordered]@{ rmtsrvname = $name; useself = 'TRUE'; locallogin = $m.local_login }) $null)
                        } else {
                            Log "Linked server '$name': login mapping '$(if ($m.local_login) { $m.local_login } else { 'all logins' })' -> '$($m.remote_name)' needs its password: EXEC master.dbo.sp_addlinkedsrvlogin @rmtsrvname = N'$name', @useself = 'FALSE', @locallogin = $(ConvertTo-SqlLiteral $m.local_login), @rmtuser = N'$($m.remote_name)', @rmtpassword = N'<password>';" 'WARN'
                        }
                    }
                    if (-not $DryRun) { $tx.Commit(); Log "Linked server '$name' created." } else { $tx.Rollback(); Log "Linked server '$name' would be created." }
                } catch { $tx.Rollback(); Log "Linked server '$name' not created: $($_.Exception.Message)" 'ERROR' }
            }
        }
    }

    # ---------------- jobs ----------------
    $created = 0; $skipped = 0; $failed = 0
    foreach ($d in $docs) {
        $j = $d.job; $n = $j.name
        if ($n -in $tJobs -and -not $Overwrite) { $skipped++; continue }
        $script.AddRange([string[]]@('', "-- ===== job: $n =====", 'BEGIN TRANSACTION;', 'DECLARE @jobId uniqueidentifier;'))
        $tx = if ($DryRun) { $null } else { $conn.BeginTransaction() }
        try {
            if ($n -in $tJobs) { [void](Exec-Proc $conn $tx 'msdb.dbo.sp_delete_job' ([ordered]@{ job_name = $n; delete_unused_schedule = 1 }) $null) }
            if ($j.category_name -and $j.category_name -notin $tCats) {
                $script.Add("IF NOT EXISTS (SELECT 1 FROM msdb.dbo.syscategories WHERE name = $(ConvertTo-SqlLiteral $j.category_name) AND category_class = 1)")
                [void](Exec-Proc $conn $tx 'msdb.dbo.sp_add_category' ([ordered]@{ class = 'JOB'; type = 'LOCAL'; name = $j.category_name }) $null); $tCats += $j.category_name
            }
            $p = [ordered]@{
                job_name = $n; enabled = $(if ($KeepEnabled) { [int]$j.enabled } else { 0 }); description = $j.description
                start_step_id = [int]$j.start_step_id; category_name = $j.category_name; owner_login_name = $(if ($Owner) { $Owner } else { $j.owner_login_name })
                notify_level_eventlog = [int]$j.notify_level_eventlog; delete_level = [int]$j.delete_level
            }
            foreach ($pair in @(@('notify_email_operator_name', 'notify_level_email'), @('notify_netsend_operator_name', 'notify_level_netsend'), @('notify_page_operator_name', 'notify_level_page'))) {
                $op = $j.($pair[0]); if ($op -and $op -in $tOps) { $p[$pair[0]] = $op; $p[$pair[1]] = [int]$j.($pair[1]) }
            }
            $p['job_id'] = $null
            $jobId = Exec-Proc $conn $tx 'msdb.dbo.sp_add_job' $p 'job_id'
            foreach ($s in @($d.steps)) {
                $sp = [ordered]@{ job_id = $jobId; step_id = [int]$s.step_id; step_name = $s.step_name; subsystem = $s.subsystem; command = (Apply-Map $s.command)
                    cmdexec_success_code = [int]$s.cmdexec_success_code; on_success_action = [int]$s.on_success_action; on_success_step_id = [int]$s.on_success_step_id
                    on_fail_action = [int]$s.on_fail_action; on_fail_step_id = [int]$s.on_fail_step_id; retry_attempts = [int]$s.retry_attempts; retry_interval = [int]$s.retry_interval
                    os_run_priority = [int]$s.os_run_priority; flags = [int]$s.flags }
                if ($s.database_name) { $sp['database_name'] = Apply-Map $s.database_name }
                if ($s.database_user_name) { $sp['database_user_name'] = $s.database_user_name }
                if ($s.server) { $sp['server'] = Apply-Map $s.server }
                if ($s.additional_parameters) { $sp['additional_parameters'] = Apply-Map $s.additional_parameters }
                if ($s.output_file_name) { $sp['output_file_name'] = Apply-Map $s.output_file_name }
                if ($s.proxy_name) { $sp['proxy_name'] = $s.proxy_name }
                if ($DryRun) { $sp.Remove('job_id'); $sp.Insert(0, 'job_name', $n) }
                [void](Exec-Proc $conn $tx 'msdb.dbo.sp_add_jobstep' $sp $null)
            }
            [void](Exec-Proc $conn $tx 'msdb.dbo.sp_update_job' ([ordered]@{ job_name = $n; start_step_id = [int]$j.start_step_id }) $null)
            foreach ($s in @($d.schedules)) {
                $sp = [ordered]@{ job_name = $n; name = $s.name; enabled = [int]$s.enabled; freq_type = [int]$s.freq_type; freq_interval = [int]$s.freq_interval
                    freq_subday_type = [int]$s.freq_subday_type; freq_subday_interval = [int]$s.freq_subday_interval; freq_relative_interval = [int]$s.freq_relative_interval
                    freq_recurrence_factor = [int]$s.freq_recurrence_factor; active_start_date = [int]$s.active_start_date; active_end_date = [int]$s.active_end_date
                    active_start_time = [int]$s.active_start_time; active_end_time = [int]$s.active_end_time }
                [void](Exec-Proc $conn $tx 'msdb.dbo.sp_add_jobschedule' $sp $null)
            }
            [void](Exec-Proc $conn $tx 'msdb.dbo.sp_add_jobserver' ([ordered]@{ job_name = $n; server_name = '(local)' }) $null)
            $script.Add('COMMIT;')
            if (-not $tx) { $created++; Log "Would create '$n' ($(@($d.steps).Count) step(s), $(@($d.schedules).Count) schedule(s))." }
            if ($tx) { $tx.Commit(); $created++; Log "Created '$n' ($(@($d.steps).Count) step(s), $(@($d.schedules).Count) schedule(s), $(if ($KeepEnabled -and $j.enabled) { 'enabled' } else { 'DISABLED' }))." }
        } catch {
            if ($tx) { $tx.Rollback() }
            $failed++; Log "'$n' not created: $($_.Exception.Message)" 'ERROR'
        }
    }
    Log "Done: $created $(if ($DryRun) { 'would be created' } else { 'created' }), $skipped skipped (already there), $failed failed. Script: $scriptFile"
}
finally {
    $script | Set-Content -Encoding UTF8 -Path $scriptFile
    $conn.Close()
}

<#
.SYNOPSIS
  Export SQL Server Agent jobs (with steps, schedules and what they depend on) from a server, read-only.

.DESCRIPTION
  Writes to -OutDir:
    jobs\<job>.json        one file per job: job, steps, schedules, notifications, dependencies (input for Import-AgentJobs.ps1)
    linked-servers.json    linked server definitions and login mappings (passwords cannot be read; they are never exported)
    inventory.csv          one line per job: enabled, owner, steps, schedules, linked servers, databases
    dependencies.md        what the target server needs before the jobs can run there
  The export reads msdb and sys.servers only; nothing on the source server is changed.
  Job step commands can contain connection strings or passwords: keep -OutDir private and out of source control.

.EXAMPLE
  .\Export-AgentJobs.ps1 -Server fcl-main20 -OutDir D:\agent-jobs\fcl-main20
.EXAMPLE
  .\Export-AgentJobs.ps1 -Server FCL-WMS -Job 'ProcessProduction','Import*' -OutDir D:\agent-jobs\fcl-wms
#>
[CmdletBinding()]
param(
    [Parameter(Mandatory)][string]$Server,
    [Parameter(Mandatory)][string]$OutDir,
    [string[]]$Job = @('*'),
    [pscredential]$Credential,
    [switch]$IncludeSystemJobs
)
$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot 'AgentJobCommon.ps1')

$conn = New-SqlConnection -Server $Server -Credential $Credential
try {
    $serverName = (Invoke-SqlRows $conn 'SELECT @@SERVERNAME AS n, SERVERPROPERTY(''ProductVersion'') AS v').Rows[0]
    $jobs = ConvertTo-PlainRows (Invoke-SqlRows $conn @"
SELECT j.job_id, j.name, j.enabled, j.description, j.start_step_id, c.name AS category_name, c.category_class,
       SUSER_SNAME(j.owner_sid) AS owner_login_name, j.notify_level_eventlog, j.notify_level_email, j.notify_level_netsend, j.notify_level_page,
       oe.name AS notify_email_operator_name, onet.name AS notify_netsend_operator_name, op.name AS notify_page_operator_name, j.delete_level,
       (SELECT COUNT(*) FROM msdb.dbo.sysjobservers s WHERE s.job_id = j.job_id AND s.server_id <> 0) AS remote_targets
FROM msdb.dbo.sysjobs j
LEFT JOIN msdb.dbo.syscategories c ON c.category_id = j.category_id
LEFT JOIN msdb.dbo.sysoperators oe ON oe.id = j.notify_email_operator_id
LEFT JOIN msdb.dbo.sysoperators onet ON onet.id = j.notify_netsend_operator_id
LEFT JOIN msdb.dbo.sysoperators op ON op.id = j.notify_page_operator_id
ORDER BY j.name
"@)
    $jobs = @($jobs | Where-Object { $n = $_.name; ($Job | Where-Object { $n -like $_ }).Count -gt 0 })
    if (-not $IncludeSystemJobs) { $jobs = @($jobs | Where-Object { $_.name -notin @('syspolicy_purge_history') }) }

    $linked = ConvertTo-PlainRows (Invoke-SqlRows $conn @"
SELECT s.server_id, s.name, s.product, s.provider, s.data_source, s.location, s.provider_string, s.catalog,
       s.is_rpc_out_enabled, s.is_data_access_enabled, s.is_collation_compatible, s.uses_remote_collation, s.collation_name,
       s.connect_timeout, s.query_timeout, s.is_remote_proc_transaction_promotion_enabled, s.lazy_schema_validation
FROM sys.servers s WHERE s.is_linked = 1 ORDER BY s.name
"@)
    $logins = ConvertTo-PlainRows (Invoke-SqlRows $conn @"
SELECT s.name AS linked_server, p.name AS local_login, l.uses_self_credential, l.remote_name
FROM sys.linked_logins l JOIN sys.servers s ON s.server_id = l.server_id LEFT JOIN sys.server_principals p ON p.principal_id = l.local_principal_id
WHERE s.is_linked = 1
"@)
    $dbNames = @((ConvertTo-PlainRows (Invoke-SqlRows $conn 'SELECT name FROM sys.databases WHERE database_id > 4')) | ForEach-Object name)
    $lsNames = @($linked | ForEach-Object name)

    New-Item -ItemType Directory -Force -Path (Join-Path $OutDir 'jobs') | Out-Null
    $inventory = @()
    foreach ($j in $jobs) {
        $p = @{ id = $j.job_id }
        $steps = ConvertTo-PlainRows (Invoke-SqlRows $conn @"
SELECT st.step_id, st.step_name, st.subsystem, st.command, st.flags, st.additional_parameters, st.cmdexec_success_code,
       st.on_success_action, st.on_success_step_id, st.on_fail_action, st.on_fail_step_id, st.server, st.database_name,
       st.database_user_name, st.retry_attempts, st.retry_interval, st.os_run_priority, st.output_file_name, px.name AS proxy_name
FROM msdb.dbo.sysjobsteps st LEFT JOIN msdb.dbo.sysproxies px ON px.proxy_id = st.proxy_id
WHERE st.job_id = @id ORDER BY st.step_id
"@ $p)
        $schedules = ConvertTo-PlainRows (Invoke-SqlRows $conn @"
SELECT s.name, s.enabled, s.freq_type, s.freq_interval, s.freq_subday_type, s.freq_subday_interval, s.freq_relative_interval,
       s.freq_recurrence_factor, s.active_start_date, s.active_end_date, s.active_start_time, s.active_end_time
FROM msdb.dbo.sysjobschedules js JOIN msdb.dbo.sysschedules s ON s.schedule_id = js.schedule_id WHERE js.job_id = @id ORDER BY s.name
"@ $p)
        $allText = ($steps | ForEach-Object { "$($_.command)`n$($_.additional_parameters)" }) -join "`n"
        $lsRefs = @(Find-LinkedServerRefs $allText $lsNames)
        $dbRefs = @(@($steps | ForEach-Object database_name | Where-Object { $_ -and $_ -notin @('master', 'msdb', 'tempdb', 'model') }) + @(Find-DatabaseRefs $allText $dbNames) | Select-Object -Unique)
        $doc = [ordered]@{
            exportedFrom = "$($serverName.n)"; sqlVersion = "$($serverName.v)"; exportedAt = (Get-Date).ToString('s')
            job = $j; steps = $steps; schedules = $schedules
            dependencies = [ordered]@{
                owner = $j.owner_login_name; linkedServers = $lsRefs; databases = $dbRefs
                proxies = @($steps | ForEach-Object proxy_name | Where-Object { $_ } | Select-Object -Unique)
                operators = @(@($j.notify_email_operator_name, $j.notify_netsend_operator_name, $j.notify_page_operator_name) | Where-Object { $_ } | Select-Object -Unique)
                subsystems = @($steps | ForEach-Object subsystem | Select-Object -Unique)
                outputFiles = @($steps | ForEach-Object output_file_name | Where-Object { $_ })
            }
        }
        $safe = ($j.name -replace '[\\/:*?"<>|]', '_').Trim()
        $doc | ConvertTo-Json -Depth 8 | Set-Content -Encoding UTF8 -Path (Join-Path $OutDir "jobs\$safe.json")
        $inventory += [pscustomobject]@{
            Job = $j.name; Enabled = [bool]$j.enabled; Owner = $j.owner_login_name; Category = $j.category_name; Steps = $steps.Count
            Subsystems = ($doc.dependencies.subsystems -join ','); Schedules = (($schedules | ForEach-Object { Get-ScheduleText $_ }) -join '; ')
            LinkedServers = ($lsRefs -join ','); Databases = ($dbRefs -join ','); Proxies = ($doc.dependencies.proxies -join ','); Operators = ($doc.dependencies.operators -join ',')
        }
        if ($j.remote_targets -gt 0) { Write-Warning "$($j.name) is a multi-server job; only the local target is exported." }
    }
    [ordered]@{ exportedFrom = "$($serverName.n)"; linkedServers = $linked; logins = $logins } | ConvertTo-Json -Depth 6 |
        Set-Content -Encoding UTF8 -Path (Join-Path $OutDir 'linked-servers.json')
    $inventory | Export-Csv -NoTypeInformation -Encoding UTF8 -Path (Join-Path $OutDir 'inventory.csv')

    # dependencies.md: what the target needs
    $usedLs = @($inventory | ForEach-Object { $_.LinkedServers -split ',' } | Where-Object { $_ } | Sort-Object -Unique)
    $md = @("# SQL Agent jobs exported from $($serverName.n)", '', "Exported $(Get-Date -Format 'yyyy-MM-dd HH:mm'): $($jobs.Count) job(s).", '',
        '| Job | Enabled | Owner | Schedule | Linked servers | Databases |', '|---|---|---|---|---|---|')
    $md += $inventory | ForEach-Object { "| $($_.Job) | $($_.Enabled) | $($_.Owner) | $($_.Schedules) | $($_.LinkedServers) | $($_.Databases) |" }
    $md += '', '## The target server needs', ''
    $md += "- **Logins** owning the jobs: $((@($inventory | ForEach-Object Owner | Sort-Object -Unique)) -join ', ') (or pass -Owner to Import-AgentJobs.ps1 to give them to a service account)"
    $md += "- **Linked servers** used by the steps: $(if ($usedLs) { $usedLs -join ', ' } else { 'none' })"
    foreach ($ls in $usedLs) {
        $d = $linked | Where-Object name -eq $ls
        $map = @($logins | Where-Object linked_server -eq $ls | ForEach-Object { if ($_.uses_self_credential) { "$(if ($_.local_login) { $_.local_login } else { 'all logins' }) as themselves" } else { "$(if ($_.local_login) { $_.local_login } else { 'all logins' }) -> remote login '$($_.remote_name)' (password must be re-entered)" } })
        $md += "  - ``$ls``: provider $($d.provider), data source $($d.data_source); logins: $(if ($map) { $map -join '; ' } else { 'none mapped' })"
    }
    $md += "- **Databases** referenced: $((@($inventory | ForEach-Object { $_.Databases -split ',' } | Where-Object { $_ } | Sort-Object -Unique)) -join ', ')"
    $md += "- **Operators / proxies**: $((@($inventory | ForEach-Object { ($_.Operators -split ','), ($_.Proxies -split ',') } | Where-Object { $_ } | Sort-Object -Unique)) -join ', ')"
    $md += '', 'Linked server passwords are never exported. SQLNCLI providers are not available on SQL Server 2022; Import-AgentJobs.ps1 maps them to MSOLEDBSQL.'
    $md | Set-Content -Encoding UTF8 -Path (Join-Path $OutDir 'dependencies.md')

    Write-Host "Exported $($jobs.Count) job(s) and $($linked.Count) linked server definition(s) from $($serverName.n) to $OutDir"
    $inventory | Format-Table Job, Enabled, Owner, Steps, LinkedServers, Schedules -AutoSize | Out-String -Width 220 | Write-Host
}
finally { $conn.Close() }

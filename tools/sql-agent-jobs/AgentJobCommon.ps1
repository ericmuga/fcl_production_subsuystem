# Shared helpers for Export-AgentJobs.ps1 / Import-AgentJobs.ps1 (Windows PowerShell 5.1+, no modules needed).

function New-SqlConnection {
    param([Parameter(Mandatory)][string]$Server, [string]$Database = 'master', [pscredential]$Credential)
    $b = New-Object System.Data.SqlClient.SqlConnectionStringBuilder
    $b['Data Source'] = $Server
    $b['Initial Catalog'] = $Database
    $b['Application Name'] = 'AgentJobTransfer'
    $b['Connect Timeout'] = 15
    if ($Credential) {
        $b['User ID'] = $Credential.UserName
        $b['Password'] = $Credential.GetNetworkCredential().Password
    } else {
        $b['Integrated Security'] = $true
    }
    $c = New-Object System.Data.SqlClient.SqlConnection $b.ConnectionString
    $c.Open()
    return $c
}

function Invoke-SqlRows {
    param([Parameter(Mandatory)]$Connection, [Parameter(Mandatory)][string]$Sql, [hashtable]$Params = @{}, $Transaction)
    $cmd = $Connection.CreateCommand()
    $cmd.CommandText = $Sql
    $cmd.CommandTimeout = 120
    if ($Transaction) { $cmd.Transaction = $Transaction }
    foreach ($k in $Params.Keys) { [void]$cmd.Parameters.AddWithValue("@$k", $(if ($null -eq $Params[$k]) { [DBNull]::Value } else { $Params[$k] })) }
    $t = New-Object System.Data.DataTable
    $r = $cmd.ExecuteReader()
    $t.Load($r)
    $r.Close()
    return , $t
}

function ConvertTo-PlainRows {
    param($Table)
    $out = @()
    foreach ($row in $Table.Rows) {
        $o = [ordered]@{}
        foreach ($col in $Table.Columns) { $v = $row[$col.ColumnName]; $o[$col.ColumnName] = $(if ($v -is [DBNull]) { $null } else { $v }) }
        $out += [pscustomobject]$o
    }
    return , $out
}

# Literal for the review script (N'...' with quotes doubled)
function ConvertTo-SqlLiteral {
    param($Value)
    if ($null -eq $Value) { return 'NULL' }
    if ($Value -is [bool]) { return $(if ($Value) { '1' } else { '0' }) }
    if ($Value -is [int] -or $Value -is [long] -or $Value -is [decimal] -or $Value -is [byte] -or $Value -is [int16]) { return [string]$Value }
    return "N'" + ([string]$Value).Replace("'", "''") + "'"
}

# Which of the given names a job step command refers to (4-part names, OPENQUERY / OPENDATASOURCE, EXEC ... AT).
function Find-LinkedServerRefs {
    param([string]$Text, [string[]]$Names)
    $found = @()
    if (-not $Text) { return , $found }
    foreach ($n in $Names) {
        $e = [regex]::Escape($n)
        $patterns = @("\[$e\]\s*\.", "(?<![\w\.\]\[])$e\s*\.\s*\[?\w", "OPENQUERY\s*\(\s*\[?$e\]?", "\bAT\s+\[?$e\]?(?![\w\.])")
        foreach ($p in $patterns) { if ([regex]::IsMatch($Text, $p, 'IgnoreCase')) { $found += $n; break } }
    }
    return , ($found | Select-Object -Unique)
}

function Find-DatabaseRefs {
    param([string]$Text, [string[]]$Names)
    $found = @()
    if (-not $Text) { return , $found }
    foreach ($n in $Names) {
        $e = [regex]::Escape($n)
        if ([regex]::IsMatch($Text, "(\[$e\]|(?<![\w\[\]\.])$e)\s*\.\s*\[?\w*\]?\s*\.|\bUSE\s+\[?$e\]?(?![\w])", 'IgnoreCase')) { $found += $n }
    }
    return , ($found | Select-Object -Unique)
}

function Get-ScheduleText {
    param($s)
    $time = { param($t) $t = '{0:D6}' -f [int]$t; '{0}:{1}' -f $t.Substring(0, 2), $t.Substring(2, 2) }
    $freq = switch ([int]$s.freq_type) {
        1 { 'once' } 4 { "every $($s.freq_interval) day(s)" } 8 { "weekly (days mask $($s.freq_interval)) every $($s.freq_recurrence_factor) week(s)" }
        16 { "monthly on day $($s.freq_interval)" } 32 { 'monthly (relative)' } 64 { 'when SQL Agent starts' } 128 { 'when idle' } default { "type $($s.freq_type)" }
    }
    $sub = switch ([int]$s.freq_subday_type) { 1 { "at $(& $time $s.active_start_time)" } 2 { "every $($s.freq_subday_interval) s" } 4 { "every $($s.freq_subday_interval) min" } 8 { "every $($s.freq_subday_interval) h" } default { '' } }
    if ([int]$s.freq_subday_type -gt 1) { $sub += " between $(& $time $s.active_start_time) and $(& $time $s.active_end_time)" }
    return "$freq $sub".Trim()
}

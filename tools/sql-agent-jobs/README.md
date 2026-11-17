# Moving SQL Agent jobs to another database server

These two PowerShell scripts move SQL Server Agent jobs, including `ProcessProduction` and the import jobs on fcl-main20, from one server to another.
- **What they copy:** each job with its steps, schedules and notifications.
- **What they check first:** that the target has the linked servers, logins and databases the jobs use.
- **Requirements:** Windows PowerShell 5.1 or later. No modules are needed.
- **Permissions:** read access to `msdb` and `sys.servers` on the source; sysadmin on the target.

| Script | Does |
|---|---|
| `Export-AgentJobs.ps1` | **Reads only.** Writes one JSON file per job, `linked-servers.json`, `inventory.csv` and `dependencies.md` (what the target needs). |
| `Import-AgentJobs.ps1` | Checks the target first, then creates each job in its own transaction, **disabled** by default. Optionally replaces server and database names, gives jobs a new owner and creates missing linked servers. Writes the T-SQL it ran and a log next to the export. |

> **Keep the exports private.** Job step text can contain connection strings or passwords. Write exports outside any repository; the `.gitignore` here blocks `agent-jobs-export*/`. Linked server passwords are never exported.

## Cutover, one server at a time

1. **Export** from the old server:
   ```powershell
   .\Export-AgentJobs.ps1 -Server fcl-main20 -OutDir D:\agent-jobs-export\fcl-main20
   ```
   Read `dependencies.md`. For fcl-main20 (13 Nov 2026) it lists:
   - 24 jobs, all owned by a personal login;
   - linked servers `100.100.2.54`, `100.100.2.55`, `172.16.10.5` and `FCL-PBI`, all on the SQLNCLI provider;
   - remote logins `SyncPrices` and `BC2022`, whose passwords have to be re-entered.

2. **Dry run** against the new server: this runs the checks and writes the script only.
   ```powershell
   .\Import-AgentJobs.ps1 -Server NEW-SQL -InDir D:\agent-jobs-export\fcl-main20 `
       -Owner 'FARMERSCHOICE\svc_sqlagent' -CreateLinkedServers `
       -Map @{ 'fcl-main20' = 'NEW-SQL' } -WhatIf
   ```
   - **`-Owner`** moves the jobs off the personal login (integration map §8 item 12).
   - **`-Map`** replaces text in step commands, step server, database and output file. The checks warn when a step still names the old server.

3. **Import for real.** Run the same command without `-WhatIf`. The jobs arrive **disabled**.
   - **Remote logins:** add their passwords using the `sp_addlinkedsrvlogin` lines printed in the log.
   - **SQLNCLI:** it isn't available on SQL Server 2022, so linked servers are switched to MSOLEDBSQL. MSOLEDBSQL 19 encrypts by default, so the remote servers need a trusted certificate. Otherwise agree a provider string such as `Encrypt=Optional` with security.

4. **Switch jobs over**, one job or group at a time, so the same job never runs on both servers:
   ```sql
   -- old server
   EXEC msdb.dbo.sp_update_job @job_name = N'Import Transfers', @enabled = 0;
   -- new server
   EXEC msdb.dbo.sp_update_job @job_name = N'Import Transfers', @enabled = 1;
   ```
   Then check **Job History** on the new server after the first run. To roll back, swap the two `@enabled` values.

5. **Retire the old jobs** once the new server has run cleanly for a full cycle, including weekly jobs such as `main_bu_full`.

## Options

| Parameter | Use |
|---|---|
| `-Job 'Import*','Midnight'` | Wildcards; both scripts take it |
| `-Owner <login>` | New job owner; it must exist on the target |
| `-Map @{ old = new }` | Text replacements in steps (case-insensitive) |
| `-KeepEnabled` | Keep each job's enabled state instead of creating it disabled |
| `-Overwrite` | Replace jobs of the same name on the target; otherwise they are skipped |
| `-CreateLinkedServers` | Create the linked servers the jobs use that are missing on the target |
| `-ProviderMap` | Provider replacements; default SQLNCLI* → MSOLEDBSQL |
| `-Force` | Import even when the checks found blocking problems |
| `-Credential` | SQL login instead of Windows authentication |

## Tested

**13 Nov 2026:**
- **Export:** exported fcl-main20 read-only.
- **Dry run:** dry-ran all 24 jobs against localhost.
- **Round trip:** imported two jobs into localhost, exported them back and compared. Steps and schedules were identical; the jobs were disabled and their owner was replaced. The test jobs were deleted afterwards.

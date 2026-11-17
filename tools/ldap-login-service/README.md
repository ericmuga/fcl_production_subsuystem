# Local LDAP login service

WMS doesn't check passwords itself. `LoginController::processLogin` posts the username and password to `LOGIN_URL`, and the login is accepted when the reply contains `"success": true`. In production another service answers. This one does the same job on a developer machine by binding to Active Directory as the user, the same way bc-app does (`server/src/services/ldap.js`).

```powershell
cd tools\ldap-login-service
npm install          # once
npm start            # listens on http://127.0.0.1:3010/login
```

Then, in the WMS `.env`:

```
LOGIN_URL=http://127.0.0.1:3010/login
```

Run `php artisan config:clear`, then start `php artisan serve` and log in at http://127.0.0.1:8000 with your domain username (without `FARMERSCHOICE\`) and your domain password.

## Settings

Set these as environment variables or in a `.env` file next to `server.js`. That `.env` is git-ignored.

| Setting | Default |
|---|---|
| `AD_URL` | `ldap://172.16.9.4:389` |
| `AD_DOMAIN` | `farmerschoice.co.ke` |
| `HOST` | `127.0.0.1` (loopback only) |
| `PORT` | `3010` |

## Security and troubleshooting

- **Credentials are never logged.** The log shows only the username and AD's diagnostic code.
- **Blank passwords are always refused**, because AD would otherwise accept them as an anonymous bind.
- **Health check:** `GET /health` shows which AD server the service is using.
- **Account lockout:** every wrong password counts toward the normal AD lockout.

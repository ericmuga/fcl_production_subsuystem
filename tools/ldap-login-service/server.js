// Local login API for WMS: checks a domain username and password against Active Directory with a direct UPN bind
// (same approach as bc-app server/src/services/ldap.js).
//
// Contract expected by WMS (App\Models\Helpers::validateLogin, config app.login_api_url = LOGIN_URL):
//   POST /login   {"username": "FARMERSCHOICE\\jdoe", "password": "..."}
//   200           {"success": true,  "username": "jdoe"}
//   200           {"success": false, "message": "Invalid credentials."}
//
// Settings (environment or .env next to this file):
//   AD_URL      ldap://172.16.9.4:389
//   AD_DOMAIN   farmerschoice.co.ke
//   HOST        127.0.0.1          (listen on loopback only)
//   PORT        3010
// Passwords are never logged.
const http = require('http');
const fs = require('fs');
const path = require('path');
const ldap = require('ldapjs');

loadDotEnv(path.join(__dirname, '.env'));
const AD_URL = process.env.AD_URL || 'ldap://172.16.9.4:389';
const AD_DOMAIN = process.env.AD_DOMAIN || 'farmerschoice.co.ke';
const HOST = process.env.HOST || '127.0.0.1';
const PORT = Number(process.env.PORT || 3010);

function loadDotEnv(file) {
  if (!fs.existsSync(file)) return;
  for (const line of fs.readFileSync(file, 'utf8').split(/\r?\n/)) {
    const m = /^\s*([A-Z0-9_]+)\s*=\s*(.*)\s*$/i.exec(line);
    if (m && !line.trim().startsWith('#') && process.env[m[1]] === undefined) {
      process.env[m[1]] = m[2].replace(/^["']|["']$/g, '');
    }
  }
}

// "FARMERSCHOICE\jdoe", "jdoe" or "jdoe@farmerschoice.co.ke" -> { sam, upn }
function identity(username) {
  const value = typeof username === 'string' ? username.trim() : '';
  const sam = value.replace(/^.*\\/, '').replace(/@.*$/, '');
  if (!sam || /[\x00-\x20]/.test(sam)) return null;
  return { sam, upn: value.includes('@') ? value.replace(/^.*\\/, '') : `${sam}@${AD_DOMAIN}` };
}

function bind(upn, password) {
  return new Promise((resolve, reject) => {
    const client = ldap.createClient({ url: AD_URL, reconnect: false, connectTimeout: 8000, timeout: 10000 });
    let settled = false;
    const done = (err) => {
      if (settled) return;
      settled = true;
      try { client.destroy(); } catch { /* ignore */ }
      err ? reject(err) : resolve();
    };
    client.on('error', done);
    client.bind(upn, password, done);
  });
}

function log(msg, extra = {}) {
  console.log(JSON.stringify({ at: new Date().toISOString(), msg, ...extra }));
}

function send(res, status, body) {
  res.writeHead(status, { 'Content-Type': 'application/json' });
  res.end(JSON.stringify(body));
}

const server = http.createServer((req, res) => {
  if (req.method === 'GET' && req.url === '/health') return send(res, 200, { ok: true, adUrl: AD_URL, domain: AD_DOMAIN });
  if (req.method !== 'POST' || !['/login', '/'].includes(req.url)) return send(res, 404, { success: false, message: 'Not found' });

  let raw = '';
  req.on('data', (c) => { raw += c; if (raw.length > 10000) req.destroy(); });
  req.on('end', async () => {
    let body;
    try { body = JSON.parse(raw || '{}'); } catch { return send(res, 400, { success: false, message: 'Invalid JSON' }); }
    const id = identity(body.username);
    // An empty password would be an anonymous bind, which AD accepts: never treat it as a login.
    if (!id || typeof body.password !== 'string' || body.password === '') {
      return send(res, 200, { success: false, message: 'Username and password are required.' });
    }
    try {
      await bind(id.upn, body.password);
      log('login ok', { user: id.sam });
      send(res, 200, { success: true, username: id.sam });
    } catch (err) {
      if (Number(err.code) === 49) {
        const diagnostic = /\bdata\s+([0-9a-f]+)/i.exec(err.message || '')?.[1];
        log('login rejected', { user: id.sam, diagnostic: diagnostic || 'n/a' });
        return send(res, 200, { success: false, message: 'Invalid credentials.' });
      }
      log('AD error', { user: id.sam, code: err.code, error: err.message });
      send(res, 200, { success: false, message: 'Active Directory is not reachable.' });
    }
  });
});

server.listen(PORT, HOST, () => log('ldap login service listening', { url: `http://${HOST}:${PORT}/login`, adUrl: AD_URL }));

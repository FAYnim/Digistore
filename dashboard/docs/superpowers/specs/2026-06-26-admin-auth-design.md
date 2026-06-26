# Design — Admin Authentication

Date: 2026-06-26

## Goal
Protect all dashboard pages and `/dashboard/api/` endpoints with PHP session-based admin authentication.

## Scope
Implement admin login, logout, session storage, page/API guards, CSRF for login, session cookie hardening, topbar admin display, API 401 handling, and a one-time manual admin seeder.

## Architecture
- `dashboard/auth/session.php` centralizes secure session startup.
- `dashboard/auth/csrf.php` creates and validates login CSRF tokens.
- `dashboard/auth/check-auth.php` blocks unauthenticated access.
- `dashboard/login.php` renders the login UI.
- `dashboard/auth/login-process.php` validates credentials from `admin_users`.
- `dashboard/logout.php` clears the session.
- `database/seed-admin.php` creates the first admin manually.

## Data Model
Use `admin_users`:

```sql
CREATE TABLE admin_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  name VARCHAR(100) NOT NULL,
  status ENUM('active', 'inactive') DEFAULT 'active',
  last_login_at DATETIME DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

Passwords use `password_hash()` and login uses `password_verify()`.

## Auth Flow
1. User opens dashboard page.
2. Page includes `auth/check-auth.php` before rendering.
3. Missing/invalid session redirects to `login.php`.
4. Login submits username, password, CSRF token to `auth/login-process.php`.
5. Valid login regenerates session ID and stores:
   - `admin_logged_in`
   - `admin_id`
   - `admin_username`
   - `admin_name`
6. Logout destroys session and redirects to login.

## API Flow
Every `dashboard/api/*.php` includes `auth/check-auth.php`. If unauthenticated, response is HTTP 401 JSON:

```json
{
  "success": false,
  "message": "Unauthorized",
  "data": null,
  "errors": null
}
```

Frontend API helper redirects to `login.php` when response status is 401.

## UI
Login page follows existing Tailwind dashboard theme: centered card, username/password fields, error message, login button, and link back to catalog.

Topbar shows the active admin name/username and a logout link.

## Security
- `session.cookie_httponly = 1`
- `session.use_strict_mode = 1`
- `session.cookie_secure = 1` only when HTTPS is active
- `session_regenerate_id(true)` after login
- Generic invalid credential error
- Inactive account error
- CSRF token on login form

## Testing
Manual checks:
- Dashboard redirects to login without session.
- Login succeeds with seeded active admin.
- Login fails with wrong password.
- Inactive admin cannot login.
- Logout clears session.
- API returns 401 when unauthenticated.
- API works after login.
- Frontend redirects on API 401.

## Deployment Note
`database/seed-admin.php` must be deleted after first admin creation, especially before production deployment.

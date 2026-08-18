# Photo Gallery Web Application

A full-stack photo gallery application built on a traditional LAMP stack (Linux, Apache, MariaDB, PHP) with a vanilla HTML, CSS, and JavaScript frontend. No frontend or backend frameworks are used.

This project was developed as a dual learning exercise: building a real, deployable web application while deliberately practicing secure coding habits aligned with the OWASP Top 10. Each feature described below was implemented, then audited against its most relevant vulnerability class, corrected, and verified. The Security section documents that process in detail and is the most relevant section for evaluating this project.

---

## Overview

Users can browse a shared photo gallery, register an account, and log in using session-based authentication. Access to functionality is determined by an assigned role.

| Role | View Gallery | Upload | Delete |
|---|---|---|---|
| Guest (not logged in) | No | No | No |
| Viewer | Yes | No | No |
| Uploader | Yes | Yes | Yes |

The interface adapts to these three states. A guest is shown only a login or registration prompt. A viewer is shown the full gallery without upload controls. An uploader is shown the full gallery with upload and delete controls.

---

## Features

- Role-based, three-state user interface (guest, viewer, uploader)
- Session-based authentication with secure logout, including session and cookie invalidation
- Multi-file image upload with per-file validation and partial-success reporting
- Server-side file type validation using MIME type inspection, not file extension alone
- Image deletion with confirmation
- Fullscreen photo viewer with keyboard and click navigation
- Responsive gallery layout
- User feedback for all actions (login, registration, upload, deletion) via SweetAlert2

---

## Security

Security was addressed throughout development rather than as a final step. Each item below was identified, corrected, and manually verified. Verification included deliberately triggering error conditions to confirm that logs captured the relevant detail and that no sensitive information reached the browser.

| Vulnerability Class | Issue Identified | Countermeasure Applied |
|---|---|---|
| Broken Access Control | Upload and delete controls, and gallery content, were rendered client-side regardless of authentication state | Server-side conditional rendering based on session role, combined with server-side authorization checks on every state-changing endpoint. The interface reflects access state; the server enforces it. |
| Injection | Risk of SQL injection through unsanitized query construction | All database queries use prepared statements with bound parameters. No queries are built through string concatenation. |
| Identification and Authentication Failures | No protection against repeated login attempts | Login attempts are rate-limited by both username and IP address, using a sliding time window and an auto-expiring lockout, backed by a dedicated login attempts table. |
| Security Misconfiguration | Detailed PHP errors, including server file paths, were returned directly in HTTP responses | Error display disabled in production. Errors are logged to a file stored outside the web root. Sensitive directories and files are explicitly denied at the web server configuration level. |
| Sensitive Data Exposure | Database credentials were stored directly in source code | Credentials moved to a git-ignored environment file, loaded through a minimal custom parser, with restricted file permissions. |
| Insecure File Upload | Client-side file size and type checks can be bypassed by a direct request to the server | Server-side MIME type validation, enforced file size limits, and server-generated filenames rather than user-supplied filenames. |
| Cross-Site Scripting | Upload failure messages rendered user-controlled filenames as HTML without escaping | All user-controlled text is passed through an escaping function before being rendered as HTML. |
| Excessive Privilege | Application risked connecting to the database with administrator-level privileges | A dedicated database user was created, scoped to only the operations and tables required by the application. |
| Session Management | The logout endpoint did not correctly terminate the session due to an incorrect file dependency | Session bootstrapping corrected. Logout now clears session data, destroys the session, and explicitly expires the session cookie. |
| Cross-Site Request Forgery (CSRF) | State-changing requests (login, register, upload, delete, logout) relied solely on session cookies, with no verification that the request originated from the application's own interface | Synchronizer token pattern implemented: a per-session token is generated server-side, embedded in the page, and required as a custom header on every state-changing request. Verified server-side using a timing-safe comparison. |
| Security Misconfiguration (Missing Headers) | The application did not send any HTTP security headers, leaving the browser to apply only default behavior | Content-Security-Policy, X-Content-Type-Options, X-Frame-Options, Strict-Transport-Security, Referrer-Policy, Permissions-Policy, and appropriate Cache-Control headers applied. Each header selected against a specific, named threat rather than added by default. |

### Known Limitations

The following items have not yet been implemented and are noted here for transparency:

- TLS termination is not configured within this repository. The deployed instance uses a third-party tunnel for HTTPS.
- No automated test coverage for authentication, rate-limiting, or CSRF verification logic.

---

## Technology Stack

Frontend: HTML5, CSS3, vanilla JavaScript, SweetAlert2

Backend: PHP, MySQLi, PHP Sessions

Database: MariaDB

Server Environment: Apache2 on Linux

No frontend or backend frameworks were used. This was a deliberate choice, made to understand the underlying mechanisms that frameworks typically abstract, including routing, session handling, and query construction, before adopting tools that automate them.

---

## Architecture

```
Frontend (HTML, CSS, JavaScript)
        |
        v
JSON API requests and multipart form data (uploads)
        |
        v
public/
  index.php     (renders interface by role)
  login.php     (authentication and rate limiting)
  register.php  (account creation)
  logout.php    (session termination)
  upload.php    (multi-file upload)
  delete.php    (photo deletion)
        |
        v
includes/  (outside web root)
  db.php               (database connection and environment loading)
  auth.php             (session and role guards)
  env.php              (environment file loader)
  rate_limit.php       (login attempt throttling)
  csrf.php             (CSRF token generation and verification)
  security_headers.php (HTTP security header configuration)
        |
        v
MariaDB (users, photos, login_attempts)
```

The `includes/` and `logs/` directories are located outside the Apache document root and are additionally denied at the web server configuration level. This is a deliberate defense-in-depth measure and does not rely solely on directory placement.

---

## Project Structure

```
gallery-project/
  .env                 (database credentials; not included in repository)
  .gitignore
  includes/
    db.php
    auth.php
    env.php
    rate_limit.php
    csrf.php
    security_headers.php
  logs/
    php-error.log      (not included in repository)
  public/
    index.php
    login.php
    register.php
    logout.php
    upload.php
    delete.php
    css/
      style.css
    js/
      script.js
```

---

## Setup

1. Clone the repository and place it outside the Apache document root, with only the `public/` directory served directly.

   ```
   git clone https://github.com/yourusername/gallery-project.git
   ```

2. Create the database and required tables. See `schema.sql`.

3. Copy `.env.example` to `.env` and populate it with your database credentials.

   ```
   DB_HOST=localhost
   DB_USER=gallery_app
   DB_PASS=your_password
   DB_NAME=gallery_db
   ```

4. Configure Apache to use `public/` as the document root. Confirm that `includes/`, `logs/`, and `.env` are not reachable via HTTP request.

5. Create a database user scoped to this application rather than using an administrative account.

   ```sql
   CREATE USER 'gallery_app'@'localhost' IDENTIFIED BY 'your_password';
   GRANT SELECT, INSERT, UPDATE, DELETE ON gallery_db.* TO 'gallery_app'@'localhost';
   ```

Note: `schema.sql`, containing table definitions for `users`, `photos`, and `login_attempts`, and `.env.example`, containing placeholder values, should be added to the repository prior to publishing.

---

## Notes on Development

This project was used to examine how access control failures manifest in practice, beyond their theoretical description. Attempting to bypass the application's own interface clarified the distinction between an interface that conceals functionality and a server that actually enforces restrictions on it.

Several issues encountered during development were silent failure modes: a failed query preparation that occurred before any output was returned, or a session termination function that ran without an active session to act on. These were not visible without deliberate verification, which informed a decision to treat server-side error logging as a required feature rather than a supplementary one.

Security considerations were not treated as separate from feature development. Each feature implemented, including authentication, file upload, and database writes, corresponds to a recognized vulnerability category, and implementing the feature functioned as an exercise in defending it.

---

## Planned Work

- Automated tests for authentication, rate-limiting, and CSRF verification logic
- TLS termination configured directly rather than delegated to a tunnel provider

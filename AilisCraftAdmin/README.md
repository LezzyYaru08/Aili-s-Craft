# Aili's Craft — Standalone Administrator System

This version is independent of the old Owner/User dashboard. It follows the provided admin flowchart.

## Admin features
- Administrator/Manager roles only (Staff removed).
- Manage administrator accounts.
- Manage customer accounts from the user system.
- Modify customer name, email, status, and optionally reset password.
- Delete customer accounts.
- Send a Notice or Message to a selected customer.
- User system displays notices/messages in its notification bell and Profile page.
- System maintenance, source-code module, and activity log.

## Shared browser storage
The admin and user applications communicate through localStorage keys such as `ac_users`, `ac_notifications`, and `ac_user_messages`. For this browser prototype, serve both applications from the same origin (for example, the same localhost host/port) so both apps can access the shared data.


## Database-backed account management

The Manage Accounts page now reads Owner, Administrator, and Manager records from
`admin_accounts`, and the User Accounts page reads customer records from `users`.
New staff accounts created in the admin page are inserted into `admin_accounts`
with a PHP `password_hash()` password hash. Updates and deletions are also sent
to the database. The admin panel and owner panel must use the same
`ailiscraft_db` database and the same PHP session cookie scope.

No schema change is required for this update. Do not re-import the full
`ailiscraft_database_schema.sql` over an existing database: it contains DROP
TABLE statements and would erase existing records. Back up your database first.

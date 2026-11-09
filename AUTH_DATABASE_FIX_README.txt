AilisCraft authentication/database fix

1. Back up your current database and project folder.
2. Ensure the schema has been imported and api/config.php points to the correct database.
3. Start Apache and MySQL in XAMPP.
4. Open http://localhost/AilisCraft/AilisCraftAdmin/setup_accounts.php and click the setup button ONCE. This creates initial admin/owner rows only if admin_accounts is empty.
5. Delete AilisCraft/AilisCraftAdmin/setup_accounts.php immediately after setup. Change initial passwords after first login.
6. Customer registration and login now validate against the users table and store password hashes. Owner/Admin login validates active role-specific records in admin_accounts; hard-coded browser credentials are no longer accepted.

Initial local credentials created by the one-time setup:
Administrator: admin / AilisAdmin123
Owner: Owner / AilisCraft123

If your project is hosted under a different folder name, adjust the URL path accordingly. Do not import the root schema over an existing database without a backup; it contains DROP TABLE statements.

# Current database backup

`project_database_backup.sql.enc` contains the latest full PostgreSQL database
export, encrypted with Laravel's existing `APP_KEY`. The export includes schema,
data, sequences, functions and triggers, without database ownership or role grants.

The unencrypted, timestamped `database-backup-*.sql` copy stays in the local project
root and is excluded from Git. The older `project_last_database_exported_1.sql`
is a previous snapshot; use the encrypted backup for the latest database state.

Keep the original `.env` / `APP_KEY` in a separate secure backup. The key is required
to decrypt this backup and the application's encrypted vendor application data.
It is intentionally excluded from GitHub. Uploaded product media and identity
document files are stored separately and are not included in the database export.

To decrypt the latest backup from the project root with the original `APP_KEY` set:

```powershell
php artisan tinker --execute="file_put_contents('database-restore.sql', Crypt::decryptString(trim(file_get_contents('project_database_backup.sql.enc'))));"
```

Import the resulting SQL into an empty PostgreSQL database with PostgreSQL 18's
`psql --set ON_ERROR_STOP=on --dbname=YOUR_DATABASE --file=database-restore.sql`.
Set database credentials through your normal PostgreSQL authentication settings;
do not put passwords in committed files. Importing into an empty database avoids
overwriting the running application's database.

`project_database_backup.json` records the UTC export time, database version,
table count, byte size and SHA-256 of the decrypted SQL for verification.

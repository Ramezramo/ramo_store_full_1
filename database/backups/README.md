# PostgreSQL backup

The encrypted file contains a full PostgreSQL custom-format dump. The recovery key is stored separately and is required to restore it. The dump does not include database roles or ownership grants.

Install the Python package cryptography. With the recovery key available locally, decrypt the backup and restore it into a new, empty PostgreSQL database:

~~~powershell
python -c "from pathlib import Path; from cryptography.fernet import Fernet; Path('ramo-store.dump').write_bytes(Fernet(Path('recovery.key').read_bytes().strip()).decrypt(Path('database/backups/ramo-store-2026-11-23.dump.fernet').read_bytes()))"
pg_restore --exit-on-error --no-owner --no-privileges --dbname=ramo_store_restored ramo-store.dump
~~~

Run the commands from the repository root and replace recovery.key with the local key path. Keep the decrypted dump and key outside version control. Laravel-encrypted fields still require the original application's separate APP_KEY.

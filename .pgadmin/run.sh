#!/usr/bin/env bash
set -e
cd "$(dirname "$0")/.."
export PGADMIN_PYPATH=$(cat .pgadmin/pythonpath.txt)
export PYTHONPATH="/home/runner/workspace/.pgadmin:$PGADMIN_PYPATH"
export PGADMIN_SETUP_EMAIL="admin@ramostore.local"
export PGADMIN_SETUP_PASSWORD="admin123456"
exec python3 /nix/store/f3xb192jmg6abd3caph11nl331qj29r0-pgadmin-9.3/lib/python3.12/site-packages/pgadmin4/pgAdmin4.py

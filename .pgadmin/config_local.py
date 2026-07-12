import os

DATA_DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'data')
LOG_FILE = os.path.join(DATA_DIR, 'pgadmin4.log')
SQLITE_PATH = os.path.join(DATA_DIR, 'pgadmin4.db')
SESSION_DB_PATH = os.path.join(DATA_DIR, 'sessions')
STORAGE_DIR = os.path.join(DATA_DIR, 'storage')

SERVER_MODE = True
DEFAULT_SERVER = '0.0.0.0'
DEFAULT_SERVER_PORT = 8080
UPGRADE_CHECK_ENABLED = False
MASTER_PASSWORD_REQUIRED = False
CHECK_EMAIL_DELIVERABILITY = False
ALLOW_SPECIAL_EMAIL_DOMAINS = ['local']
# Behind Replit's reverse proxy, the app sees requests over plain HTTP while
# the browser sees HTTPS; Flask-WTF's strict same-origin Referer check on
# CSRF-protected API calls fails as a result ("referrer header is missing"),
# even though the token itself is valid. Disable the strict check.
WTF_CSRF_SSL_STRICT = False

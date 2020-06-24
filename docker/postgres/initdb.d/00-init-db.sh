#!/bin/bash
set -e

# psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" <<-EOSQL
#     CREATE USER svarta WITH LOGIN ENCRYPTED PASSWORD 'svarta';
#     CREATE DATABASE svarta WITH OWNER svarta;
#     GRANT ALL PRIVILEGES ON DATABASE svarta TO svarta;
# EOSQL

psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" < '/var/www/html/resources/db/00-init.sql'

# psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$ICR_DB" < '/var/www/html/resources/db/10-structure-core.sql'
# psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$ICR_DB" < '/var/www/html/resources/db/11-structure-timetable.sql'

psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$ICR_DB" < '/var/www/html/resources/db/90-dump.sql'
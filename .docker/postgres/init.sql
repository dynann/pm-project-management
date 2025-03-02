CREATE DATABASE laravel_db;
CREATE USER laravel_user WITH PASSWORD 'password';
ALTER ROLE laravel_user SET client_encoding TO 'utf8';
ALTER ROLE laravel_user SET default_transaction_isolation TO 'read committed';
ALTER ROLE laravel_user SET timezone TO 'UTC';
GRANT ALL PRIVILEGES ON DATABASE laravel_db TO laravel_user;

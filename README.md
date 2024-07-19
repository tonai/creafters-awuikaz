# Creafters Awuikaz

Connect to mysql:
```sh
sudo mysql
```

Then create database and user:
```sql
CREATE DATABASE awuikaz;
CREATE USER 'awuikaz'@'localhost' IDENTIFIED BY 'zakiuwa';
GRANT ALL ON awuikaz.* TO 'awuikaz'@'localhost';
```

And import database:
```sh
sudo mysql awuikaz < sql/bdd.sql
```
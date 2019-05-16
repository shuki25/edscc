# Update Instructions
This document provides a brief instruction of steps needed to take after each time you do a `git pull` to your server with updates. Some updates would have some database changes and you would need to update the database changes manually after the `git pull`.
```
$ cd /path/to/base/dir/edscc
$ git pull
$ composer update
$ bin/console make:migration
$ bin/console doctrine:migrations:migrate
$ cd src/Sql
$ mysql -u [username] -p --default-character-set=utf8 edscc < update.sql
```

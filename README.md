# ED:SCC
*Elite Dangerous: Squadron Command Center* is a web application for Elite Dangerous gamers to track their gaming progress over time and compare their performance with other gamers and squadrons. As well as for squadron leaders to track and monitor squadron's activities for strategies or lore role play.

### Beta and Demo Servers
We have two servers for you to check out the features or for your use for data analysis.  The demo server is populated with fake data so that you can see what the site would look like when it is enriched with data rather than starting out from blank and not being able to view the dashboard with line graphs and statistics.

Beta Server: [beta.edscc.net](https://beta.edscc.net)<br>
Demo Server: [demo.edscc.net](https://demo.edscc.net)

### Requirements
* PHP 7.2 or higher
* MySQL 5.7 or higher
* Postfix, Sendmail or other mail transporter (needed for e-mail notifications)
* Apache2 with rewrite enabled
* Nginx not tested, Symfony 4 reported that it is supported
* Composer to install package dependencies

### Install
1. Download the source code from Github.
```
$ git clone https://github.com/shuki25/edscc.git
```
2. Install the package dependencies with composer.
```
$ cd edscc
$ composer install
```
3. Create a new database, an user and grant appropriate privileges. Replace username with the user you want to create for the app to use (e.g. edscc) and a strong password.
```
$ mysql -u root -p

create database edscc;
grant all privileges on edscc.* to 'username'@'localhost' identified by 'password';

```
4. Type \q to exit the program and log in as the user you just created to install initial database.
``` 
$ cd src/Sql
$ mysql -u [username] -p edscc < install.sql
```
5. EDSCC uses IP2Location Database to track login location as a security measure and notifies users if a new login location was detected. The database needs to be downloaded and installed in the `database` folder. You have to register for an account at ip2location.com and download [DB9 LITE version](https://lite.ip2location.com/database/ip-country-region-city-latitude-longitude-zipcode) or download a [commerical database](https://www.ip2location.com/databases/db9-ip-country-region-city-latitude-longitude-zipcode) for a premium data.

### Configurations
1. Copy .env to .env.local and change the settings accordingly.
```
$ cd ../..
$ cp .env .env.local
$ vi .env.local
```
2. Configure `APP_SECRET` to a new value, as it is necessary to protect the user session data from XSS attacks. `APP_ENV` takes two possible modes, `dev` or `prod` for development or production mode respectively. `prod` is recommended if you will not be updating the code base frequently.
3. `DATABASE_URL` is your database server connection settings.
4. `MAILER_URL` will be your local sendmail server or use gmail as your mail transporter but that requires extra configurations on Google's end. `MAILER_FROM` is the from e-mail address.
5. `APP_REMOTE_ADDR` is the server environment variable for remote IP of the user. If your server setup uses load balancing or proxy, you will need to specify the environment variable to the real IP address.
5. `IP2LOCATION_PATH` is the path to IP2Location database. Change the filename if different from default.
6. Configure your apache2 settings for Symfony 4 framework. The source has already included .htaccess for apache2. Refer to [Symfony 4 documentation](https://symfony.com/doc/current/setup/web_server_configuration.html) for the Apache2 or Nginx setup. See below for examples.
7. Configure crontab to set up a cron job to execute log files import processing script in the background.

```
$ crontab -e
```
Add the following lines in the editor. It will run at various time depending on the tasks. You can change the frequency if you'd like. It's best to keep them staggered to prevent several tasks from running at the same time. Refer to [Crontab reference guide](https://linuxconfig.org/linux-crontab-reference-guide) for more information.
```
*/5 * * * * cd /path/to/base/dir/edscc; bin/console app:import-queue >/dev/null 2>&1
1-59/5 * * * * cd /path/to/base/dir/edscc; bin/console app:capi-queue >/dev/null 2>&1
*/10 * * * * cd /path/to/base/dir/edscc; bin/console app:capi-refresh-token >/dev/null 2>&1
0 1 * * * cd /path/to/base/dir/edscc; bin/console app:capi-autoupdate >/dev/null 2>&1
```


#### Sample HTTP Apache2 Configs
```apacheconfig
<VirtualHost *:80>

    ServerAdmin webmaster@dontcare.com
    DocumentRoot /path/to/base/dir/edscc/public
    ErrorLog ${APACHE_LOG_DIR}/edscc-error.log
    CustomLog ${APACHE_LOG_DIR}/edscc-access.log combined

    ServerName <your server hostname>

    php_value upload_max_filesize 20M
    php_value post_max_size 21M

    <Directory /path/to/base/dir/edscc>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
</VirtualHost>
```

#### Sample HTTPS Apache2 Configs
```apacheconfig
<IfModule mod_ssl.c>
    <VirtualHost *:443>
    
        ServerAdmin webmaster@dontcare.com
        DocumentRoot /path/to/base/dir/edscc/public
        ErrorLog ${APACHE_LOG_DIR}/edscc-error-ssl.log
        CustomLog ${APACHE_LOG_DIR}/edscc-access-ssl.log combined
        ServerName <your server hostname>
    
        php_value upload_max_filesize 20M
        php_value post_max_size 21M
    
    <Directory /path/to/base/dir/edscc>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    SSLCertificateFile /path/to/cert/files/fullchain.pem
    SSLCertificateKeyFile /path/to/cert/files/privkey.pem
    Include /path/to/cert/files/options-ssl-apache.conf
    
    </VirtualHost>
</IfModule>
```

### Post-Configuration Install Steps
1. Run a script to download and import minor faction data from eddb.io.
```
$ cd /path/to/base/dir/edscc
$ bin/console app:install-minor-faction
```

### Contributing
Contributions are welcome and will be fully credited. We accept contributions through pull requests on [GitHub](https://github.com/shuki25/edscc). This code base is built on Symfony 4 framework, all contributions must be based off on that.

### Pull Requests
* [PSR-2 Coding Standards](https://www.php-fig.org/psr/psr-2/)
* **Document any changes in behaviors** - revise README.md and any other documentations as needed.
* **Create feature branch** - Do not ask us to pull from your master branch.
* **One pull request per feature** - If you want to contribute more, please do one thing at a time and send multiple pull requests.
* **Coherent commit history** - Each individual commit in your pull request needs to be meaningful. If you make multiple intermediate commits during development, please squash them before submitting.

### Additional Tips
1. If you are the site administrator for the web app and would like to add Message of the Day (MOTD) messages, you would need to have a super user privileges. First, create an account on the site and create a squadron for you to manage. Then edit your permissions directly in the database (e.g. mysql line command or a GUI app) and add `["ROLE_SUPERUSER"]` (json format) in the column `roles` in the table `user` under your user account. This will give you a super user privileges on the site.
```sql
UPDATE user SET roles='["ROLE_SUPERUSER"]' WHERE id='<your user>'
```
2. If you want to populate your site with dummy data for development and testing when adding a new feature for a pull request, there are line commands that you can run from the console shell. Use `-h` to see options for the line command. By default, it will create 100 users and 10 squadrons and dummy data will be populated. It is a long process to execute depending on the number of users and squadrons are being created and your server's processing power.
```
$ cd /path/to/base/dir/edscc
$ bin/console app:load-dummy-data -h
```
3. After you load the database with dummy data, you can set up a cron job to incrementally add dummy data daily so that there are some new data added each day. Example crontab entry that runs at 9 a.m. and 4 p.m. daily:
```
0 9,16 * * * cd /path/to/base/dir/edscc; bin/console app:load-dummy-daily-data
```
4. If you want to update minor factions data every Sunday morning at 2 a.m., you can add this to the crontab as well. Or run it manually with the `-u` option.
```
0 2 * * 0 cd /path/to/base/dir/edscc; bin/console app:install-minor-faction -u >/dev/null 2>&1
```

### Contacting Maintainer
You can drop a message to shuki25#1766 on [EDCD discord](https://discord.gg/zQjjutY). If you want to contribute, please make a new issue to describe a new feature you would like to create to ensure that the work would not be duplicated by someone else who is already working on. Otherwise, leave a note

### Credits / Acknowledgements
* "Elite Dangerous" is &copy;1984 - 2019 Frontier Developments plc.
* Thanks to Cmdr Athalen for German translation.
* Thanks to Cmdr QUSTO for Russian translation.
* Thanks to Marginal for [EDMC](https://github.com/marginal/EDMarketConnector).
* Uses [gitlocalize.com](https://gitlocalize.com) for translation management.

### License
GNU GPL-3.0

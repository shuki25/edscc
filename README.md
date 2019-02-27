# ED:SCC
*Elite Dangerous: Squadron Command Center* is a web application for Elite Dangerous gamers to track their gaming progress over time and compare their performance with other gamers and squadrons. As well as for squadron leaders to track and monitor squadron's activities for strategies or lore role play.

### Beta and Demo Servers
We have two servers for you to check out the features or for your use for data analysis.  The demo server is populated with fake data so that you can see what the site would look like when it is enriched with data rather than starting out from blank and not being able to view dashboard with line graphs and statistics.

Beta Server: [beta.edscc.net](https://beta.edscc.net)
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
5. Configure your apache2 settings for Symfony 4 framework. The source has already included .htaccess for apache2. Refer to [Symfony 4 documentation](https://symfony.com/doc/current/setup/web_server_configuration.html) for the Apache2 or Nginx setup.

### Contributing
Contributions are welcome and will be fully credited. We accept contributions through pull requests on [GitHub](https://github.com/shuki25/edscc).

### Pull Requests
* PSR-2 Coding Standards
* **Document any changes in behaviors** - revise README.md and any other documentations as needed.
* **Create feature branch** - Do not ask us to pull from your master branch.
* **One pull request per feature** - If you want to contribute more, please do one thing at a time and send multiple pull requests.
* **Coherent commit history** - Each individual commit in your pull request needs to be meaningful. If you make multiple intermediate commits during development, please squash them before submitting.

### License
GNU GPL-3.0

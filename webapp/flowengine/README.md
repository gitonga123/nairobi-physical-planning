# Permitflow v2.5.0 #

Permitflow is a workflow management system targetted towards eGovernment services. It comes with a dynamic application form builder, dynamic workflow management, payments management and permit management modules.

### What is this repository for? ###

* These are the latest installable permitflow files re-written using symfony 1.5.8 and applying best practises with unit/functional tests
* v2.5.0
* [Learn Markdown](https://bitbucket.org/tutorials/markdowndemo)

### How do I get set up? ###

* Summary of set up (Using installation wizard)
    1. Clone this project to a folder in your server e.g. /var/www/html/permitflow
    2. cd ~/permitflow
    3. Run php composer.phar update(https://getcomposer.org/download/)
	4. Configure virtual server
    4. Open a browser and point to the server e.g. http://localhost/
    5. Use the installation wizard to configure the organisation settings
    6. Enter details for the system administrator account
    7. Log into the backend to configure the system e.g. http://localhost/plan

* Configuration
    - After installation there are two wizards you can run:
      1. Security Configuration Wizard - Setup administrators, groups and credentials
      2. Workflow Configuration Wizard - Configure stages, departments and other workflow related settings

* Dependencies
    - PHP 7+
    - Nginx
    - MariaDB (MySQL)
    - Composer
    - php-curl (If pushing data to remote servers)

* Migrations
    1. change the database schema (permitflow/permitflow_src/config/database/schema.yml)
    2. create or modify models manually in the permitflow/lib/models/doctrine
    3. ```php symfony doctrine:build-sql``` -  to generate new sql with modifications
    4. ```php symfony doctrine:insert-sql``` - to build model files from current schema / only on new installations

* How to run tests
    - ```php symfony test:all``` - Launches all tests
    - ```php symfony test:coverage``` - Outputs test code coverage
    - ```php symfony test:functional``` - Launches functional tests
    - ```php symfony test:unit``` - Launches unit tests

### Contribution guidelines ###

* Writing tests
  - [Symfony Unit Tests](http://symfony.com/legacy/doc/jobeet/1_4/en/08?orm=Doctrine)
  - [Symfony Functional Tests](http://symfony.com/legacy/doc/jobeet/1_4/en/09?orm=Doctrine)

* Code review
  - This can be achieved using bitbucket.
  - Changes to be written into seperate branches and pushed online.
  - After branches are pushed online, a pull request to merge into the master is made
  - Code is analyzed and if everything is good, the pull request is approved and the code is merged into the master branch

### Who do I talk to? ###

* OTB Africa: info@otbafrica.com
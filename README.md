Requirements
============

This application is built with

- Silex Framework
- Twig
- Symfony Security CSRF Component
- Symfony Validator Component
- Symfony Monolog Component
- Swift Mailer Component
- PHP 7

For Development

- Codeception

Setup
=====

You will need composer to install all the dependency library.

```
composer install
```

Configuration
=============

The application is configure through environment variable.

- environment dev for running in PHP standalone server
- receiver_email Receiver Email Address

Test
====

To run test, you need codeception and selenium server

Download Codeception phar, Selenium standalone server and Chrome Driver.

Then, run Selenium standalone server and Chrome Driver

```
java -Dwebdriver.chrome.driver=./chromedriver -jar selenium-server-standalone-3.4.0.jar
```

Run codeception

```
codeception run
```

Running
=======

To run the application on development environment

```
environment=dev php -S localhost:8888 -t public/ src/index.php
```

FAQ
===

1. How to retrieve enquiry?
   - All enquiry is store in enquiry.csv file.

2. Security consideration in the application?
   - Form submission will only be successful with the correct nonce which
   get generated the page load.
   - Validation of client's input are perform on frontend in Javascript
   and backend in PHP.

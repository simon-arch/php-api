## About
API containing a set of CRUD operations to work with remote JSON datafile.
> Completed as part of the technical practice for 2025.

## Prerequisites
[**PHP 8.1**](https://www.php.net/releases/8.1/en.php)
[Symfony-cli](https://symfony.com/download)
[Composer](https://getcomposer.org/download/)

Enable PHP extensions:
- curl *(optional)*
- openssl
- sodium

## Setup Guide
Clone this repository:
```bash
git clone https://github.com/simon-arch/php-api.git
```
Install composer.json dependencies:
```bash
composer install
```
**Important!** Change *JWT_PASSPHRASE* variable in .env:
```php
JWT_PASSPHRASE = <secure-secret>
```
Generate JWT SSL keypair:
```bash
php bin/console lexik:jwt:generate-keypair
```
Start Symfony server:
```bash
symfony serve
```

## API Endpoints
Check out Postman docs:
[Postman documentation](https://documenter.getpostman.com/view/41722328/2sAYX3qiDV)
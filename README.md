# Digitalocean API shell
Console utility for working with DigitalOcean API

### Requirements
* PHP 7.4+
* [Composer](https://getcomposer.org)
* PHP [Curl](https://www.php.net/manual/book.curl.php) extension
* PHP [GMP](https://www.php.net/manual/book.gmp.php) extension
* PHP [JSON](https://www.php.net/manual/book.json.php) extension

### Installation
* Run `composer create-project grayfolk/digitalocean-api-shell`.
* Go to `digitalocean-api-shell` folder and edit `accounts.json`. This is a simple [JSON](https://www.json.org) file with `"username" : "API Key"` pairs. You can use whatever username is convenient for you - it's not related to DigitalOcean and used as alias only (but it shoud be unique) and real DigitalOcean API key. You can obtain API key in your [DigitalOcean Account - API - Personal access tokens - Generate New Token](https://cloud.digitalocean.com/account/api/tokens/new).

### Running
Go to `digitalocean-api-shell` folder and run `./do`.

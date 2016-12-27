TGL API Client
============

PHP client for TGL's REST API.

##Dependencies
This library depends on the following projects:
- eelkevdbos/firebase-php [github](https://github.com/eelkevdbos/firebase-php/releases/tag/0.1.3)

## Basic Usage

```php
require_once __DIR__ . '/tglApiClient.php';

// Sign in with the API key you received
$key ="YOUR API KEY";
$client = new TglApiClient();
$client->signInWithApiKey($key);

// Retrieve a document JSON by its ID
$document = $client->getDocument("ID OF A DOCUMENT");

```

## How to use the data
Please have a look at the explanation on the site of our [Wordpress plugin](https://github.com/the-green-lion/wp-tgl-content-insert)
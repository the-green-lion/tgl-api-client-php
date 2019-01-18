TGL API Client
============

PHP client for TGL's REST API.

You may also be interested in our [JS](https://github.com/the-green-lion/tgl-api-client-js) and [.Net](https://github.com/the-green-lion/tgl-api-client-csharp) client libraries or our [Wordpress Plugin](https://github.com/the-green-lion/wp-tgl-content-insert)

##Dependencies
This library depends on the following projects:
- ktamas77/firebase-php [github](https://github.com/ktamas77/firebase-php/)

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

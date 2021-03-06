<?php

require_once __DIR__ . '\..\src\tglApiClient.php';


// Instantiate API client and sign in
$key = "<<YOUR API KEY>>";
$secret = "<<YOUR API SECRET>>";
$client = new TglApiClient();
$client->signIn($key, $secret);

// Load the IDs of all program documents
$ids = $client->listDocuments("program");

// Get the Thailand Umphang location document
$document = $client->getDocument("1qHoygXnd8S3JSzFqteOtSXXM-LMTdDGS6658nKeRwqQ");

// Get the price for upgrading upgrading the room to $name
$name = $document->accommodation->types[1]->name;
$price = $document->accommodation->types[1]->upgradePrice->currentPrice;

// Suppose our retail price is 45USD per point
echo "Upgrade to " . $name . " costs " . ($price * 45) . "USD per week.";
?>
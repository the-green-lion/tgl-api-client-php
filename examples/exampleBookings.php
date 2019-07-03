<?php

require_once __DIR__ . '\..\src\tglApiClient.php';


// Instantiate API client and sign in
$key = "<<YOUR API KEY>>";
$secret = "<<YOUR API SECRET>>";
$client = new TglApiClient("https://api-dev.thegreenlion.net/");
$client->signIn($key, $secret);

$impersonateUserId = '1K5iLYMq9Lvy1mvLpq4kGHoCgqTQeEoiYaUvDwsIwcHg';


//date_default_timezone_set('UTC');
$newBooking = array(
        'reference' => "K0038", // Optional. Your internal customer number.
        //'dateArrival' => date("Y-m-d\TH:i:s\Z", mktime(0, 0, 0, 3, 26, 2017)), // Optional
        'comment' => "API Test", // Optional
        'dateStart' => date("Y-m-d\TH:i:s\Z", mktime(0, 0, 0, 11, 15, 2021)),
        'fees' => array( // Optional
            array(
                'quantity' => 5,
                'sku' => "upgrade-comfort"
            )
        ),
        //'flightNumber' => "DE123", // Optional,
        'participant' => array(
            'birthday' => date("Y-m-d\TH:i:s\Z", mktime(0, 0, 0, 3, 11, 2002)),
            'countryCode' => 'DE', //Nationality as ISO 3166-1 alpha-2 code
            'email' => 'test@gmail.com', // Optional
            'firstName' => 'Claus',
            'gender' => 0, // 0=Male, 1=Female
            'lastName' => 'Santa',
            'passportNumber' => 'XK2F11LA5' // Optional
        ),
        'programs' => array(
            array( 'id' => '1mKY3DddPftfvIDPK959drDwyOnTIuLiHUf3gWqdhZ9A' ),
            array( 'id' => '1bkBXHXgtsqCkSsi73SaIbT2CTzo-kPN7mazp4sT9o0k' ),
            array( 'id' => '1bkBXHXgtsqCkSsi73SaIbT2CTzo-kPN7mazp4sT9o0k' ),
            array( 'id' => '1bkBXHXgtsqCkSsi73SaIbT2CTzo-kPN7mazp4sT9o0k' ),
            array( 'id' => '1bkBXHXgtsqCkSsi73SaIbT2CTzo-kPN7mazp4sT9o0k' )
        )
    
);

// Retrieve all bookings that user or API key has access to
// Depending on the permission settings, the could be bookings made by the user or bookings made by any user in the organization 
$allBookings = $client->listBookings(null, 1);

// Make a new booking. We'll get back the booking ID
// We need to make bookings in the name of a specific user by passing its ID
// This user will show up in the booking system as the one who created this booking
$bookingId = $client->createBooking($newBooking, $impersonateUserId);

// Retrieve that booking again from the API
$booking = $client->getBooking($bookingId);


// Now the participant confirmed his arrival details. Update booking.
$booking->dateArrival = date("Y-m-d\TH:i:s\Z", mktime(10, 5, 0, 11, 14, 2019));
$booking->flightNumber = "DE123";
$client->updateBooking($bookingId, $booking);

// Participant canceled his trip.
// We will later still be able to get this booking via the API but 'isCanceled' will be 'true'
$client->cancelBooking($bookingId);

// Retrieve all bookings with a certain reference
$filter = array(
    //'isCanceled' => true,
    //'dateStartBefore' => '2017-05-11',
    //'dateStartAfter' => '2017-02-11'
    'reference' => 'K0038',
    //'email' => 'test@test.com' 
);
$bookings = $client->listBookings($filter, 1);


echo $booking;
?>
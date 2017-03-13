<?php

// Use firebase library from here: https://github.com/ktamas77/firebase-php
require_once __DIR__ . '/firebaseLib.php';

/**
 * PHP Client Library for TGL's REST API
 *
 * @author Bernhard Gessler <bernhard@thegreenlion.net>
 * @url    https://github.com/the-green-lion/tgl-api-client-php/
 */
class TglApiClient
{
    private static $FIREBASE_URL = 'https://tgl-api-20e32.firebaseio.com';
    private static $FIREBASE_KEY = 'AIzaSyC5Fw0sHmxEg7-S1iylkQ68WN6X2rlGq8M';

    public $token = null;
    private $tokenFirebase = null;
    private $firebaseClient = null;

    private $urlEndpointBookings = "https://api.thegreenlion.net/bookings%s?auth=%s%s";

    public function signInWithApiKey($apiKey)
    {
        $result = $this->signInTgl($apiKey);
        if ($result === FALSE) { 
          return FALSE;
        }
         
        $result = $this->signInFirebase();
        if ($result === FALSE) { 
          return FALSE;
        }

        return true;
    }

    private function signInTgl($apiKey)
    {
        // use key 'http' even if you send the request to https://...
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/json\r\nContent-Length: 0\r\n",
                'method'  => 'POST',
                'content' => ""
            )
        );

        $context  = stream_context_create($options);

        $payload = file_get_contents("https://api.thegreenlion.net/user/".$apiKey."/authenticate", false, $context);
        if($payload === FALSE) {
          return FALSE;
        }
        
        $result = json_decode($payload);
        $this->token = $result->token;

        return TRUE;
    }

    private function signInFirebase()
    {
        $url = "https://www.googleapis.com/identitytoolkit/v3/relyingparty/verifyCustomToken?key=".self::$FIREBASE_KEY;
        $data = array('token' => $this->token, 'returnSecureToken' => 'true');
        
        // use key 'http' even if you send the request to https://...
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($data)
            )
        );

        $context  = stream_context_create($options);
        $payload = file_get_contents($url, false, $context);
        if ($payload === FALSE) { 
          return FALSE;
        }

        $result = json_decode($payload);
        $this->tokenFirebase = $result->idToken;
        $this->firebaseClient = new \Firebase\FirebaseLib(self::$FIREBASE_URL, $this->tokenFirebase);

        return TRUE;
    }

    private function loadUserFirebase($token)
    {
    }

    
    // Get a specific country by its ID
    public function getDocument($id) {
        return json_decode($this->firebaseClient->get('content/'.$id));
    }

    

    // Bookings API
    public function listBookings($filter, $page)
    {
        $parameters = "&page=" . $page;
        if (isset($filter['isCanceled'])) $parameters .= "&isCanceled=" . $filter['isCanceled'];
        if (isset($filter['dateStartBefore'])) $parameters .= "&dateStartBefore=" . $filter['dateStartBefore'];
        if (isset($filter['dateStartafter'])) $parameters .= "&dateStartafter=" . $filter['dateStartafter'];
        if (isset($filter['reference'])) $parameters .= "&reference=" . $filter['reference'];
        if (isset($filter['email'])) $parameters .= "&email=" . $filter['email'];

        // use key 'http' even if you send the request to https://...
        $options = array(
            'http' => array(
                'header'  => "Content-Length: 0\r\n",
                'method'  => 'GET'
            )
        );

        $context  = stream_context_create($options);

        $payload = file_get_contents(sprintf($this->urlEndpointBookings, '', $this->tokenFirebase, $parameters), false, $context);
        if($payload === FALSE) {
          return FALSE;
        }
        
        return json_decode($payload);
    }

    public function getBooking($id)
    {
        // use key 'http' even if you send the request to https://...
        $options = array(
            'http' => array(
                'header'  => "Content-Length: 0\r\n",
                'method'  => 'GET'
            )
        );

        $context  = stream_context_create($options);

        $payload = file_get_contents(sprintf($this->urlEndpointBookings, '/'.$id, $this->tokenFirebase, ''), false, $context);
        if($payload === FALSE) {
          return FALSE;
        }
        
        return json_decode($payload);
    }

    public function createBooking($booking)
    {
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($booking)
            )
        );

        $context  = stream_context_create($options);
        
        $payload = file_get_contents(sprintf($this->urlEndpointBookings, '', $this->tokenFirebase, ''), false, $context);
        if($payload === FALSE) {
          return FALSE;
        }
        
        return json_decode($payload)->id;
    }

    public function updateBooking($id, $booking)
    {
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'PUT',
                'content' => json_encode($booking)
            )
        );

        $context  = stream_context_create($options);
        
        $payload = file_get_contents(sprintf($this->urlEndpointBookings, '/'.$id, $this->tokenFirebase, ''), false, $context);
        if($payload === FALSE) {
          return FALSE;
        }
        
        return TRUE;
    }

    public function cancelBooking($id)
    {
        $options = array(
            'http' => array(
                'method'  => 'DELETE',
                'content' => ""
            )
        );

        $context  = stream_context_create($options);
        
        $payload = file_get_contents(sprintf($this->urlEndpointBookings, '/'.$id, $this->tokenFirebase, ''), false, $context);
        if($payload === FALSE) {
          return FALSE;
        }
        
        return TRUE;
    }
}
?>
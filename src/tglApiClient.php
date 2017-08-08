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
    public $userId = null;

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

        $result = $this->loadUserFirebase();
        if ($result === FALSE) { 
          return FALSE;
        }        

        return true;
    }

    private function signInTgl($apiKey)
    {
        /*//  Initiate curl
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json', 'Content-Length: 0'));
        curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.52 Safari/537.17');
        curl_setopt($ch, CURLOPT_AUTOREFERER, true); 
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);

        // Disable SSL verification
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        // Will return the response, if false it print the response
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Set the url
        curl_setopt($ch, CURLOPT_URL,"https://api.thegreenlion.net/user/".$apiKey."/authenticate");
        
        // Execute
        echo curl_exec($ch);
        $payload = curl_exec($ch);
        //$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        //error_log( 'Payload' );
        //error_log( $payload );
        //error_log( 'Error Code' );
        //error_log( $code );
        //$infoFull = curl_getinfo($ch);
        //error_log( 'CURL Info' );
        //error_log( json_encode($infoFull) );
        $errorFull = curl_error($ch);
        error_log( 'CURL Error' );
        error_log( $errorFull );

        // Closing
        curl_close($ch);*/

        // use key 'http' even if you send the request to https://...
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/json\r\nContent-Length: 0\r\n",
                'method'  => 'POST',
                'content' => ""
            ),
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
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

    private function loadUserFirebase()
    {
        $url = "https://www.googleapis.com/identitytoolkit/v3/relyingparty/getAccountInfo?key=".self::$FIREBASE_KEY;
        $data = array('idToken' => $this->tokenFirebase);
        
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
        $this->userId = $result->users[0]->localId;

        return TRUE;
    }

    
    // Get the IDs of all documents of a type
    public function listDocuments($documentType) {
        
        $userData = json_decode($this->firebaseClient->get('users/'.$this->userId));
        $ids = $this->firebaseClient->get('permissions/'.$userData->agentId.'/'.$documentType);
        if($ids === FALSE) {
          return FALSE;
        }
        
       return array_keys((array)json_decode($ids));
    }
    
    // Get a specific document by its ID
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
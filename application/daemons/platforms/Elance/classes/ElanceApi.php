<?php

class ElanceApi
{
    const API_TRANSPORT = 'https';

    const API_HOST_PRODUCTION = 'api.elance.com/api2';

    const API_HOST_SANDBOX = 'api.elance.com/api2';

    const AUTH_TOKEN_TIMEOUT = 9999999999;

    const OAUTH_DEBUG = TRUE;

    const USAGE_STATS = TRUE;

    protected $auth;

    protected $api_base;

    protected $callUrl;

    private $consumer_key;

    private $consumer_secret;


    public function __construct( $consumer_key, $consumer_secret, $is_production = FALSE, $transport = self::API_TRANSPORT )
    {
        // Assign the API users credentials in a place we can get at them
        $this->consumer_key = $consumer_key;

        $this->consumer_secret = $consumer_secret;

        // Define the API host base
        if ( $is_production )
        {
            $this->api_base = $transport . '://' . self::API_HOST_PRODUCTION;
        }
        else
        {
            $this->api_base = $transport . '://' . self::API_HOST_SANDBOX;
        }

        $this->authorize( );

    }

    public function authorize( $token_filename = null )
    {
        $auth = array ();

        // Name the auth token data filename
        if ( is_null( $token_filename ) )
        {
            $token_filename = sys_get_temp_dir( ) . '/elance_api_' . md5( $this->consumer_key . $this->consumer_secret . $this->api_base ) . '.token';
        }

        // Check if the cache file exists
        if ( file_exists( $token_filename ) )
        {
            $auth = json_decode( file_get_contents( $token_filename ), TRUE );
        }

        // Check if the token data file data has timed out
        if ( !isset( $auth[ 'timestamp' ] ) or ( time( ) - $auth[ 'timestamp' ] > self::AUTH_TOKEN_TIMEOUT ) )
        {
            $authToken = $this->getAuthToken( );

            $auth[ 'timestamp' ] = time( );

            $auth[ 'access_token' ] = $authToken->access_token;

            $auth[ 'refresh_token' ] = $authToken->refresh_token;

            $auth[ 'expires_in' ] = $authToken->expires_in;

            file_put_contents( $token_filename, json_encode( $auth ) );
        }

        // Place this auth data in the publicly acessable $this->auth so others can see that data if required.
        $this->auth = $auth;	

        // Return the request token that this authorization is for
        return $auth[ 'access_token' ];
    }


    private function getAuthToken()
    {
        $accessTokenUrl = $this->api_base . '/oauth/token';

        $r = $this->InitCurl( $accessTokenUrl );

        $post_fields = "client_id=" . $this->consumer_key . "&client_secret=" . $this->consumer_secret . "&grant_type=client_credentials";

        curl_setopt( $r, CURLOPT_POST, true );

        curl_setopt( $r, CURLOPT_POSTFIELDS, $post_fields );

        $response = curl_exec( $r );

        if ( $response == false )
        {
            die( "curl_exec() failed. Error: " . curl_error( $r ) );
        }

        $data = json_decode( $response );

        return $data->data;
    }

    private function InitCurl( $url )
    {
        $r = null;

        if ( ( $r = @curl_init( $url ) ) == false )
        {
            header( "HTTP/1.1 500", true, 500 );
            die( "Cannot initialize cUrl session. Is cUrl enabled for your PHP installation?" );
        }

        curl_setopt( $r, CURLOPT_RETURNTRANSFER, 1 );

        curl_setopt( $r, CURLOPT_ENCODING, 1 );

        curl_setopt( $r, CURLOPT_SSL_VERIFYPEER, false );

        curl_setopt( $r, CURLOPT_CAINFO, "C:\wamp\bin\apache\Apache2.2.21\cacert.crt" );

        return ( $r );
    }


    public function getJobList( $params = array( ) )
    {
        $call_url = $this->api_base . '/jobs';

        $data = $this->fetch( $call_url, $params );

        if ( isset( $data->data ) )
        {
            return $data->data;
        }
        else
        {
            return $data; // ie return error data
        }
    }


    private function fetch( $url, $params = array() )
    {
        // Check that an authorization call has been made before we get here
        if ( !isset( $this->auth[ 'timestamp' ] ) )
        {
            echo 'ERROR: Developer has not yet called authorize() before making Elance API calls' . "\n\n";

            die( __METHOD__ );
        }

        try
        {
            //$fields = '?access_token=' . $this->auth[ 'access_token' ];
            $fields = '?access_token=4f21faa83340a00328000001|4905439|bW-7K-euwpD7aUa5KGMohA';

			//print $fields; die;
			
            if ( !empty( $params ) )
            {
                foreach( $params as $key => $value )
                {
                    $fields .= '&' . $key . '=' . $value;
                }
            }

            $url = $url . $fields;
			
			$r = $this->InitCurl($url);
			
            $response = curl_exec( $r );

            if ( $response == false )
            {
                die( "curl_exec() failed. Error: " . curl_error( $r ) );
            }
			
			

            $data = json_decode( $response );
			
            return $data->data;
        }
        catch ( Exception $e )
        {
            echo 'ERROR: unable to perform fetch from :- ' . $url . "\n\n";

            die( __METHOD__ );
        }
    }
}
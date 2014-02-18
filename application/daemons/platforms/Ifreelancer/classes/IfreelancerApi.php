<?php

require_once( 'Zend/Dom/Query.php' );

require_once( 'Zend/Http/Client.php' );

class IfreelancerApi
{
    public $startUrl = 'http://www.freelance.com/en/search/mission';

    public function __construct()
    {

        $html = $this->getDocumentContents( $this->startUrl );

        $dom = new Zend_Dom_Query( $html );

        $results = $dom->query( '#result tbody tr' );

        foreach ($results as $r)
        {

//            echo $r->textContent . "\n";
//            echo "Parent Tag: " . $r->nodeName . "\n\n";

//            echo "Node Value: " . $r->nodeValue . "\n\n";

            $children = $r->getElementsByTagName( 'td' );

            if( $children->length > 0 )
            {
                foreach( $children as $c )
                {
                    if ( $c->hasAttribute( 'date' ) )
                    {
                        echo $c->nodeValue . "\n";
                    }
//                    print_r($c->nodeValue);

#                    echo "Child Tag: " . $c->nodeName."\n\n";

 //                   echo $c->getAttribute( 'class' ) . "\n";

//                    echo $c->nodeValue."\n\n";
                }

            }

            //echo $result->getAttribute( 'class' ) . "\n";
        }
    }

    public function getDocumentContents( $url, $params = array( ), $method = 'GET' )
    {
        $client = new Zend_Http_Client( $url );

        if ( !empty( $params ) && $method == 'POST' )
        {
            foreach( $params as $key => $value )
            {
                $client->setParameterPost( $key, $value );
            }
        }

        switch( $method )
        {
            case 'POST':
                $response = $client->request( Zend_Http_Client::POST );

            case 'GET':
            default:
                $response = $client->request();

        }

        return $response->getBody();
    }
}

new IfreelancerApi();
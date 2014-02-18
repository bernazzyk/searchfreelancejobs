<?php

class OdeskDaemon extends ServiceDaemonAbstract
{
    const live                  = false;

    const perPage               = 200;


    public $platformId          = 4;


    private $consumerKey        = null;

    private $consumerSecret     = null;


    /**
     *
     *
     */
    public function __construct( )
    {
        $odesk_user = 'freelancerfm';

        $odesk_pass = 'Emp12345';

        $secret     = '6c21f6884bcbd3cc';

        $api_key    = '8f3e8ef823d8a928240d48309f1cf054';

        $company    = 'freelancerfm';

        $this->api = new OdeskApi( $secret, $api_key );

        $this->api->option( 'mode', 'web' );

        /**
         *  Check filesystem here...
         */
        if ( !isset( $_SESSION['saved_token_id'] ) )
        {
            $token = $this->api->auth( $odesk_user, $odesk_pass );

            #$_SESSION['saved_token_id'] = $token; // save your token using prefered method
        }
        else
        {
            $this->api->option('api_token', $_SESSION['saved_token_id']); // use saved token, then you app do not require
                                                                          // login and auth again
        }
    }




    /**
     *
     * @param integer $page
     *
     * @throws Exception
     *
     */
    public function searchProjects( $page = 1 )
    {
        $perPageEnd         = $page * self::perPage;

        $perPageStart       = $perPageEnd - self::perPage;

        $resultsRange       = $perPageStart . ';' . $perPageEnd;

        $searchProjects     = $this->api->searchProjects( array( 'page' => $resultsRange ) );

        $resultsCount       = 5000;

        $numberOfPages      = ceil( $resultsCount / self::perPage );

        if ( $page > $numberOfPages )
        {
            return true;
        }

        foreach( $searchProjects->jobs->job as $project )
        {
            try
            {
                if ( is_object( $project ) )
                {
                    $this->addUpdateProject( new OdeskDetailsValidator( $project ) );
                }
                else
                {
                    throw new Exception( "No details found for project id {$project->op_recno}" );
                }
            }
            catch( Exception $e )
            {
                echo "\n\nEXCEPTION :: " . $e->getMessage( ) . "\n\n";
            }
        }

        $searchProjects = null;

        $resultsCount = null;

        $numberOfPages = null;

        $project = null;

        echo "Page {$page}\n";

        $page++;

        $this->searchProjects( $page );
    }
}
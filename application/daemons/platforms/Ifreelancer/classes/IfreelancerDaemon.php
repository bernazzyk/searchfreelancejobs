<?php

class IfreelancerDaemon extends ServiceDaemonAbstract
{
    const live                  = false;

    const perPage               = 200;


    public $platformId           = 1;


    private $consumerKey        = null;

    private $consumerSecret     = null;


    /**
     *
     *
     */
    public function __construct( )
    {
        if ( true === self::live )
        {
            $this->consumerKey     = 'e2cd52f6417d0c0dcf6d7a81b6120132086e44ec';

            $this->consumerSecret  = 'db1e5f8c5dde1208ae154e62dec238db6a9a4219';
        }
        else
        {
            $this->consumerKey     = '209dda8981fd6a37be106cbee02cc9d4bec42ce4';

            $this->consumerSecret  = 'b847fab7e91ffe4f7fe8c317017ae45c226deb73';
        }

        $this->api = new Freelancer( $this->consumerKey, $this->consumerSecret, self::live );

        $token = $this->api->authorize( );

        echo "Using authorized token: {$token}\n";
    }


    /**
     *
     * @param integer $page
     *
     * @throws Exception
     *
     */
    public function searchProjects( $page = 0 )
    {
        $searchProjects     = $this->api->searchProjects( array( 'page' => $page, 'status' => 'Open', 'count' => self::perPage ) );

        $resultsCount       = $searchProjects['json-result']['results_count'];

        $numberOfPages      = ceil( $resultsCount / self::perPage );

        if ( $page > $numberOfPages )
        {
            return true;
        }

        foreach( $searchProjects['json-result']['items'] as $project )
        {
            $details = $this->api->getProjectDetails( array( 'projectid' => $project['projectid'] ) );

            try
            {
                if ( isset( $details['json-result'] ) && is_array( $details[ 'json-result' ] ) )
                {
                    $this->addUpdateProject( new FreelancerDetailsValidator( (object ) $details[ 'json-result' ] ) );
                }
                else
                {
                    throw new Exception( "No details found for project id {$project[ 'projectid' ]}" );
                }
            }
            catch( Exception $e )
            {
                echo "\n\nEXCEPTION :: " . $e->getMessage( ) . "\n\n";
            }
        }

        $page++;

        $this->searchProjects( $page );
    }
}
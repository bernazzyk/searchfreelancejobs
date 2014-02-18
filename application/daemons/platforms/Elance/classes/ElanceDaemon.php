<?php

class ElanceDaemon extends ServiceDaemonAbstract
{
    const live                  = false;

    const perPage               = 25;


    public $platformId          = 3;


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
            $this->consumerKey     = '4f21faa83340a00328000001';

            $this->consumerSecret  = 'tYL5A3ymBkl0zjwokx4BjA';
        }
        else
        {
            $this->consumerKey     = '4f21faa83340a00328000001';

            $this->consumerSecret  = 'tYL5A3ymBkl0zjwokx4BjA';
        }

        $this->api = new ElanceApi( $this->consumerKey, $this->consumerSecret, self::live );
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
        $searchProjects     = $this->api->getJobList( array( 'page' => $page, 'rpp' => self::perPage ) );

        $resultsCount       = $searchProjects->totalResults;

        $numberOfPages      = $searchProjects->totalPages;

        if ( $page > $numberOfPages )
        {
            return true;
        }

        foreach( $searchProjects->pageResults as $project )
        {
            try
            {
                if ( is_object( $project ) )
                {
                    $this->addUpdateProject( new ElanceDetailsValidator( $project ) );
                }
                else
                {
                    throw new Exception( "No details found for project id {$project->jobId}" );
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

        $page++;

        $this->searchProjects( $page );
    }
}
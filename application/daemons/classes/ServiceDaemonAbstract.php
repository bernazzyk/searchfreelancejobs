<?php

abstract class ServiceDaemonAbstract
{
    public $api             = null;

    public $externalIdList  = array( );

    /**
     *
     *
     */
    public function go()
    {
        if ( true === $this->searchProjects( ) )
        {

            /**
             *
             * Find all existing records and prune out the stale ones by comparing against the list of freshly added / updated ones.
             *
             */
            $select = db( )->select( )->

            from( 'projects', array (

                'id'
            ) )->

            where( 'platform_id = ?', $this->platformId );

            $records = db( )->fetchAll( $select );

            foreach ( $records as $record )
            {
                if ( !in_array( $record[ 'id' ], $this->externalIdList ) )
                {
                    /**
                     *
                     * toasty!
                     *
                     */

                    db( )->delete( 'projects', 'id = ' . $record[ 'id' ] );
                }
            }
        }
    }

    /**
     *
     * @param FreelancerDetailsValidator $details
     *
     */
    protected function addUpdateProject( ServiceValidatorAbstract $details )
    {
        $projectExists = false;

        /**
         *
         * See if we have already added this job before
         *
         */
        $select = db( )
            ->select( )

            ->from( 'projects', array ( 'id' ) )

            ->where( 'platform_id = ?', $this->platformId )

        ->where( 'external_id = ?', $details->id( ) );


        $updateInsert = array
        (
            'external_url'     => $details->url( ),

            'title'            => $details->title( ),

            'description'      => $details->description( ),

            'posted'           => $details->formatDate( $details->dateStart( ) ),

            'ends'             => $details->formatDate( $details->dateEnd( ) ),

            'budget_low'       => $details->budgetLow( ),

            'budget_high'      => $details->budgetHigh( )
        );


        if ( $projectId = db( )->fetchOne( $select ) )
        {
            $projectExists = true;

            db( )->update( 'projects', $updateInsert, "id = {$projectId}" );

            echo "{$details->id( )} already exists. Not adding, but continuing to freshen up record, buyer & categories.\n";
        }
        else
        {
            /**
             *
             * Add some additional fields for inserting a new record.
             *
             */

            $updateInsert[ 'external_id' ] = $details->id( );

            $updateInsert[ 'platform_id' ] = $this->platformId;

            $updateInsert[ 'active' ] = 1;


            db( )->insert( 'projects', $updateInsert );


            $projectId = db( )->lastInsertId( );

            echo "Inserted new project with id {$projectId}\n";
        }


        /**
         *
         * Log the added / updated id so that we can compare it with our database contents later
         *
         */
        $this->externalIdList[ ] = $details->id( );

        /**
        *
        * Insert / update categories
        *
        */

        if ( true === $projectExists )
        {
            /**
             *
             * Fetch all current categories & compare them to the list supplied. Remove ones from the database that shouldn't be there.
             *
             */

            $select = db( )
                ->select( )

                ->from( array ( 'ppcr' => 'projects_project_categories_relation' ), array ( 'project_categories_id' ) )

                ->where( 'ppcr.project_id = ?', $projectId )

                ->join( array ( 'pc' => 'project_categories' ), 'pc.id = ppcr.project_categories_id', array ( 'name' ) );


            if ( $currentCategories = db( )->fetchAll( $select ) )
            {
                foreach ( $currentCategories as $category )
                {
                    if ( !in_array( $category[ 'name' ], $details->jobs( ) ) )
                    {
                        db( )->delete( 'projects_project_categories_relation', 'project_id = ' . $projectId . ' AND project_categories_id = ' . $currentCategories[ 'project_categories_id' ] );

                        /**
                         *
                         *  Check if this id is used by other projects and delete if necessary.
                         *
                         */

                        $select = db( )
                            ->select( )

                            ->from( 'projects_project_categories_relation' )

                            ->where( 'project_id != ?', $projectId );

                        if ( !db( )->fetchRow( $select ) )
                        {
                            /**
                             *
                             * Not in use by other projects, toast it.
                             *
                             */

                            db( )->delete( 'project_categories', 'id = ' . $category[ 'project_categories_id' ] );
                        }
                    }
                }
            }
        }


        if ( count( $details->jobs( ) ) > 0 )
        {
            foreach ( $details->jobs( ) as $job )
            {
                /**
                 *
                 * Check if category already exists and if not, add it.
                 *
                 */

                $select = db( )
                    ->select( )

                    ->from( 'project_categories', array ( 'id' ) )

                    ->where( 'name = ?', $job );

                if ( !( $categoryId = db( )->fetchOne( $select ) ) )
                {
                    echo "Category does not exist, adding...\n";

                    $insert = array
                    (
                        'name'         => $job,

                        'parent_id'    => 0,

                        'active'       => 1
                    );

                    db( )->insert( 'project_categories', $insert );

                    $categoryId = db( )->lastInsertId( );
                }


                /**
                 *
                 * Check if the category is already associated with this project
                 *
                 */

                $select = db( )
                    ->select( )

                    ->from( 'projects_project_categories_relation' )

                    ->where( 'project_id = ?', $projectId )

                    ->where( 'project_categories_id = ?', $categoryId );


                if ( !db( )->fetchOne( $select ) )
                {
                    $insert = array
                    (
                        'project_id'             => $projectId,

                        'project_categories_id'  => $categoryId
                    );

                    db( )->insert( 'projects_project_categories_relation', $insert );
                }
            }
        }


        /**
         *
         * Insert / Update buyer
         *
         */

        if ( null !== $details->buyer( ) && is_object( $details->buyer( ) ) )
        {
            $select = db( )
                ->select( )

                ->from( 'buyers' )

                ->where( 'external_id = ?', $details->buyer( )->id )

                ->where( 'platform_id = ?', $this->platformId );


            if ( db( )->fetchRow( $select ) )
            {
                /**
                 *
                 * Buyer already exists, update info.
                 *
                 */

                $update = array
                (
                    'url'     => $details->buyer( )->url,

                    'name'    => $details->buyer( )->username
                );

                db( )->update( 'buyers', $update, 'external_id = ' . $details->buyer( )->id . ' AND platform_id = ' . $this->platformId );
            }
            else
            {
                /**
                 *
                 * New buyer, insert and associate.
                 *
                 */

                $insert = array
                (
                    'external_id'     => $details->buyer( )->id,

                    'url'             => $details->buyer( )->url,

                    'name'            => $details->buyer( )->username,

                    'platform_id'     => $this->platformId
                );

                db( )->insert( 'buyers', $insert );

                $buyerId = db( )->lastInsertId( );


                $insert = array
                (
                    'buyer_id'     => $buyerId,

                    'project_id'   => $projectId
                );

                db( )->insert( 'buyers_projects_relation', $insert );
            }
        }

        $updateInsert         = null;

        $insert               = null;

        $select               = null;

        $buyerId              = null;

        $categoryId           = null;

        $details              = null;

        $currentCategories    = null;

        $category             = null;

        $job                  = null;

        $projectId            = null;
    }

}
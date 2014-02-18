<?php
class ElanceDetailsValidator extends ServiceValidatorAbstract
{
    /**
     *
     * Validaate validate validate!
     *
     */

    public function __construct( $details )
    {
        if
        (
            false === (
                isset( $details->jobId )

                || ( int ) $details->jobId > 0
            )
        )
        {
            throw new Exception( 'Invalid jobId' );
        }


        if
        (
            false === (
                isset( $details->jobURL )

                || strlen( $details->jobURL ) == 0

                || strlen( $details->jobURL ) < 255
            )
        )
        {
            throw new Exception( 'Invalid Url' );
        }

        if
        (
            false === (
                isset( $details->name )

                || strlen( $details->name ) == 0

                || strlen( $details->name ) < 255
            )
        )
        {
            throw new Exception( 'Invalid name' );
        }

        if
        (
            false === (
                isset( $details->description )

                || strlen( $details->description ) == 0

                || strlen( $details->description ) < 5000
            )
        )
        {
            throw new Exception( 'Invalid description' );
        }

        if
        (
            false === (
                isset( $details->startDate )

                || ( int ) $details->startDate > 0

                || $details->startDate < $details->endDate

                || $details->startDate < time( )
            )
        )
        {
            throw new Exception( 'Invalid start time.  Must be an integer, less than now and less than end_unixtime' );
        }

        if
        (
            false === (
                ( int ) $details->endDate > 0

                || $details->endDate > $details->startDate

                || $details->endDate < time( )
            )
        )
        {
            throw new Exception( 'Invalid end time.  Must be an integer, greater than now and greater than start_unixtime' );
        }

        if
        (
            false === (
                empty( $details->budgetMin )

                || ( int ) $details->budgetMin < ( int ) $details->budgetMax

            )
        )
        {
//            throw new Exception( 'Minimum budget must be less than max budget or empty. Min: ' . $details->budgetMin . ' Max: ' . $details->budgetMax );
        }

        if
        (
            false === (
                empty( $details->budgetMax )

                || ( int ) $details->budgetMax > ( int ) $details->budgetMin
            )
        )
        {
//            throw new Exception( 'Maximum budget must be greater than min budget or empty. Min: ' . $details->budgetMin . ' Max: ' . $details->budgetMax );
        }

        if
        (
            false === (
                isset( $details->category )

                || isset( $details->subcategory )
            )
        )
        {
            throw new Exception( 'Category must be set' );
        }

        if
        (
            false === (
                ( int ) $details->clientUserId > 0

                || isset( $details->clientUserName )

                || isset( $details->clientImageURL )
            )
        )
        {
            throw new Exception( 'Invalid buyer provided' );
        }


        $this->id( $details->jobId );

        $this->url( $details->jobURL );

        $this->title( $details->name );

        $this->description( $details->description );

        $this->dateStart( $details->startDate );

        $this->dateEnd( $details->endDate );

        $this->budgetLow( $details->budgetMin );

        $this->budgetHigh( $details->budgetMax );

        $this->jobs( array( $details->category . ' > ' . $details->subcategory ) );

        $this->buyer( ( object ) array( 'id' => $details->clientUserId, 'username' => $details->clientUserName, 'url' => $details->clientImageURL ) );
    }
}

<?php
class IfreelancerDetailsValidator extends ServiceValidatorAbstract
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
                isset( $details->id )

                || ( int ) $details->id > 0
            )
        )
        {
            throw new Exception( 'Invalid id' );
        }


        if
        (
            false === (
                isset( $details->url )

                || strlen( $details->url ) == 0

                || strlen( $details->url ) < 255
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
                isset( $details->short_descr )

                || strlen( $details->short_descr ) == 0

                || strlen( $details->short_descr ) < 5000
            )
        )
        {
            throw new Exception( 'Invalid name' );
        }

        if
        (
            false === (
                isset( $details->start_unixtime )

                || ( int ) $details->start_unixtime > 0

                || $details->start_unixtime < $details->end_unixtime

                || $details->start_unixtime < time( )
            )
        )
        {
            throw new Exception( 'Invalid start time.  Must be an integer, less than now and less than end_unixtime' );
        }

        if
        (
            false === (
                ( int ) $details->end_unixtime > 0

                || $details->end_unixtime > $details->start_unixtime

                || $details->end_unixtime < time( )
            )
        )
        {
            throw new Exception( 'Invalid end time.  Must be an integer, greater than now and greater than start_unixtime' );
        }

        if
        (
            false === (
                empty( $details->budget[ 'min' ] )

                || ( int ) $details->budget[ 'min' ] < ( int ) $details->budget[ 'max' ]

            )
        )
        {
//            throw new Exception( 'Minimum budget must be less than max budget or empty. Min: ' . $details->budget['min'] . ' Max: ' . $details->budget['max'] );
        }

        if
        (
            false === (
                empty( $details->budget[ 'max' ] )

                || ( int ) $details->budget[ 'max' ] > ( int ) $details->budget[ 'min' ]
            )
        )
        {
//            throw new Exception( 'Maximum budget must be greater than min budget or empty. Min: ' . $details->budget['min'] . ' Max: ' . $details->budget['max'] );
        }

        if
        (
            false === (
                is_array( $details->jobs )
            )
        )
        {
            throw new Exception( 'Jobs must be an array' );
        }

        if
        (
            false === (
                is_array( $details->buyer )

                || ( int ) $details->buyer[ 'id' ] > 0

                || isset( $details->buyer[ 'username' ] )

                || isset( $details->buyer[ 'url' ] )
            )
        )
        {
            throw new Exception( 'Invalid buyer provided' );
        }


        $this->id( $details->id );

        $this->url( $details->url );

        $this->title( $details->name );

        $this->description( $details->short_descr );

        $this->dateStart( $details->start_unixtime );

        $this->dateEnd( $details->end_unixtime );

        $this->budgetLow( $details->budget[ 'min' ] );

        $this->budgetHigh( $details->budget[ 'max' ] );

        $this->jobs( $details->jobs );

        $this->buyer( ( object ) $details->buyer );
    }

}

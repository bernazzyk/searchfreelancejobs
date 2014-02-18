<?php
class OdeskDetailsValidator extends ServiceValidatorAbstract
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
                isset( $details->op_recno )

                || ( int ) $details->op_recno > 0
            )
        )
        {
            throw new Exception( 'Invalid id' );
        }


        if
        (
            false === (
                isset( $details->ciphertext )

                || strlen( $details->ciphertext ) == 0

                || strlen( $details->ciphertext ) < 64
            )
        )
        {
            throw new Exception( 'Invalid ciphertext' );
        }

        if
        (
            false === (
                isset( $details->op_title )

                || strlen( $details->op_title ) == 0

                || strlen( $details->op_title ) < 255
            )
        )
        {
            throw new Exception( 'Invalid name' );
        }

        if
        (
            false === (
                isset( $details->op_description )

                || strlen( $details->op_description ) == 0

                || strlen( $details->op_description ) < 5000
            )
        )
        {
            throw new Exception( 'Invalid Description' );
        }

        if
        (
            false === (
                isset( $details->op_time_posted )

                || isset( $details->op_date_created )

            )
        )
        {
            throw new Exception( 'Invalid start time.' );
        }

        if
        (
            false === (
                isset( $details->op_job_expiration )
            )
        )
        {
            throw new Exception( 'Invalid end time.' );
        }

        if
        (
            false === (
                empty( $details->amount )
            )
        )
        {
//            throw new Exception( 'Minimum budget must be less than max budget or empty. Min: ' . $details->budget['min'] . ' Max: ' . $details->budget['max'] );
        }

        if
        (
            false === (
                isset( $details->job_category_level_one )

                || isset( $details->job_category_level_two )
            )
        )
        {
            throw new Exception( 'Category must be set' );
        }

        if
        (
            false === (
                is_object( $details->buyer )

                || isset( $details->buyer->timezone )

                || isset( $details->buyer->op_state )

                || isset( $details->buyer->op_country )

                || isset( $details->buyer->op_city )
            )
        )
        {
            throw new Exception( 'Invalid buyer provided' );
        }


        $this->id( $details->op_recno );

        $this->url( 'http://www.odesk.com/jobs/' . $details->ciphertext );

        $this->title( $details->op_title );

        $this->description( $details->op_description );

        $this->dateStart( strtotime( $details->op_date_created . ' ' . $details->op_time_posted ) );

        $this->dateEnd( strtotime( $details->op_job_expiration ) );

        $this->budgetLow( $details->amount );

        $this->budgetHigh( $details->amount );

        $this->jobs( array( $details->job_category_level_one . ' > ' . $details->job_category_level_two ) );

        $this->buyer( null );
    }

}

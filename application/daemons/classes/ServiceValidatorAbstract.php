<?php

abstract class ServiceValidatorAbstract
{
    private $_fields = array( );

    /**
     *
     * Setter / Getter.  This method simply assigns the first variable to the passed function named. I.e. it doesn't take into account multiple variables passed to the function
     *
     *
     * @param string $method Name of dynamically called method
     *
     * @param mixed $arguments List of parameters passed to dynamically called method
     *
     * @return string Value of dynamically set method arguments or stored arguments retrieved from getter
     *
     */

    public function __call( $method, $arguments )
    {
        if ( method_exists( $this, $method ) )
        {
            call_user_func_array( array( $this, $method ), $arguments );
        }

        if ( count( $arguments ) > 0)
        {
            /*
             *
            * Setter
            *
            */

            $this->_fields[$method] = $arguments[0];

            return $arguments[0];
        }
        else
        {
            /*
             *
            * Getter
            *
            */

            if ( !empty( $this->_fields[$method] ) )
            {
                return $this->_fields[$method];
            }
            else
            {
                return null;
            }
        }
    }


    public function formatDate( $timestamp )
    {
        return date( 'Y-m-d H:i:s', $timestamp );
    }

    public function __destruct( )
    {
        $this->_fields = array( );
    }


}
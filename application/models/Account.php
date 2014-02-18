<?php

class Application_Model_Account extends Application_Model_Freelancer
{

    public function save( array $data )
    {
        $errors = array ();

        // First Name
        if ( ! Zend_Validate::is( $data['name'], 'NotEmpty' ) )
        {
            $errors[ 'first-name' ] = "Please provide your name.";
        }

        // Does Email already exist?
        if ( Zend_Validate::is( $data['email'], 'EmailAddress' ) )
        {
            if ( true === $this->emailExists( $data['email'] ) )
            {
                $errors[ 'email' ] = "An account using this e-mail address already exists.";
            }
        }
        else
        {
            $errors[ 'email' ] = "Please provide a valid e-mail address.";
        }

        // Password must be at least 6 characters
        $validPassword = new Zend_Validate_StringLength( 6, 20 );

        if ( ! $validPassword->isValid( $data['password'] ) || $this->banedPassword( $data['password'] ) )
        {
            $errors[ 'password' ] = "Your password must be 6-20 characters and not lame.";
        }

        // If no errors, insert the record
        if ( count( $errors ) == 0 )
        {
            $data = array
            (
                'name' 			=> $data['name'],

                'email' 		=> $data['email'],

                'password' 		=> $this->encryptPassword( $data['password'] ),

                'recovery_key' 	=> ''
            );

            return $this->db->insert( 'accounts', $data );
        }

        return $errors;
    }


    private function encryptPassword( $password )
    {
        return md5( $password );
    }


    private function emailExists( $email )
    {
        $select = $this->db
            ->select( )

            ->from( 'accounts' )

            ->where( 'email = ?', $email );


        if ( $this->db->fetchRow( $select ) )
        {
            return true;
        }

        return false;
    }


    private function banedPassword( $password )
    {

        $badPasswords = array
        (
            'Password',

            'password',

            'letmein'
        );

        return in_array( $password, $badPasswords );
    }
}

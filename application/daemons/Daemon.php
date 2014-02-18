<?php
require_once ( 'System/Daemon.php' );

class Daemon
{
    public $script;

    public $name;

    public $delay;

    public $listeners;


    public function __construct( $script = false, $delay = 1 )
    {
        if ( $script === false )
        {
            $script = $_SERVER[ "SCRIPT_NAME" ];
        }

        $this->script     = $script;

        $this->name       = strtolower( basename( $this->script, ".php" ) );

        $this->delay      = $delay;

        $this->listeners  = array ( );

        $this->init( );
    }


    public function start( )
    {
        System_Daemon::start( ); // Spawn Deamon!

        $count = 1;

        while ( !System_Daemon::isDying( ) && $count < 5 )
        {
            $this->logInfo( "Robo Daemon still running" );

            foreach ( $this->listeners as $callback )
            {
                $this->log( print_r( $callback,1 ) );

                call_user_func_array( $callback, array ( ) );
            }

            System_Daemon::iterate( $this->delay );

            $count++;
        }
    }


    public function logInfo( $message )
    {
        System_Daemon::log( System_Daemon::LOG_INFO, $message );
    }


    public function log( $message )
    {
        $this->logInfo( $message );
    }


    public function run( $callback )
    {
        $this->listeners[ ] = $callback;
    }


    public function init( )
    {
        System_Daemon::setOption( "appName", $this->name );

        System_Daemon::setOption( "appDescription", $this->name );

        System_Daemon::setOption( "appDir", getcwd( ) );

        System_Daemon::setOption( "authorName", "Elijah Ethun" );

        System_Daemon::setOption( "appExecutable", 'dispatcher.php' );

        System_Daemon::setOption( "authorEmail", "elijahe@gmail.com" );

//        System_Daemon::writeAutoRun( );
    }


    public function pid( )
    {
        return System_Daemon::getOption( "appPidLocation" );
    }


    public function stop( )
    {
        System_Daemon::stop( );
    }
}


class ScriptDaemon extends Daemon
{
    public function __construct( $script )
    {
        parent::__construct( $script );

        $this->run( array ( $this, "loop" ) );
    }

    public function loop( )
    {
        $output = shell_exec( "sudo php \"$this->script\"" );

        $this->log( $output );
    }
}
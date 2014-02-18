#!/Applications/MAMP/bin/php/php5.3.6/bin/php -q
<?php
require_once ( 'System/Daemon.php' );
require_once ( 'Daemon.php' );


if ( count( $argv ) != 3 )
{
    exit( "\nUsage: daemon [script] [start|stop|restart]\n\n" );
}


$script = $argv[ 1 ];

$method = $argv[ 2 ];


if ( !file_exists( $script ) )
{
    exit( "Could not find script: $script" );
}


$daemon = new ScriptDaemon( $script );


switch ( $method )
{
    case "start" :
        $daemon->start( );

        break;

    case "stop" :
        kill_daemon( $daemon );

        break;

    case "restart" :
        kill_daemon( $daemon );

        sleep( 0.5 );

        $daemon->start( );

        break;

    default :
        exit( "Could not understand method: $method" );
}

function kill_daemon( $daemon )
{
    $pid = file_get_contents( $daemon->pid( ) );

    posix_kill( $pid, SIGKILL );
}

echo "[ OK ]\n";

exit( );
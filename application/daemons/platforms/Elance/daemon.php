<?php
ini_set('memory_limit', '512M');

require_once( '../../common.php' );

require_once( 'classes/ElanceApi.php' );

require_once( 'classes/ElanceDetailsValidator.php' );

require_once( 'classes/ElanceDaemon.php' );

$daemon = new ElanceDaemon( );

$daemon->go( );


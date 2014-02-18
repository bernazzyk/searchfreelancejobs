<?php
require_once( '../../common.php' );

require_once( 'classes/OdeskApi.php' );

require_once( 'classes/OdeskDetailsValidator.php' );

require_once( 'classes/OdeskDaemon.php' );

$daemon = new OdeskDaemon( );

$daemon->go( );
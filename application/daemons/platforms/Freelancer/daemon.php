<?php
require_once( '../../common.php' );

require_once( 'classes/FreelancerApi.php' );

require_once( 'classes/FreelancerDetailsValidator.php' );

require_once( 'classes/FreelancerDaemon.php' );

$daemon = new FreelancerDaemon( );

$daemon->go( );
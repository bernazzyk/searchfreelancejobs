<?php
error_reporting(E_ALL);
@ini_set('log_errors','On');
@ini_set('display_errors','On');

// Define path to application directory
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../../application'));

// Define application environment
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    realpath( dirname( __FILE__ ) . '/../../classes' ),
    realpath( dirname( __FILE__ ) . '/classes' ),
    get_include_path(),
)));

require_once( 'Zend/Config/Ini.php');
require_once( 'Zend/Db.php' );
//require_once( 'ServiceValidatorAbstract.php' );
//require_once( 'ServiceDaemonAbstract.php' );

try
{
    /**
     * Loading configuration from ini file
     */
    $databaseConfiguration = new Zend_Config_Ini(
        APPLICATION_PATH . '/configs/database.ini',
        'development'
    );

    /**
     * Creating the database handler from the loaded ini file
     */

    $dbAdapter = Zend_Db::factory( $databaseConfiguration->database );

}
catch ( Exception $e )
{
    echo $e->getMessage( ) . "\n";
    exit;
}


function db( )
{
    global $dbAdapter;

    return $dbAdapter;
}

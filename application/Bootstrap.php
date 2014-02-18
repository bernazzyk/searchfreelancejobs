<?php
define('SITE_URL','http://www.searchfreelancejobs.com/');
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    
    protected function _initSession()
    {
        Zend_Session::start();
    }
    
    protected function _initDatabase()
    {
        /**
         * Loading configuration from ini file
         */
        $databaseConfiguration = new Zend_Config_Ini( APPLICATION_PATH . '/configs/database.ini', APPLICATION_ENV);
        
        /**
         * Creating the database handler from the loaded ini file
         */
        $dbAdapter = Zend_Db::factory( $databaseConfiguration->database );
        
        /**
         * Lets define the newly created handler as our default database handler
         */
        Zend_Db_Table_Abstract::setDefaultAdapter($dbAdapter);
        
        $registry = Zend_Registry::getInstance();
        $registry->databaseConfiguration = $databaseConfiguration;
        $registry->db = $dbAdapter;
        
        $dbAdapter->query('SET NAMES utf8');
        
        /*$profiler = new Zend_Db_Profiler_Firebug('All DB Queries');
        $profiler->setEnabled(true);
        $dbAdapter->setProfiler($profiler);*/
    }
    
    protected function _initAuth()
    {
        $auth = Zend_Auth::getInstance();
        $authStorage = $auth->getStorage();
        $userId = (int)$authStorage->read();
        if ($userId) {
            $accountModel = new Application_Model_DbTable_Accounts();
            
            $account = $accountModel->find($userId)->current();
            Zend_Registry::set('account', $account);
            
            /*if (!$account->agreed
                && !$account->isTrial()
                && !in_array($_SERVER['REQUEST_URI'], array('/registration/step2', '/auth/logout/', '/termsconditions', '/privacy'))
            ) {
                header('location: /registration/step2');
                die();
            }*/
        }
    }
    
    protected function _initViewHelpers()
    {
                $this->bootstrap( 'view' );
                
                $view = $this->getResource( 'view' );
                $view->doctype( 'XHTML1_STRICT' );
                $view->headMeta()
                    ->appendHttpEquiv( 'Content-Type', 'text/html;charset=utf-8' )
                    ->appendHttpEquiv( 'content-language', 'EN-US' )
                    ->appendName( 'description', 'SearchFreelanceJobs' )
                    ->appendName( 'keywords', 'SearchFreelanceJobs Keywords' )
                    ->appendName( 'format-detection', 'telephone=no' )
                    ->appendName( 'robots', 'index,nofollow' )
                    ->appendName( 'revisit-after', '31 days' )
                    ->appendName( 'mssmarttagspreventparsing', 'true' );
                $view->headTitle()->setSeparator( ' - ' );
                $view->headTitle( 'SearchFreelanceJobs.com' );
                
                $baseUrl = '/'; //substr($_SERVER['PHP_SELF'], 0, -9);
                
                $view->addHelperPath( "ZendX/JQuery/View/Helper", "ZendX_JQuery_View_Helper" );
                $view->jQuery()->addStylesheet( $baseUrl . 'media/js/global/jquery/css/ui-lightness/jquery-ui-1.8.21.custom.css' );
                $view->jQuery()->setLocalPath( $baseUrl . 'media/js/global/jquery/js/jquery-1.7.2.min.js' );
                $view->jQuery()->setUiLocalPath( $baseUrl . 'media/js/global/jquery-ui-1.9.1/js/jquery-ui-1.9.1.custom.min.js' );
                $view->jQuery()->enable();
                $view->jQuery()->uiEnable();
                
                Zend_Paginator::setDefaultScrollingStyle('Sliding');
                Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
            }
            
    protected function _initUser() {
        $_privateKey = array(
            'name' => 'vidretal_privateKey',
            'value' => 'mamatata!@#'
        );
        $_hardcodedUser = array(
            'name' => 'admin@yestoapps.com',
            'password' => 'IonEric123'
        );
        $_is_authenticated = false;
        $_auth_errors = array();

      /*  if (isset($_SESSION[$_privateKey['name']]) && $_SESSION[$_privateKey['name']]==$_privateKey['value']) {
            $_is_authenticated = true;
            } else {
            if (isset($_POST['submit'])) {
                if ($_POST['username']==$_hardcodedUser['name'] && $_POST['password']==$_hardcodedUser['password']) {
                    $_is_authenticated = true;
                    $_SESSION[$_privateKey['name']] = $_privateKey['value'];
                } else {
                    $_auth_errors[] = "Invalid username or password";
                }
        }

        }*/
        if(!strpos($_SERVER['REQUEST_URI'],'extractprojects')){
        if ($_is_authenticated || $_SERVER['REQUEST_URI'] == '/backend/videos/upload/' || $_SERVER['REQUEST_URI'] == '/cron/weekly/' || $_SERVER['REQUEST_URI'] == '/cron/daily/')
        {
            
        } else {
           // header("Location: /login.php");
            //exit;
        }
        }
    }
    
}

define( 'LOG_FILE_PATH', getcwd( ) . '/logs/' );

function dev_log( )
{
    require_once 'Zend/Log/Writer/Stream.php';	// File log writer

    $arguments = func_get_args();

    $classes_to_convert_to_string = array( 'Zend_Db_Select', 'freelancer_select' );

    foreach ( $arguments as $argument )
    {
        if ( is_object( $argument ) && in_array( get_class( $argument ), $classes_to_convert_to_string ) )
        {
            $argument = $argument->__toString();
        }

        write_log( $argument, Zend_Log::DEBUG, 'development_log' );
    }
}

function write_log( $message, $level = Zend_Log::ERR, $from_development_log = '' )
{
    if ( is_array( $message ) || is_object( $message ) || empty( $message ) )
    {
        $message = var_expert( $message );
    }

    if( stristr( $message, 'SQLSTATE' ) )
    {
        $message = "\n" . $message;
    }

    if( substr( $message, 0, 2 ) == '#0' )
    {
        $message = "\n" . $message;
    }

    if ( $from_development_log == 'exception_log' )
    {
        $trace_level = 0;
    }
    elseif ( $from_development_log == 'development_log' )
    {
        $trace_level = 3;
    }
    else
    {
        $trace_level = 2;
    }

    if ( $trace_level )
    {
        $back_trace = backtrace_for_log( $trace_level );

        $message = 'PRE CONFIG [' . $back_trace[count($back_trace)-$trace_level] . ']:' . $message;
    }

    if ( !empty( $message ) )
    {
        $filename = get_writable_log_file( 'application_log_' . date( 'Y-m-d' ) . '.txt' );

        if( $filename )
        {
            try
            {
                require_once 'Zend/Log.php';				// Zend_Log base class
                require_once 'Zend/Log/Writer/Stream.php';	// File log writer

                $writer = new Zend_Log_Writer_Stream( $filename );
                $logger = new Zend_Log( $writer );
                $logger->log( $message, $level );
            }
            catch( Exception $e )
            {
                file_put_contents( $filename, date( 'Y-m-d H:i:s' ) . ' -- Error when trying to log' . PHP_EOL . PHP_EOL, FILE_APPEND );
                file_put_contents( $filename, 'LOG MESSAGE: ' . PHP_EOL . $message . PHP_EOL . PHP_EOL, FILE_APPEND );
                file_put_contents( $filename, 'EXCEPTION: ' . PHP_EOL . $e->getMessage() . PHP_EOL . PHP_EOL . PHP_EOL, FILE_APPEND );
            }
        }
    }
}

function get_writable_log_file( $filename )
{
    $full_path_filename = LOG_FILE_PATH . $filename;

    if ( !file_exists( LOG_FILE_PATH ) || !get_writable_file( $full_path_filename ) )
    {

        $temp_directory = '/tmp/';

        $full_path_filename = $temp_directory . $filename;

        if( !get_writable_file( $full_path_filename ) )
        {
            return false;
        }
    }

    return $full_path_filename;
}

function backtrace_for_log( $trace_level = 2 )
{
    //only do the final few steps, called from write_log to automatically output the write_log location
    $steps = array();

    $history = debug_backtrace();

    $previousFunc = '';

    for ( $item_counter = $trace_level; $item_counter >= 0; $item_counter-- )
    {
        $backtrace = '';

        if( array_key_exists( $item_counter, $history ) )
        {
            $item = $history[$item_counter];

            if( ( !empty( $item['class'] ) && ( stristr( $item['class'], 'Zend' ) || stristr( $item['class'], 'xajax' ) ) ) ||
                    ( !empty( $item['file'] ) && ( stristr( $item['file'], 'Zend' ) || stristr( $item['file'], 'xajax' ) ) ) ||
                    ( !empty( $item['function'] ) && in_array( $item['function'], array( 'processRequest', 'require_once' ) ) ) )
            {
                continue;
            }

            $filename_ar = explode( '/', $item['file'] );
            $filename = empty( $item['file'] ) ? '' : end( $filename_ar );

            if ( !empty( $item['class'] ) )
            {
                $backtrace .= $item['class'] . ':';
            }

            $backtrace .= $previousFunc;

            if ( !empty( $item['line'] ) && !empty( $filename ) )
            {
                $backtrace .= ' on line ' . $item['line'] . ' of ' . $filename;
            }
            else
            {
                $backtrace .= ' in ' . $previousFunc;
            }

            $previousFunc = $item['function'];

            $steps[] = $backtrace;
        }
    }

    return $steps;
}

function get_writable_file( $filename )
{
    if( is_writable( $filename ) )
    {
        return true;
    }
    else
    {
        if( touch( $filename ) && chmod( $filename, 0666 ) )
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}

function var_expert( $mixed_thing )
{
    if ( is_array( $mixed_thing ) || is_object( $mixed_thing ) || empty( $mixed_thing ) )
    {
        $print_r_result = print_r( $mixed_thing, true );

        if ( false === stripos( $print_r_result, '*RECURSION*' ) )
        {
            return var_export( $mixed_thing, true );
        }
        else
        {
            return $print_r_result;
        }
    }
    else
    {
        return $mixed_thing;
    }
	
	
	  /**
     * 
     * This puts the application.ini setting in the registry
     */
   /* protected function _initConfig()
    {
        Zend_Registry::set('config', $this->getOptions());
    }*/

    /**
     * 
     * This function initializes routes so that http://host_name/login
     * and http://host_name/logout is redirected to the user controller.
     * 
     * There is also a dynamic route for clean callback urls for the login 
     * providers
     */
  /*  protected function _initRoutes()
    {
        $front = Zend_Controller_Front::getInstance();
        $router = $front->getRouter();

        $route = new Zend_Controller_Router_Route('login/:provider',
                                                  array(
                                                  'controller' => 'user',
                                                  'action' => 'login'
                                                  ));
        $router->addRoute('login/:provider', $route);

        $route = new Zend_Controller_Router_Route_Static('login',
                                                         array(
                                                         'controller' => 'user',
                                                         'action' => 'login'
                                                         ));
        $router->addRoute('login', $route);

        $route = new Zend_Controller_Router_Route_Static('logout',
                                                         array(
                                                         'controller' => 'user',
                                                         'action' => 'logout'
                                                         ));
        $router->addRoute('logout', $route);
    }*/
	
}

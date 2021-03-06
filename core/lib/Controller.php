<?php
/**
 * Controller
 * 
 * @package local - framework
 * @author Stuart Duncan
 * @copyright 2012
 * @version $Id$
 * @access public
 */
class Controller {

	/** The theme - Default is 'default' **/
	var $_theme = 'default';

	private static $instance;

	/** $this->set(name, value) puts information into $this->_variables for use in the views **/
	private $_variables = array();

	public $config = array();

	/**
	 * Controller::__construct()
	 * Called on creation
	 * 
	 * @return void
	 */
	public function __construct(){
		self::$instance =& $this;

		// Load the load class
		$this->load = new Load();

		// load the default config file, if there is one.
		$this->loadConfig();
	}

	/**
	 * Controller::getInstance()
	 * Returns an instance of this class
	 * 
	 * @return
	 */
	public static function &getInstance(){
		return self::$instance;
	}

	public function loadConfig($file = 'config'){
		// Path to the config file to load
		$path = APP_PATH . 'config' . DS . $file . '.php';

		// If the file exists, continue on.
		if( file_exists($path) ){
			require_once($path);
			if( is_array($config) ){
				foreach( $config as $key => $item ){
					$this->config[$key] = $item;
				}
			}
			else {
				$this->config[] = $config;
			}
		}
		else {
			return false;
		}

		return true;
	}

	public function error($msg = '', $status = 404){
		$this->load->library('error');
		$this->error->show($msg, $status);
	}

	/**
	 * Controller::index()
	 * Default first view ever if there's nothing in 'app' folder
	 * 
	 * @return void
	 */
	public function index(){
		$this->set('output', 'Hello World!');
		$this->setTheme('default');
		$this->view('index');
	}

	/**
	 * Controller::set()
	 * Set data for the templates
	 * 
	 * @param mixed $name
	 * @param mixed $value
	 * @return void
	 */
	public function set($name = null, $value = null){
		$this->_variables[$name] = $value;
	}

	/**
	 * Controller::setTheme()
	 * Already defaults to 'default' which is set in the core
	 * 
	 * @param string $theme
	 * @return void
	 */
	public function setTheme($theme = ''){
		if( !empty($theme) ){
			$this->_theme = $theme;
		}
	}

	/**
	 * Controller::getTheme()
	 * Returns $_theme value
	 * 
	 * @return string
	 */
	public function getTheme(){
		return $this->_theme;
	}	

	/**
	 * Controller::view()
	 * Default view method. This will call call a file and either output or return it's contents
	 * 
	 * @param mixed $page
	 * @param bool $return
	 * @return mixed
	 */
	public function view($page = null, $return = false){
		$path = '';

		// _variables is set using $this->set(name, value) and extracted here
		extract($this->_variables);

		// Now check for the theme, in app and core
		if( is_dir(APP_PATH . 'theme' . DS . $this->_theme) ){
			$path = APP_PATH . 'theme' . DS . $this->_theme;
		}

		// No path? That means the file was not found.
		if( empty($path) ){
			$this->error('Theme "' . $this->_theme . '" is not available');
			return;
		}

		// Full path to view file, including theme folder and theme name
		if( file_exists(APP_PATH . 'theme' . DS . $this->_theme . DS . $page . '.php') ){
			$view = APP_PATH . 'theme' . DS . $this->_theme . DS .  $page . '.php';
		}
		// If not found in app path, check core. Core only has one theme
		elseif( file_exists(CORE_PATH . 'default' . DS . $page . '.php') ){
			$view = CORE_PATH . 'default' . DS . $page . '.php';
		}

		// Still can't find the file?
		if( empty($view) ){
			$this->error('File "' . $this->_theme . DS . $page . '.php" does not exist', 404);
		}
		else {
			// The user wants the info returned
			if( $return ){
				ob_start();
				include($view);
				$buffer = ob_get_contents();
				ob_end_clean();
				return $buffer;
			}
			// Display the template
			else {
				require_once($view);
			}
		}
	}
}
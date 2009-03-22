<?php
/**
* M PHP Framework
*
* @package      M
* @subpackage   Dispatcher
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

/**
 * 
* Dispatcher Class
* The main and only goal of the dispatcher is to route the request to the correct Module
* The constructor takes two parameters, 1 and 2 correspond to the module and action names. 
* The third one is optional parameters to pass to module. Mostly used when a module is used as a component
 *
 */
class Dispatcher extends Maman {

	/**
	 * 
	 * Page
	 *
	 * @var		string
	 * @access	protected
	 */
	protected $page;
    
	/**
	 * 
	 * Module
	 *
	 * @var		string
	 * @access	protected
	 */
	protected $module;
    
    /**
     * 
     * Action
     *
     * @var		string
     * @access	protected
     */
	protected $action;
    
	/**
	 * 
	 * Optional params
	 *
	 * @var		string
	 * @access	protected
	 */
	protected $params;
    
    /**
     * 
     * Constructor
     *
     * @param $module	Module to dispatch
     * @param $action	Action to dispatch
     * @param $params	Optional parameters
     */
    function __construct($module = 'defaut', $action = 'index',$params = null) {
        $this->module = $module?$module:'defaut';
        $this->action = $action?$action:'index';
        $this->params = $params;
        $this->setConfig(PEAR::getStaticProperty('Dispatcher','global'));
    }
    
	/**
	 *
	 * Execute the dispatch
	 *
	 * @access	public
	 *
	 */
    public function execute ()
    {
      
      $path=$this->getPath();

        try
        {
          Log::info('Trying module '.$this->module);
        	$this->page = Module::factory($this->module,$path,$this->params);
        	try
        	{
              Log::info('Trying action '.$this->action);
        		  $this->page->executeAction($this->action);
              
        	}
        	catch (Exception $e)
        	{
              Log::info('Action '.$this->action.' rejected for module '.$this->module.', trying index instead ');
              Log::info('Reason : '.$e->getMessage());
        		  $this->action = 'index';
        		  $this->page->executeAction($this->action);
        	}

        }
        catch (Error404Exception $e) {
          $this->returnModuleNotFound();
        }
        catch (SecurityException $e) {        

            Log::info('User not allowed for '.$this->module);
            Log::info('Setting target after login to '.$this->module.'/'.$this->action);
            $data = $_REQUEST;
            unset($data['module']);
            unset($data['action']);
            User::getInstance()->setTarget($this->module.'/'.$this->action,$data);
            $this->page = Module::factory($this->getConfig('loginmodule'),$path);
            $this->page->executeAction($this->getConfig('loginaction'));
        }
        catch (Exception $e)
        {
          
            Log::info('Error module '.$this->module.'. Revealing as 404');
        	  $this->page = Module::factory('error',$path);
            $this->page->executeAction('404');
        }        
        Log::info($this->module.'/'.$this->action.' executed successfully');
    }

    /**
	 *
	 * Handle "Not found error"
	 *
	 * @access	private
	 * @return	string	Value
	 * @static
	 *
	 */
    private function returnModuleNotFound() {
      Log::info('Module '.$this->module.' not found');
      $this->page = Module::factory('error',$path);
      $this->page->executeAction('404');
    }
    
    /**
	 *
	 * Get the module page
	 *
	 * @access	public
	 * @return	string	Module page
	 * @static
	 *
	 */
    public function &getPage ()
    {
        return $this->page;
    }
    
    
    /**
	 *
	 * Get the module path
	 *
	 * @access	public
	 * @return	string	Module path
	 * @static
	 *
	 */
    public function getPath()
    {
      return $this->getConfig('modulepath','all',null);
    }

    /**
	 *
	 * Display the module
	 *
	 * @access	public
	 * @return	string	Module output
	 * @static
	 *
	 */
    public function display ()
    {
        return $this->page->output();
    }
}
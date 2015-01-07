<?php
/**
* M PHP Framework
*
* @package      M
* @subpackage   Dispatcher
* @author       Arnaud Sellenet <demental@sat2way.com>

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
	 * @var		Module
	 * @access	protected
	 */
	protected $page;

	/**
	 *
	 * Module name
	 *
	 * @var		string
	 * @access	protected
	 */
	protected $module;

    /**
     *
     * Action name
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
   * @param $module	name of the requested module
   * @param $action	name of the requested action
   * @param $params	Optional parameters
   */
  function __construct($module = 'defaut', $action = 'index',$params = null)
  {
    $this->module = $module?$module:'defaut';
    $this->action = $action?$action:'index';
    $this->params = $params;
    $this->setConfig(PEAR::getStaticProperty('Dispatcher','global'));
  }

	/**
	 *
	 * Execute the dispatching
	 * Tries to include the requested module. If not found executes the 404 page.
	 * Tries to execute the requested action. If not found executes the module index.
	 * If the user does not have privileges for accessing the requested module/action, redirects to login page.
	 *
	 * @access	public
	 * @return null
	 */
    public function execute()
    {
      $path=$this->getPath();

      try
      {
        Log::info('Trying module '.$this->module);
      	$this->page = $this->moduleInstance($this->module,$path,$this->params);

      	try
      	{
            Log::info('Trying action '.$this->action);
      		  $this->page->executeAction($this->action);
      	}
        catch (SecurityException $e) {
          $this->handleSecurity();
        }
        catch (Error404Exception $e) {
            $this->returnActionNotFound();
          }
        catch (Exception $e)
        {
            Log::info('Action '.$this->action.' rejected for module '.$this->module.', trying index instead ');
            Log::info('Reason : '.$e->getMessage());
            $this->page->handleException($e);
        }

      }
      catch (Error404Exception $e) {

        $this->returnModuleNotFound();
      }
      catch (SecurityException $e) {
        $this->handleSecurity();
      }
      catch (Exception $e)
      {

          Log::info('Error module '.$this->module.'. Revealing as 404');
      	  $this->page = $this->moduleInstance('error',$path);
          $this->page->executeAction('404');
      }
      Log::info($this->module.'/'.$this->action.' executed successfully');
    }

    protected function handleSecurity()
    {
      Log::info('User not allowed for '.$this->module);
      Log::info('Setting target after login to '.$this->module.'/'.$this->action);
      $data = $_REQUEST;
      unset($data['module']);
      unset($data['action']);
      try {
        User::getInstance()->setTarget($this->module.'/'.$this->action,$data);
      } catch(Exception $e) {

      }
      $this->page = $this->moduleInstance($this->getConfig('loginmodule',$this->module),$path);
      $this->page->executeAction($this->getConfig('loginaction',$this->module));

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
      $this->page = $this->moduleInstance('error',$path);
      $this->page->executeAction('404');
      header('Status: 404');
      header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
    }
    private function returnActionNotFound() {
      Log::info('Action '.$this->action.' not found');
      if(method_exists($this->page,'handleNotFound')) {
        Log::info('Using custom handleNotFound() method');

        $this->page->handleNotFound($this->action);
      } else {
        $this->page = $this->moduleInstance('error',$path);
        $this->page->executeAction('404');
        header('Status: 404');
        header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
      }
    }
    public function moduleInstance()
    {

      $args = func_get_args();

      $res = call_user_func_array(array('Module','factory'),$args);

      return $res;
    }

  /**
	 *
	 * Get the module page
	 *
	 * @access	public
	 * @return	Module current executed Module
	 * @static
	 *
	 */
    public function getPage()
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
	 * returns the dispatching result
	 *
	 * @access	public
	 * @return	string rendered output
	 * @static
	 *
	 */
    public function display()
    {
        return $this->page->output();
    }
}

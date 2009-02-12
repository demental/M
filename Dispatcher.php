<?php
// ====================
// = Dispatcher Class
// = The main and only goal of the dispatcher is to route the request to the correct Module
// = The constructor takes two parameters, 1 and 2 correspond to the module and action names. 
// = The third one is optional parameters to pass to module. Mostly used when a module is used as a component
// ====================
class Dispatcher extends Maman {
    protected $page;
    protected $module;
    protected $action;
    protected $params;    
    function __construct($module = 'defaut', $action = 'index',$params = null) {
        $this->module = $module?$module:'defaut';
        $this->action = $action?$action:'index';
        $this->params = $params;
        $this->setConfig(PEAR::getStaticProperty('Dispatcher','global'));
    }
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
    private function returnModuleNotFound() {
      Log::info('Module '.$this->module.' not found');
      $this->page = Module::factory('error',$path);
      $this->page->executeAction('404');
    }
    public function &getPage ()
    {
        return $this->page;
    }
    public function getPath()
    {
      return $this->getConfig('modulepath','all',null);
    }

    public function display ()
    {
        return $this->page->output();
    }
}
?>
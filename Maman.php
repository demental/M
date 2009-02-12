<?php
// ================================================================================
// = Abstract class from which several other classes (like Module or Mail) extend =
// = Provides configuration methods
// ================================================================================
abstract class Maman
{
    protected $config;
    
    function setConfig($conf)
    {
  	$this->config = $conf;
    }
    function setConfigValue($key,$value, $action = 'all') 
    {
    	$this->config[$action][$key] = $value;
    }
    function getAllConfig() {
        return $this->config;
    }
    function getConfig($value,$action = 'all',$default = false)
    {
      switch(true) {
        case isset($this->config[$action][$value]):
  	      return $this->config[$action][$value];
        case isset($this->config['all'][$value]):
          return $this->config['all'][$value];
        default:
          return false;
      }
    }
}
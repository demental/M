<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   Maman
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 *
 * Abstract class from which several other classes (like Module or Mail) extend
 * Provides internal configuration methods
 *
 */
abstract class Maman
{
	/**
	 * 
	 * Config
	 *
	 * @var		array
	 * @access	protected
	 */
	protected $config;

	/**
	 * 
	 * Set config
	 *
	 * @param $conf	array	Config data
	 */
	function setConfig($conf)
	{
		$this->config = $conf;
	}
	
	/**
	 * 
	 * Set config value
	 *
	 * @param $key		string	Config key
	 * @param $value	string	Config value
	 * @param $action	string	Action
	 */
	function setConfigValue($key,$value, $action = 'all')
	{
		$this->config[$action][$key] = $value;
	}
	
	/**
	 * 
	 * Get entire config
	 *
	 * @return Config data	array
	 */
	function getAllConfig() {
		return $this->config;
	}
	
	/**
	 * 
	 * Get specific config value
	 *
	 * @param $value	string	Value to get
	 * @param $action	string	Action
	 * @param $default	string	Default value
	 * @return Value	string
	 */
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
	public function stackTrace() {
    $stack = debug_backtrace();
    $output = '';

    $stackLen = count($stack);
    for ($i = 1; $i < $stackLen; $i++) {
        $entry = $stack[$i];

        $func = $entry['function'] . '(';
        $argsLen = count($entry['args']);
        for ($j = 0; $j < $argsLen; $j++) {
            $func .= $entry['args'][$j];
            if ($j < $argsLen - 1) $func .= ', ';
        }
        $func .= ')';

        $output .= $entry['file'] . ':' . $entry['line'] . ' - ' . $func . PHP_EOL;
    }
    return $output;
	}
}
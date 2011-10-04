<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   app.php
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 * Command to launch unit tests
 */

class Command_Phpunit_Test extends Command {
  public function shortHelp()
  {
    $this->line('Launches unit tests with phpunit');
  }
  public function longHelp($params)
  {
    $this->line('Launches unit tests');
    $this->line('Usage : phpunit_test [testgroup]');
    $this->line('Testgroup is one of the subfolders in the test folder. You may sort your tests by groups there');
    $this->line('use "all" for firing all unit tests');
    $this->line('DRAWBACK : you cannot have neither a fixtures nor a all nor an extensions group');
  }
  public function execute($params)
  {
    if(!defined('TESTS_FOLDER')) define('TESTS_FOLDER',APP_ROOT.PROJECT_NAME.'/tests/');
    
    
    $folder = $params[0];
    if($folder == 'all') {
      $folders = $this->getAllTestFolders();
    } else {
      $folders = array($folder);
    }
    foreach($folders as $folder) {
      $this->launchtests($folder);
    }
  }
  public function launchtests($folder)
  {
    $options = &PEAR::getStaticProperty('DB_DataObject', 'options');
    $options['database'] = DB_URI_TEST;
    
    $testfolder = TESTS_FOLDER.'/'.$folder;
		if(is_dir($testfolder)) {
    	$testfolder.='/';
		} elseif(!file_exists($testfolder.'.php')) {
    	$testfolder.='.php';			
		} elseif(!file_exists($testfolder)) {
      return $this->error(TESTS_FOLDER.'/'.$folder.' does not exist');			
		}
	
		$binfile = realpath(dirname(__FILE__).'/../bin/phpunit');
    $files =array($binfile, $testfolder);
    $com = 'sh '.implode(' ',$files);
    Command::launch($com);
    $this->line('tests finished');

    
  }
  public function getAllTestFolders()
  {
    $testfolder = TESTS_FOLDER;
    $op = opendir($testfolder);
    while (($file = readdir($op)) !== false) {
      if(is_dir($testfolder . $file) && !in_array($file,$forbidden)){
        $folders[] = $file;
      }
    }
    return $folders;
  }
}
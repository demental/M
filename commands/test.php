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

class Command_Test extends Command {
  public function shortHelp()
  {
    $this->line('Launches unit tests');
  }
  public function longHelp($params)
  {
    $this->line('Launches unit tests');
    $this->line('Usage : test [testgroup]');
    $this->line('Testgroup is one of the subfolders in the test folder. You may sort your tests by groups there');
    $this->line('use "all" for firing all unit tests');
    $this->line('DRAWBACK : you cannot have neither a fixtures nor a all nor an extensions group');
  }
  public function execute($params)
  {
    if(!defined('TESTS_FOLDER')) define('TESTS_FOLDER',APP_ROOT.PROJECT_NAME.'/tests/');
    if(!defined('SIMPLE_TEST')) define('SIMPLE_TEST', 'simpletest/');
    
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
    
    require_once(SIMPLE_TEST . 'unit_tester.php');
    require_once(SIMPLE_TEST . 'reporter.php');
    $test = &new GroupTest('Tests of '.$folder);
    $testfolder = TESTS_FOLDER.'/'.$folder.'/';
    if(!$op = opendir($testfolder)) {
      return $this->error(TESTS_FOLDER.'/'.$folder.' folder does not exist');
    }
    while (($file = readdir($op)) !== false) {

      if(filetype($testfolder . $file)=='file' && eregi('\.php$',$file)){
        $test->addTestFile($testfolder . $file);
      }
    }
    $test->run(new TextReporter());
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
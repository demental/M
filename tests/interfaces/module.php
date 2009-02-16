<?php
/**
* Module_Unisttests
*/

class Module_Unittests extends Module
{
  public function getCacheId()
  {
    return false;
  }
  public function doExecIndex()
  {
  }
  /**
   * Launches tests with no db alteration
   */
  public function doExecLaunchnodb() {
    $this->launchtests('nodb','All tests without database alteration');
    $this->hasLayout(false);
    $this->setTemplate('unittests/empty');

  }
  /**
   * Launches tests with no db alteration
   */  
  public function doExecLaunchdb()
  {
    $options = &PEAR::getStaticProperty('DB_DataObject', 'options');
    $options['database'] = DB_URI_TEST;

    if($this->hasRequest('confirm')) {
      $this->launchtests('db','All tests WITH database alteration');
      $this->hasLayout(false);
      $this->setTemplate('unittests/empty');
    }
    $u = DB_DataObject::factory('user');
    $u->find();// To init database
    $db = $u->getDatabaseConnection();
    $dbn = $db->database_name;
    $this->assign('db',$dbn);
  }
  public function doExecLaunchframework()
  {
    $options = &PEAR::getStaticProperty('DB_DataObject', 'options');
    $options['database'] = DB_URI_TEST;

    if($this->hasRequest('confirm')) {

      $this->launchtests('framework','Framework Core tests');
      $this->hasLayout(false);
      $this->setTemplate('unittests/empty');
    }
  }
  public function doExecLaunchall()
  {

    $options = &PEAR::getStaticProperty('DB_DataObject', 'options');
    $options['database'] = DB_URI_TEST;
    if($this->hasRequest('confirm')) {
      $this->launchtests('dbstruct','Database structure tests');
      $this->launchtests('nodb','All tests without database alteration');
      $this->launchtests('db','All tests WITH database alteration');
      $this->hasLayout(false);
      $this->setTemplate('unittests/empty');
    }
    $u = DB_DataObject::factory('user');
    $u->find();// To init database
    $db = $u->getDatabaseConnection();
    $dbn = $db->database_name;
    $this->assign('db',$dbn);
  }
  public function doExecLaunchstructure()
  {
      $this->launchtests('dbstruct','Database structure tests');
      $this->hasLayout(false);
      $this->setTemplate('unittests/empty');
  }
  public function launchtests($folder,$title)
  {
    // TODO write a reporter that can be assigned to the template
    ini_set('display_errors',1);

    if (! defined('SIMPLE_TEST')) {
           define('SIMPLE_TEST', 'simpletest/');
    }
    require_once(SIMPLE_TEST . 'unit_tester.php');
    require_once(SIMPLE_TEST . 'reporter.php');
    $test = &new GroupTest($title);
    $testfolder = TESTS_FOLDER.'/'.$folder.'/';
    $op = opendir($testfolder);
    while (($file = readdir($op)) !== false) {

      if(filetype($testfolder . $file)=='file' && eregi('\.php$',$file)){
        $test->addTestFile($testfolder . $file);
      }
    }
    $test->run(new HtmlReporter());
    closedir($op);
  }
  /**
   * Used for framework tests development
   */
  public function doExecInitFrameworkDOs()
  {
    $options = &PEAR::getStaticProperty('DB_DataObject', 'options');
    $options['database'] = DB_URI_TEST;
    $options['class_location'] = PEAR_FOLDER.'M/tests/DO/';
    $options['schema_location'] = PEAR_FOLDER.'M/tests/DO/';    
    ini_set('display_errors',1);

    if (! defined('SIMPLE_TEST')) {
           define('SIMPLE_TEST', 'simpletest/');
    }
    require_once(SIMPLE_TEST . 'unit_tester.php');
    require_once TESTS_FOLDER.'/extensions/DBTestCase.php';

    $t = new DBTestCase();
    $t->setUpDatabase('Mfixture.sql',1);
    require_once('M/DB/DataObject/Advgenerator.php');
	  $generator = new DB_DataObject_Advgenerator();
	  $generator->start();
    $this->redirect(array('module'=>'unittests'),array(),array_keys($_REQUEST));
  }
}
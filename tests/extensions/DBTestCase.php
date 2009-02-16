<?php

class DBTestCase extends UnitTestCase {
  public function setUpDatabase($filename,$fixturehash)
  { 
    $catbin='cat';
    $mysqlbin = 'mysql';

    $u = DB_DataObject::factory('user');
    $u->find();// To init database
    $db = $u->getDatabaseConnection();
    $h = $db->dsn['hostspec'];
    $u = $db->dsn['username'];
    $p = $db->dsn['password'];
    
    $dbn = $db->database_name;
    $file = TESTS_FOLDER.'fixtures/'.$filename;


    $sys = $catbin." $file | ".$mysqlbin." --host=$h --user=$u --password=$p $dbn";
    system($sys,$return);

    // We check that the fixture was correctly dumped...
    $fixturenumber = $db->queryOne('select val from preferences where var="fixture"');
    if($fixturenumber != $fixturehash) {
      die('Could not dump fixture database '.$fixturehash.' (db value = '.$fixturenumber.')');
    }
  }
}

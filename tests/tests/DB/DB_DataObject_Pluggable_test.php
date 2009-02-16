<?php
require_once TESTS_FOLDER.'extensions/DBTestCase.php';
require_once SIMPLE_TEST.'mock_objects.php';

class TestOfDB_DataObject_Pluggable extends DBTestCase {
  public function setup()
  {

    $options = &PEAR :: getStaticProperty('DB_DataObject', 'options');
    $options['class_location'] = PEAR_FOLDER.'M/tests/DO/';
    $options['schema_location'] = PEAR_FOLDER.'M/tests/DO/';    

    $options['class_prefix'] = 'DataObjects_';
    foreach(FileUtils::getAllFiles(PEAR_FOLDER.'M/tests/DO/') as $file){
      unlink($file);
    }
    foreach(FileUtils::getAllFiles(PEAR_FOLDER.'M/tests/DO_dist/') as $file){
      copy($file,str_replace('DO_dist','DO',$file));
    }
    
    $this->setUpDatabase('Mfixture.sql',1);    
  }
  public function testSingleConnection()
  {

    $u = DB_DataObject::factory('testuser');
    $u->find();
    $c = DB_DataObject::factory('notmigrated');
    $c->find();

    $this->assertIdentical($u->getDatabaseConnection(),$c->getDatabaseConnection(),'MDB2 Singleton');
  }
  public function testTransactionWithCustomMethods()
  {

    $c = DB_DataObject::factory('vanillainnodb');
    $this->assertTrue($c->find());
    $c->begin();
    while($c->fetch()) {
     $c->delete(); 
    }
    $c->rollback();
    $c2 = DB_DataObject::factory('vanillainnodb');
    $this->assertTrue($c2->find());

  }
  public function testTransactionRollbackWithCustomMethodsAcrossMultipleTables()
  {
    $c = DB_DataObject::factory('vanillainnodb');// innoDB
    $c->find();
    $cl = DB_DataObject::factory('vanillainnodb2');// innoDB
    $cl->name = 'Michael Jackson';
    $cl->insert();

    $u = DB_DataObject::factory('testuser');// Non innoDB
    $u->login='test';
    $u->password = 'test';
    $this->assertTrue($u->insert());// Just to verify inserting was ok
    $c->begin();
    while($c->fetch()) {
     $c->delete(); 
    }
    $u->delete();
    $cl->delete();
    $c->rollback();
    $c2 = DB_DataObject::factory('vanillainnodb');
    
    $this->assertTrue($c2->find());
    $u2 = DB_DataObject::factory('testuser');
    $u2->login='test';
    $u2->password = 'test';
    $this->assertFalse($u2->find()); // Non innoDB
    $cl = DB_DataObject::factory('vanillainnodb2');
    $cl->name = 'Michael Jackson';
    $this->assertTrue($cl->find());
  }  
  public function testTransactionCommitWithCustomMethodsAcrossMultipleTables()
  {
    $c = DB_DataObject::factory('vanillainnodb');// innoDB
    $c->find();
    $cl = DB_DataObject::factory('vanillainnodb2');// innoDB
    $cl->name = 'Michael Jackson';
    $cl->insert();

    $u = DB_DataObject::factory('testuser');// Non innoDB
    $u->login='test';
    $u->password = 'test';

    $this->assertTrue($u->insert());// Just to verify inserting was ok
    $c->begin();
    while($c->fetch()) {
     $c->delete(); 
    }
    $u->delete();
    $cl->delete();
    $c->commit();
    DB_DataObject::DebugLevel(0);    
    $c2 = DB_DataObject::factory('vanillainnodb');
        
    $this->assertFalse($c2->find());
    $u2 = DB_DataObject::factory('testuser');
    $u2->login='test';
    $u2->password = 'test';
    $this->assertFalse($u2->find()); // Non innoDB
    $cl = DB_DataObject::factory('vanillainnodb2');
    $cl->name = 'Michael Jackson';
    $this->assertFalse($cl->find());
  }  
}
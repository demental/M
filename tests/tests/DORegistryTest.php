<?php
class DORegistryTest extends PHPUnit_Framework_TestCase
{
  public function testSameObject()
  {
    $c1 = DAO::faketory('compte');
    $c2 = DB_DataObject_Pluggable::retreiveFromRegistry('compte',$c1->id);
    $this->assertEquals($c1->id,$c2->id);
    $this->assertEquals($c1->ref,$c2->ref);    

    $this->assertTrue($c1 === $c2);
  }
}

<?php
class tagDBtests extends pluginDBtestcase {
  public function setup()
  {
    $this->loadFixture('base');
    $this->tag = DB_DataObject::factory('tag');
    $this->tag->strip='test';
    $this->tag->insert();
  }
  public function testAddtag()
  {
    $do = DB_DataObject::factory('tagged');
    $this->assertFalse($do->addTag($this->tag),'a tag cannot be added to a record that dont have an ID');
    $do->title = 'test title';
    $do->insert();
    $this->assertTrue($do->addTag($this->tag));    

    $t = DB_DataObject::factory('tag_record');
    $t->record_id = $do->id;
    $t->tag_id = $this->tag->id;
    $t->tagged_table = 'tagged';
    $this->assertTrue($t->find());
    // Adding a tag a second time returns true although....
    $this->assertTrue($do->addTag($this->tag)); 

    // ... It's not recorded twice in the relationship table
    $t = DB_DataObject::factory('tag_record');
    $t->record_id = $do->id;
    $t->tag_id = $this->tag->id;
    $t->tagged_table = 'tagged';
    $t->find(true);
    
    $this->assertFalse($t->fetch(),'A tag can be added only once to a record');

    // Testing on another table to see if table name is saved successfully
    $do2 = DB_DataObject::factory('tagged2');
    $do2->title = 'test title';
    $do2->insert();
    $this->assertTrue($do2->addTag($this->tag));

    $t = DB_DataObject::factory('tag_record');
    $t->record_id = $do2->id;
    $t->tag_id = $this->tag->id;
    $t->tagged_table = 'tagged2';
    $this->assertTrue($t->find());       
  }
  public function testRemovetag()
  {
    // TODO
  }
  public function testRemovetags()
  {
    # code...
  }
  public function testGetbytags()
  {
    # code...
  }
}
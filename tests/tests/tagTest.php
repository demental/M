<?php
class DOTagTest extends PHPUnit_Framework_TestCase
{
  public function destroy_connexion($obj)
  {
    // We destroy the connexion object, this is an ugly way to check connection is not used anymore
    // TODO: create a stub and replace the connection with it.

    $_DB_DATAOBJECT['CONNECTIONS'][$obj->_database_dsn_md5] = 'fakeconnexion';

  }
  public function getTaggable()
  {
    $taggable = DAO::faketory('compte');

    $taggable->addTag('tag1');
    $taggable->addTag('tag2');
    return $taggable;
  }

  public function testAddtag__triggersTagTrigger()
  {
      $taggable = $this->getTaggable();
      require_once APP_ROOT.'tests/lib/tagtrigger_tag_with_trigger.php';
      TagTrigger_tag_with_trigger::$onAddCall = 0;
      $taggable->addTag('tag_with_trigger');
      $this->assertEquals(1,TagTrigger_tag_with_trigger::$onAddCall);
  }

  public function testAddtag__doesNotTriggerTagTriggerIfAlreadyExists()
  {
      $taggable = $this->getTaggable();
      require_once APP_ROOT.'tests/lib/tagtrigger_tag_with_trigger.php';
      TagTrigger_tag_with_trigger::$onAddCall = 0;
      $taggable->addTag('tag_with_trigger');
      $taggable->addTag('tag_with_trigger');

      $this->assertEquals(1,TagTrigger_tag_with_trigger::$onAddCall);
  }

  public function testCacheClearedAtStartup()
  {
        $taggable = DAO::faketory('compte');
        $this->assertEmpty($taggable->tagplugin_cache);
  }

  public function testHastag__CacheCreated()
  {
    $taggable = $this->getTaggable();

    $taggable->hasTag('tag1');
    $this->assertRegexp('`tag(1|2).+tag(1|2)`',$taggable->tagplugin_cache);
  }

  public function testHastag__CacheCreatedAndPersisted()
  {
    $taggable = $this->getTaggable();

    $taggable->hasTag('tag1');
    $taggable->reload();
    $this->assertRegexp('`tag(1|2).+tag(1|2)`',$taggable->tagplugin_cache);
  }

  public function testAddtag__EmptyCache()
  {
    $taggable = $this->getTaggable();
    $taggable->hasTag('tag1');
    // Vidange du cache avec addTag
    $taggable->addTag('tag3');
    $this->assertEmpty($taggable->tagplugin_cache);
  }

  public function testAddtag__EmptyCacheAndPersists()
  {
    $taggable = $this->getTaggable();
    $taggable->hasTag('tag1');
    $taggable->addTag('tag3');
    $taggable->reload();

    $this->assertEmpty($taggable->tagplugin_cache);
  }

  public function testRemovetag__EmptyCache()
  {
    $taggable = $this->getTaggable();
    $taggable->hasTag('tag1');
    $taggable->removeTag('tag2');

    $this->assertEmpty($taggable->tagplugin_cache);
  }

  public function testAddtag__CacheNotChangedIfTagAlreadyPresent()
  {
    $taggable = $this->getTaggable();
    $taggable->addTag('tag3');
    $taggable->hasTag('tag1');
    $taggable->addTag('tag3');

    $this->assertRegexp('`tag(1|2|3).+tag(1|2|3).+tag(1|2|3)`',$taggable->tagplugin_cache);

  }

  public function testHastag__ReturnsTrueIfTagExistsAndCacheEmpty()
  {
    $taggable = $this->getTaggable();
    $taggable->addTag('tag4');
    $this->assertTrue($taggable->hasTag('tag4'));
  }
  public function testHastag__ReturnsTrueIfTagExistsAndCacheFull()
  {
    $taggable = $this->getTaggable();
    $taggable->addTag('tag4');
    $taggable->hasTag('tag4');

    $this->assertTrue($taggable->hasTag('tag2'));
  }

  public function testHastag__ReturnsFalseIfTagDoesNotExistAndCacheEmpty()
  {
    $taggable = $this->getTaggable();
    $taggable->addTag('tag4');
    $this->assertFalse($taggable->hasTag('tag5'));
  }
  public function testHastag__ReturnsFalseIfTagDoesNotExistAndCacheFull()
  {
    $taggable = $this->getTaggable();
    $taggable->addTag('tag4');
    $taggable->hasTag('tag4');

    $this->assertFalse($taggable->hasTag('tag5'));
  }

  public function testHastag__UsesCacheWhenItsNotEmpty()
  {
    global $_DB_DATAOBJECT;
    $taggable = $this->getTaggable();
    $taggable->hasTag('tag1');

    $this->destroy_connexion($taggable);
    $this->assertTrue($taggable->hasTag('tag2'));
  }
  public function testHastag__WorksWhenFetchingMultipleRecords()
  {
    $taggable = $this->getTaggable();
    $taggable2 = $this->getTaggable();
    $taggable2->addTag('tag3');

    $taggable3 = DB_DataObject::factory($taggable->tableName());
    $taggable3->whereAdd('id in("'.$taggable->id.'","'.$taggable2->id.'")');
    $taggable3->find();

    $taggable3->fetch();
    $this->assertEmpty($taggable3->tagplugin_cache);

    $t1 = $taggable3->hasTag('tag3');
    $taggable3->fetch();
    $this->assertEmpty($taggable3->tagplugin_cache);

    $t2 = $taggable3->hasTag('tag3');
    $this->assertFalse($t1==$t2);
  }
  public function testDelete__DeletesTagRecord()
  {
    $taggable = $this->getTaggable();
    $taggable->delete();
    $tag1 = DB_DataObject::factory('tag');
    $tag1->get('strip','tag1');
    $tr = DB_DataObject::factory('tag_record');
    $tr->tagged_table = $taggable->tableName();
    $tr->record_id = $taggable->pk();
    $tr->tag_id = $tag1->id;
    $this->assertFalse((bool)$tr->find());
  }
  public function testDelete__populatesCache()
  {
    $taggable = $this->getTaggable();
    $taggable->delete();
    $this->assertRegexp('`tag(1|2).+tag(1|2)`',$taggable->tagplugin_cache);
  }
  public function testUndelete__recreatesTagsFromCache()
  {
    $taggable = $this->getTaggable();
    $taggable->delete();
    $taggable->undelete();

    $tag1 = DB_DataObject::factory('tag');
    $tag1->get('strip','tag1');
    $tr = DB_DataObject::factory('tag_record');
    $tr->tagged_table = $taggable->tableName();
    $tr->record_id = $taggable->pk();
    $tr->tag_id = $tag1->id;
    $this->assertTrue((bool)$tr->find());

  }
  public function testUndelete__doesNothingIfNoPkSet()
  {
    $taggable = DB_DataObject::factory($this->getTaggable()->tableName());
    $this->destroy_connexion($taggable);
    $taggable->undelete();
  }
  public function testUndelete__doesNotTriggerBackTagTriggers()
  {
    require_once APP_ROOT.'tests/lib/tagtrigger_tag_with_trigger.php';
    TagTrigger_tag_with_trigger::$onAddCall = 0;
    $taggable = $this->getTaggable();
    // onAdd called once ....
    $taggable->addTag('tag_with_trigger');
    $taggable->delete();
    $taggable->undelete();
    // .... not twice
    $this->assertEquals(1,TagTrigger_tag_with_trigger::$onAddCall);
  }

}

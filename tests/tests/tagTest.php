<?php
class DOTagTest extends PHPUnit_Framework_TestCase
{
  public function getTaggable()
  {
    $taggable = DAO::faketory('compte');
    
    $taggable->addTag('tag1');
    $taggable->addTag('tag2');
    return $taggable;
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
    $this->assertRegexp('`tag1.+tag2`',$taggable->tagplugin_cache);
  }

  public function testHastag__CacheCreatedAndPersisted()
  {
    $taggable = $this->getTaggable();
    
    $taggable->hasTag('tag1');
    $taggable->reload();
    $this->assertRegexp('`tag1.+tag2`',$taggable->tagplugin_cache);
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

    $this->assertRegexp('`tag1.+tag2.+tag3`',$taggable->tagplugin_cache);

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
    
    // We destroy the connexion object, this is an ugly way to check connection is not used anymore
    // TODO: create a stub and replace the connection with it.

    $_DB_DATAOBJECT['CONNECTIONS'][$taggable->_database_dsn_md5] = 'fakeconnexion';    
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
}
<?php
require_once TESTS_FOLDER.'extensions/DBTestCase.php';
require_once SIMPLE_TEST.'mock_objects.php';

class TestOfDB_DataObject_Plugin_i18n extends DBTestCase {
  public function setup()
  {
    ini_set('display_errors',1);

    $options = &PEAR :: getStaticProperty('DB_DataObject', 'options');
    $options['class_location'] = PEAR_FOLDER.'M/tests/DO/';
    $options['schema_location'] = PEAR_FOLDER.'M/tests/DO/';    
    $options['class_prefix'] = 'DataObjects_';
    $this->setUpDatabase('Mfixture.sql',1);   
    // Setup 3 langs : fr en es and fr as default
    define('DEFAULT_LANG','es');
    Config::set('installedLangs',array('en','fr','es'));
    // Copy original test DO's
    foreach(FileUtils::getAllFiles(PEAR_FOLDER.'M/tests/DO/') as $file){
      unlink($file);
    }
    foreach(FileUtils::getAllFiles(PEAR_FOLDER.'M/tests/DO_dist/') as $file){
      copy($file,str_replace('DO_dist','DO',$file));
    }
  }
  public function testInsert()
  {
    // Vérification que l'enregistrement principal passe bien et qu'il passe bien dans la langue en cours.
    T::setLang('fr');
    $album = DB_DataObject::factory('album');
    $album->description='Test description album en Français';
    $album->title='Bitch oh my Bitch';
    $this->assertTrue($album->insert());

    $this->assertTrue($album->id);
    // Vérification que l'enregistrement i18n passe bien.
    $i18n = DB_DataObject::factory('album_i18n');
    $i18n->i18n_lang = 'fr';
    $i18n->i18n_record_id = $album->id;
    $this->assertTrue($i18n->find(true));
    $this->assertEqual($i18n->description,'Test description album en Français');    

    T::setLang('en');
    $album2 = DB_DataObject::factory('album');
    $album2->description='Test description album2 in english';
    $album2->title='Oh my';
    $this->assertTrue($album2->insert());
    $this->assertTrue($album2->id);
    $i18n = DB_DataObject::factory('album_i18n');
    $i18n->i18n_lang = 'en';
    $i18n->i18n_record_id = $album2->id;
    $this->assertTrue($i18n->find(true));
    $this->assertEqual($i18n->description,'Test description album2 in english');
  }
  public function testUpdate()
  {
    // Vérification de mise à jour enregistrement (sans changement de langue)
    T::setLang('fr');
    $album = DB_DataObject::factory('album');
    $album->description='Test description album en Français';
    $album->title='Bitch oh my Bitch';
    $album->insert();
    $album->description = 'Test description modifiée';
    $this->assertTrue($album->update());
    $i18n = DB_DataObject::factory('album_i18n');
    $i18n->i18n_lang = 'fr';
    $i18n->i18n_record_id = $album->id;
    $this->assertTrue($i18n->find());
    $i18n->fetch();
    $this->assertEqual($i18n->description,'Test description modifiée');
    // On vérifie qu'un second enregistrement i18n n'a pas été créé plutôt que de mettre à jour l'original
    $this->assertFalse($i18n->fetch());

    // Vérification de la création d'un nouvel enregistrement i18n en cas de changement de langue
    T::setLang('en');
    $album->update();
    $i18n = DB_DataObject::factory('album_i18n');
    $i18n->i18n_lang = 'en';
    $i18n->i18n_record_id = $album->id;
    $this->assertTrue($i18n->find(true));
    $this->assertEqual($i18n->description,'Test description modifiée');    
    $i18n2 = DB_DataObject::factory('album_i18n');
    $i18n2->i18n_lang = 'fr';
    $i18n2->i18n_record_id = $album->id;
    $this->assertTrue($i18n2->find());

    // Vérification que seul l'enregistrement i18n de la langue en cours a été affecté
    $album->description = 'Modified description test';
    $album->update();
    $i18n = DB_DataObject::factory('album_i18n');
    $i18n->i18n_lang = 'en';
    $i18n->i18n_record_id = $album->id;
    $this->assertTrue($i18n->find(true));
    $this->assertEqual($i18n->description,'Modified description test');    
    $i18n2 = DB_DataObject::factory('album_i18n');
    $i18n2->i18n_lang = 'fr';
    $i18n2->i18n_record_id = $album->id;
    $i18n2->find(true);
    $this->assertEqual($i18n2->description,'Test description modifiée');
  }
  public function testUpdateNoni18nValues()
  {
    T::setLang('fr');
    $album = DB_DataObject::factory('album');
    $album->description='Test description album en Français';
    $album->title='Bitch oh my Bitch';
    $album->insert();
    $albumid = $album->id;
    $album->title = 'Bitch2';
    $this->assertTrue($album->update());
    $album = DB_DataObject::factory('album');
    $album->get($albumid);
    $this->assertEqual($album->title,'Bitch2');
  }
  public function testFetch()
  {
    // Setup

    T::setLang('fr');
    $album = DB_DataObject::factory('album');
    $album->description='Test description album en Français';
    $album->title='Bitch oh my Bitch';
    $album->insert();
    T::setLang('en');
    $album->description='Test description album in english';
    $album->update();

    T::setLang('fr');
    $album2 = DB_DataObject::factory('album');
    $album2->description='Test description album2 en Français';
    $album2->title='Oh my';
    $album2->insert();
    T::setLang('en');
    $album2->description='Test description album2 in english';
    $album2->update();

    // Test fetch un enregistrement dans la langue en cours ...
    T::setLang('fr');
    $falbum = DB_DataObject::factory('album');
    $falbum->get($album->id);
    $this->assertEqual($falbum->description,'Test description album en Français');
    T::setLang('en');
    $falbum = DB_DataObject::factory('album');
    $falbum->get($album->id);
    $this->assertEqual($falbum->description,'Test description album in english');    

    // Test fetch sur une requête renvoyant plusieurs enregistrements
    T::setLang('fr');

    $falbum = DB_DataObject::factory('album');
    $falbum->whereAdd('id in ('.implode(',',array($album->id,$album2->id)).')');
    
    $falbum->find();
    $falbum->fetch();
        DB_DataObject::DebugLevel(0);
    $this->assertEqual($falbum->description,'Test description album en Français');
    $falbum->fetch();
    $this->assertEqual($falbum->description,'Test description album2 en Français');        
    T::setLang('en');
    $falbum = DB_DataObject::factory('album');
    $falbum->find();
    $falbum->fetch();
    $this->assertEqual($falbum->description,'Test description album in english');
    $falbum->fetch();
    $this->assertEqual($falbum->description,'Test description album2 in english');
    DB_DataObject::DebugLevel(0);
  }
  public function testmigrateToI18n_UNITS()
  {
    // Test migration de l'ancien plugin international vers le nouveau.
    $notmigrated = DB_DataObject::factory('notmigrated');
    $db = $notmigrated->getDatabaseConnection();
    $iname='notmigrated_i18n';
    // ETAPE 1 : création de la table *_i18n
    // Test que la table copié est bien inexistante
    $this->assertTrue(PEAR::isError($db->query('SELECT id,titre,description,pays,testuser_id FROM notmigrated_i18n')));    
    $this->assertIdentical(true,$notmigrated->getPlugin('international')->migration_createI18nTable($notmigrated,$iname));
    // Test si les champs sont ok dans la table copiée (et qu'elle existe)
    $this->assertFalse(PEAR::isError($db->query('SELECT id,titre,description,pays,testuser_id FROM notmigrated_i18n')));    

    // ETAPE 2 : création des champs index i18n
    // TODO Tester les clés (index)
    $this->assertIdentical(true,$notmigrated->getPlugin('international')->migration_createI18nIndexes($notmigrated,$iname));    
    $this->assertTrue(PEAR::isError($db->query('SELECT id FROM notmigrated_i18n')));    
    $this->assertFalse(PEAR::isError($db->query('SELECT i18n_id,i18n_lang,i18n_record_id FROM notmigrated_i18n')));    

    // ETAPE 3 : suppression des champs non internationaux dans la table i18n
    // Premier test de récup des champs à supprimer
    $this->assertEqual(array('pays'=>array(),'testuser_id'=>array()),$notmigrated->getPlugin('international')->migration_getNonI18nFields($notmigrated,$iname));
    // Deuxième test suppression effective
    $this->assertIdentical(true,$notmigrated->getPlugin('international')->migration_removeNonI18nFields($notmigrated,$iname));
    $this->assertTrue(PEAR::isError($db->query('SELECT pays,testuser_id FROM notmigrated_i18n')));    
    $this->assertFalse(PEAR::isError($db->query('SELECT i18n_id,i18n_lang,i18n_record_id,titre,description FROM notmigrated_i18n')));    

    $this->assertIdentical(true,$notmigrated->getPlugin('international')->migration_copyDataToI18n($notmigrated,$iname));
    $this->assertEqual('Test de description élément 1 (en français)',$db->queryOne('SELECT description FROM notmigrated_i18n WHERE i18n_lang="fr" AND i18n_record_id=1'));
    $this->assertEqual('Test of element one description',$db->queryOne('SELECT description FROM notmigrated_i18n WHERE i18n_lang="en" AND i18n_record_id=1'));
    $this->assertEqual('Testo de la descripccion del elemento uno',$db->queryOne('SELECT description FROM notmigrated_i18n WHERE i18n_lang="es" AND i18n_record_id=1'));    
    $this->assertEqual('élément 1',$db->queryOne('SELECT titre FROM notmigrated_i18n WHERE i18n_lang="fr" AND i18n_record_id=1'));
    $this->assertEqual('element one',$db->queryOne('SELECT titre FROM notmigrated_i18n WHERE i18n_lang="en" AND i18n_record_id=1'));
    $this->assertEqual('elemento uno',$db->queryOne('SELECT titre FROM notmigrated_i18n WHERE i18n_lang="es" AND i18n_record_id=1'));    
    $this->assertEqual('élément 2',$db->queryOne('SELECT titre FROM notmigrated_i18n WHERE i18n_lang="fr" AND i18n_record_id=2'));
    
    $this->assertIdentical(true,$notmigrated->getPlugin('international')->migration_removeI18FieldsFromOriginal($notmigrated,$iname));
    $this->assertFalse(PEAR::isError($db->query('SELECT id,pays,testuser_id FROM notmigrated')));    
    $this->assertTrue(PEAR::isError($db->query('SELECT titre,description FROM notmigrated')));        

    $this->assertIdentical(true,$notmigrated->getPlugin('international')->migration_rebuildObjects($notmigrated,$iname));
    $obj = DB_DataObject::factory('notmigrated_i18n');
    $this->assertFalse(PEAR::isError($obj));
    // Unfortunately we can't "reload" a class declaration, therefore the test below can't be run....
//    $obj2 = DB_DataObject::factory('notmigrated');
//    $this->assertFalse(PEAR::isError($obj2)); 
//    $this->assertEqual($obj2->i18nFields,array('titre','description'));
// SO at least we can check this at least :
    $this->assertTrue(eregi('\$i18nFields',file_get_contents(PEAR_FOLDER.'M/tests/DO/Notmigrated.php')));
    // Test UTF8 (no way, tables must be set to utf8 BEFORE migration)
    $this->assertEqual('например, в Российской',$db->queryOne('SELECT description FROM notmigrated_i18n where i18n_lang="es" AND i18n_record_id=2'));

  }
  public function testmigrateToI18n_GLOBAL()
  {
    // Test migration de l'ancien plugin international vers le nouveau.
    // On teste ici l'action globale
    $notmigrated = DB_DataObject::factory('notmigrated');
    $this->assertTrue($notmigrated->getPlugin('international')->migrateToI18n($notmigrated));
    $obj = DB_DataObject::factory('notmigrated_i18n');// This is useless as anyway the class was previously loaded in the UNIT tests...
    $this->assertFalse(PEAR::isError($obj));
    // This is more accurate :
    $this->assertTrue(file_exists(PEAR_FOLDER.'M/tests/DO/Notmigrated_i18n.php'));
    $this->assertTrue(eregi('\$i18nFields',file_get_contents(PEAR_FOLDER.'M/tests/DO/Notmigrated.php')));
    // Test that other not-migrated tables are not affected
    $this->assertTrue(eregi('\$internationalFields',file_get_contents(PEAR_FOLDER.'M/tests/DO/Notmigrated_withspecialnames.php')));
  }
  public function testmigrateToI18n_withSpecialFieldNames()
  {
    $notmigrated = DB_DataObject::factory('notmigrated_withspecialnames');
    $this->assertTrue($notmigrated->getPlugin('international')->migrateToI18n($notmigrated));
  } 
  public function testprepareTranslationRecords()
  {
    // Main tests
    $langs = Config::getAllLangs();
    $t = DB_DataObject::factory('formtest');
    $t->get(2);
    $res = $t->getPlugin('international')->prepareTranslationRecords($t,$langs);

    $this->assertTrue(is_a($res['fr'],'DataObjects_Formtest_i18n'));
    $this->assertTrue(is_a($res['en'],'DataObjects_Formtest_i18n'));    
    $this->assertTrue(is_a($res['es'],'DataObjects_Formtest_i18n'));
    $this->assertEqual($res['fr']->fb_elementNamePostfix,'_fr');
    $this->assertEqual($res['en']->titre,'element two');
    $this->assertEqual($res['fr']->fb_fieldAttributes['titre'],'size="50" lang="fr"');
    $this->assertEqual($res['es']->fb_fieldAttributes['description'],'class="red" lang="es"');
    // Testing elementnamepostfix is OK
    $t = DB_DataObject::factory('formtest');
    $t->get(2);
    $t->fb_elementNamePostfix ='_test';
    $res = $t->getPlugin('international')->prepareTranslationRecords($t,$langs);
    $this->assertEqual($res['fr']->fb_elementNamePostfix,'_test_fr');

    // Testing with a new record (no pk)
    $t = DB_DataObject::factory('formtest');
    $res = $t->getPlugin('international')->prepareTranslationRecords($t,$langs);

    $this->assertTrue(is_a($res['fr'],'DataObjects_Formtest_i18n'));
    $this->assertTrue(is_a($res['en'],'DataObjects_Formtest_i18n'));    
    $this->assertTrue(is_a($res['es'],'DataObjects_Formtest_i18n'));

    // Testing with fewer langs
    $t = DB_DataObject::factory('formtest');
    $res = $t->getPlugin('international')->prepareTranslationRecords($t,array('en','es'));
    $this->assertFalse(is_a($res['fr'],'DataObjects_Formtest_i18n'));
    $this->assertTrue(is_a($res['en'],'DataObjects_Formtest_i18n'));    
    $this->assertTrue(is_a($res['es'],'DataObjects_Formtest_i18n'));
    
  }

  public function testCreateForm()
  {
    $t = DB_DataObject::factory('formtest');
    $t->get(2);
    $fb = MyFB::create($t);
    $form = $fb->getForm();
    $this->assertTrue($form->elementExists('titre_group'));
    $this->assertTrue($form->elementExists('description_group'));    

    $grp = $form->getElement('titre_group');
    $this->assertEqual('group',$grp->getType());
    $elems = $grp->getElements();
    $this->assertEqual('text',$elems[0]->getType());
    $this->assertEqual('text',$elems[1]->getType());    
    $this->assertEqual('text',$elems[2]->getType());    
    $values = $form->exportValues();
    $this->assertEqual($values['titre_group'],array('titre_fr'=>'élément 2','titre_en'=>'element two','titre_es'=>'elemento dos'));
  }
  public function testProcessFormUpdate()
  {
    $t = DB_DataObject::factory('formtest');
    $t->get(2);
    $fb = MyFB::create($t);
    $form = $fb->getForm();
    $post = array_merge($form->exportValues(),array(
      'titre_group'=>array('titre_fr'=>'Modif elt 2','titre_en'=>'Mod element two','titre_es'=>'Mod elemento dos'),
      'description_group'=>array('description_fr'=>'Modif desc 2 FR','description_en'=>'Mod desc two EN','description_es'=>'Mod desc dos ES'),      
      '_qf__' . $form->getAttribute('name')=>1
      ));      
    $get = $_GET;
    $request = array_merge($get,$post);

    $form->initRequest($get,$post,$request);
    $this->assertTrue($form->isSubmitted());
    $form->process(array($fb,'processForm'),false);
    T::setLang('fr');
    $t = DB_DataObject::factory('formtest');
    $t->get(2);
    $this->assertEqual($t->titre,'Modif elt 2');
    T::setLang('en');
    $t = DB_DataObject::factory('formtest');
    $t->get(2);
    $this->assertEqual($t->titre,'Mod element two'); 
   
  }
  public function testProcessFormUpdateNoni18nFields()
  {
    $t = DB_DataObject::factory('formtest');
    $t->get(2);
    $fb = MyFB::create($t);
    $form = $fb->getForm();
    $post = array_merge($form->exportValues(),array(
      'pays'=>'es',
      'testuser_id'=>'5',
      '_qf__' . $form->getAttribute('name')=>1
      ));      
    $get = $_GET;
    $request = array_merge($get,$post);

    $form->initRequest($get,$post,$request);
    $this->assertTrue($form->isSubmitted());
//    DB_DataObject::DebugLevel(1);
    $form->process(array($fb,'processForm'),false);
    DB_DataObject::DebugLevel(0);
    T::setLang('fr');
    $t = DB_DataObject::factory('formtest');
    $t->get(2);
    $this->assertEqual($t->pays,'es');
    $this->assertEqual($t->testuser_id,'5'); 
   
  }  
  public function testProcessFormInsert()
  {

    $t = DB_DataObject::factory('formtest');
    $fb = MyFB::create($t);
    $form = new MyQuickForm('testform','POST',M_Office::URL());
    
    $post = array_merge($form->exportValues(),array(
      'titre_group'=>array('titre_fr'=>'Nouv elt 2','titre_en'=>'New element two','titre_es'=>'nuevo elemento dos'),
      'description_group'=>array('description_fr'=>'Nouv desc 2 FR','description_en'=>'New desc two EN','description_es'=>'nuevo desc dos ES'),      
      'pays'=>'ma',
      'testuser_id'=>1,
      '_qf__' . $form->getAttribute('name')=>1,
      'i18n_id_fr'=>'',
      'i18n_id_en'=>'',      
      'i18n_id_es'=>'', 
      'id'=>''     
      ));

    $get = $_GET;
    $request = array_merge($get,$post);
    

    $form->initRequest($get,$post,$request);

    $fb->useForm($form);
    $fb->getForm();
    $this->assertTrue($form->validate());
    $this->assertTrue($form->isSubmitted());
    $values = $form->exportValues();


    $t->getPlugin('international')->preProcessForm($values,$fb,$t);
    $expected = array(
      "id"=>"",
      "pays"=>"ma",
      "testuser_id"=>"1",
      "i18n_id_fr"=>"",
      "i18n_lang_fr"=>"fr",
      "i18n_id_en"=>"",
      "i18n_lang_en"=>"en",
      "i18n_id_es"=>"",
      "i18n_lang_es"=>"es",
      "titre_fr"=>"Nouv elt 2",
      "titre_en"=>"New element two",
      "titre_es"=>"nuevo elemento dos",
      "description_fr"=>"Nouv desc 2 FR",
      "description_en"=>"New desc two EN",
      "description_es"=>"nuevo desc dos ES");
    $this->assertEqual(ksort($values),ksort($expected));
    $form->process(array($fb,'processForm'),false);
    T::setLang('fr');
    $tid = $t->id;
    $t = DB_DataObject::factory('formtest');
    $t->get($tid);
    $this->assertEqual($t->titre,'Nouv elt 2');
    T::setLang('en');
    $t = DB_DataObject::factory('formtest');
    $t->get($tid);
    $this->assertEqual($t->titre,'New element two');    
    T::setLang('es');
    $t = DB_DataObject::factory('formtest');
    $t->get($tid);
    $this->assertEqual($t->titre,'nuevo elemento dos');
  }
  public function testValidate()
  {
    T::setLang('fr');
    $t = DB_DataObject::factory('formtest');
    $fb = MyFB::create($t);
    $form = new MyQuickForm('testform','POST',M_Office::URL());
    
    $post = array_merge($form->exportValues(),array(
      'titre_group'=>array('titre_fr'=>null,'titre_en'=>'New element two','titre_es'=>'nuevo elemento dos'),
      'description_group'=>array('description_fr'=>'Nouv desc 2 FR','description_en'=>'New desc two EN','description_es'=>'nuevo desc dos ES'),      
      'pays'=>'ma',
      'testuser_id'=>1,
      '_qf__' . $form->getAttribute('name')=>1,
      'i18n_id_fr'=>'',
      'i18n_id_en'=>'',      
      'i18n_id_es'=>'', 
      'id'=>''     
      ));

    $get = $_GET;
    $request = array_merge($get,$post);
    

    $form->initRequest($get,$post,$request);

    $fb->useForm($form);
    $fb->getForm();
    $this->assertFalse($form->validate());
    echo $form->toHtml();
  }  
  public function testSetTranslation()
  {
    T::setLang('fr');
    $t = DB_DataObject::factory('formtest');
    $t->_loadPlugins();
    $this->assertFalse($t->getPlugin('international')->setTranslation($t,'titre','test titre','en'));
    $t = DB_DataObject::factory('formtest');
    $t->get(2);
    $t->getPlugin('international')->setTranslation($t,'titre','test title english','en');
    T::setLang('en');
    $t = DB_DataObject::factory('formtest');
    $t->get(2);
    $this->assertEqual('test title english',$t->titre);
    // Test that other fields are not affected
    $this->assertEqual('Test of description for element two',$t->description);
    // Test that other langs are not affected    
    T::setLang('fr');
    $t = DB_DataObject::factory('formtest');
    $t->get(2);
    $this->assertEqual('élément 2',$t->titre);
    // Test that other records are not affected
    T::setLang('en');
    $t = DB_DataObject::factory('formtest');
    $t->get(1);
    $this->assertEqual('element one',$t->titre);
  }
  public function testSetPoly()
  {
    T::setLang('fr');
    $t = DB_DataObject::factory('formtest');
    $t->get('2');
    $arr = array('fr'=>'Elt2 FR','en'=>'Elt2 EN','es'=>'Elt2 ES');
    $t->getPlugin('international')->setPoly($t,'titre',$arr);
    $t = DB_DataObject::factory('formtest');
    $t->get('2');
    $this->assertTrue($t->titre,'Elt2 FR');
    // Test that other fields are not affected
    $this->assertEqual('Test de description élément 2 (en français)',$t->description);
    // Test that other langs are affected    
    T::setLang('en');    
    $t = DB_DataObject::factory('formtest');
    $t->get('2');
    $this->assertTrue($t->titre,'Elt2 EN');
    T::setLang('es');    
    $t = DB_DataObject::factory('formtest');
    $t->get('2');
    $this->assertTrue($t->titre,'Elt2 ES');
    // Test that other records are not affected
    T::setLang('en');
    $t = DB_DataObject::factory('formtest');
    $t->get(1);
    $this->assertEqual('element one',$t->titre);
  }
}
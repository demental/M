<?php
class PluginI18nFormbuilder {
  public function before_form($fb)
  {
    $obj = $fb->_do;
    $langs = $this->getLocales($obj);
    $obj->_i18ndos = $this->prepareTranslationRecords($obj,$langs);
  }

  public function after_form($form,$fb){
    $obj = $fb->_do;
    $info = $obj->_getPluginsDef();
    $info = $info['i18n'];
    $langs = $this->getLocales($obj);
    foreach($obj->_i18ndos as $lang=>$arec) {
      if($this->_grouped) {
        $obj->_i18ndos[$lang]->fb_addFormHeader = false;
      }
      $obj->_i18nfbs[$lang] = MyFB::create($obj->_i18ndos[$lang]);

      $obj->_i18nfbs[$lang]->useForm($form);
      $obj->_i18nfbs[$lang]->getForm();
    }

    if($this->_grouped) {
      $this->after_form_grouped($form,$fb,$info,$langs);
      return;
    }

    $elements = $obj->_i18nfbs[$lang]->_reorderElements();

    if(is_array($obj->fb_fieldsToRender)) {
      $iFields = array_intersect($info,$obj->fb_fieldsToRender);
    } else {
      $iFields = $info;
    }
    $allLangs = array_diff($langs,array($this->getDefaultLang($obj)));
    array_unshift($allLangs,$this->getDefaultLang($obj));
    $langs = $allLangs;
    foreach($iFields as $field) {
      $fields = array();
      foreach($langs as $lang) {
        $completename = $obj->fb_elementNamePrefix.$field.$obj->fb_elementNamePostfix;

        $elem = $form->getElement($completename.'_'.$lang);
        $elem->setAttribute('rel',$completename);
        if($lang == $this->getDefaultLang($obj)) {
          $class='translatesource field_'.$lang;
          $id = 'autotransid_'.$completename;
        } else {
          $class='autotranslate source_autotransid_'.$completename.' field_'.$lang;
          $id = 'autotransid_'.$completename.'_'.$lang;
        }
        $elem->setAttribute('class',$elem->getAttribute('class').($elem->getAttribute('class')?' ':'').$class);
      }
    }
  }

  public function after_save(&$values,$fb)
  {
    $obj = $fb->_do;
    foreach($this->getLocales($obj) as $lang) {
      $obj->_i18ndos[$lang]->i18n_record_id = $obj->pk();
      $obj->_i18nfbs[$lang]->processForm($values);
    }
  }


  public function after_form_grouped($form,$fb,$info,$langs)
  {
    $obj = $fb->_do;
    $lang = $langs[0];
      $elements = $obj->_i18nfbs[$lang]->_reorderElements();
      if(is_array($obj->fb_fieldsToRender)) {
        $iFields = array_intersect($info,$obj->fb_fieldsToRender);
      } else {
        $iFields = $info;
      }
      $allLangs = array_diff($langs,array($this->getDefaultLang($obj)));
      array_unshift($allLangs,$this->getDefaultLang($obj));
      $langs = $allLangs;

      foreach($iFields as $field) {
        $fields = array();
        foreach($langs as $lang) {
          $completename = $obj->fb_elementNamePrefix.$field.$obj->fb_elementNamePostfix;

          $elem = $form->getElement($completename.'_'.$lang);

          $elem->setAttribute('rel',$completename);
          if($lang == $this->getDefaultLang($obj)) {
            $class='translatesource field_'.$lang;
            $id = 'autotransid_'.$completename;
          } else {
            $class='autotranslate source_autotransid_'.$completename.' field_'.$lang;
            $id = 'autotransid_'.$completename.'_'.$lang;
          }
          $elem->setAttribute('class',$elem->getAttribute('class').($elem->getAttribute('class')?' ':'').$class);
          $elem->setAttribute('id',$id);
          $label = $elem->getLabel();
          if(is_array($label)) {
            $sublabel = $label[0];
          } else {
            $sublabel = $label;
          }
          $elem->setLabel($sublabel.'('.$lang.')');
          $fields[] =$elem;
          $form->removeElement($completename.'_'.$lang);

        }
        if(!$form->elementExists('__submit__')) {
          $form->addElement('group', $completename.'_group',$label,$fields,'');
        } else {
          $form->insertElementBefore(MyQuickForm::createElement('group', $completename.'_group',$label,$fields),'__submit__');
        }
        if(in_array($field,$fb->fieldsRequired) || ($elements[$field] & DB_DATAOBJECT_NOTNULL)) {
            $form->addGroupRule($completename.'_group',$fb->requiredRuleMessage,'required',null,1);
        }
      }
    }

  public function before_save(&$values,$fb)
  {
    $obj = $fb->_do;
    if($this->_grouped) {
      $this->before_save_grouped($values,$fb);
      return;
    }

    $info = $obj->_getPluginsDef();
    $info = $info['i18n'];
    $elements = $obj->_i18nfbs[T::getLocale()]->_reorderElements();
    // To avoid duplicate saving of current lang record
    $this->_dontSavei18n = true;
    unset($obj->i18n_lang);
    unset($obj->i18n_record_id);
    unset($obj->i18n_id);
    $obj->whereAdd();
    if($obj->pk()) {
      $db = $obj->getDatabaseConnection();
      $obj->whereAdd($db->quoteIdentifier($obj->pkName()).' = '.$db->quote($obj->pk()));
    }
  }
  public function after_save_grouped(&$values,$fb)
  {
    $obj = $fb->_do;
    $elements = $obj->_i18nfbs[T::getLang()]->_reorderElements();
    $info = $obj->_getPluginsDef();

    foreach($info['i18n'] as $field) {
      $completename = $obj->fb_elementNamePrefix.$field.$obj->fb_elementNamePostfix;

      $fieldhasempty = false;
      $nonempty=null;
      foreach($this->getLocales($obj) as $lang) {
        $values[$completename.'_'.$lang] = $values[$field.'_group'][$completename.'_'.$lang];
        if(empty($values[$completename.'_'.$lang])) {
            $fieldhasempty = true;
        } else {
            $nonempty = $values[$completename.'_'.$lang];
        }
      }
      if($fieldhasempty && (in_array($field,$fb->fieldsRequired) || ($elements[$field] & DB_DATAOBJECT_NOTNULL))) {
          foreach($this->getLocales($obj) as $lang) {

              if(empty($values[$completename.'_'.$lang])) {
                  $values[$completename.'_'.$lang] = $nonempty;
              }
          }
      }
      unset($values[$completename.'_group']);
    }

    // To avoid duplicate saving of current lang record
    $this->_dontSavei18n = true;
    unset($obj->i18n_lang);
    unset($obj->i18n_record_id);
    unset($obj->i18n_id);
  }

  /**
   * Generates a FormBuilder instance for each language.
   * @params DB_DataObject main record
   * @param array languages for which to create the FormBuilders
   */
  protected function prepareTranslationRecords($obj,$langs)
  {
    $info = $obj->_getPluginsDef();
    $info = $info['i18n'];
    $out = array();
    $tablename = $obj->tableName().'_i18n';
    if(is_array($obj->fb_fieldsToRender)) {
      $iFields = array_intersect($info,$obj->fb_fieldsToRender);
    }
    if(count($iFields) == 0) $iFields = $info;
    foreach($langs as $lang) {
      $t = DB_DataObject::factory($tablename);
      $t->i18n_lang=$lang;
      $t->i18n_record_id = $obj->pk();
      if($obj->pk()) {
        $t->find(true);
      }
      foreach(get_object_vars($obj) as $var=>$val) {
        if(preg_match('`^fb_`',$var)) {
          $t->$var = $val;
        }
      }

      $t->fb_elementNamePostfix.='_'.$lang;
      foreach($info as $field) {
        if(!is_array($t->fb_fieldAttributes[$field])) {
          $t->fb_fieldAttributes[$field].=($t->fb_fieldAttributes[$field]?' ':'').'lang="'.$lang.'"';
        } else {
          $t->fb_fieldAttributes[$field]['lang'] = $lang;
        }
      }
      if(is_array($t->fb_preDefElements)) {
        foreach($t->fb_preDefElements as $key=>$elem) {
          $elem2 = clone($elem);
          if(method_exists($elem2,'updateAttributes')) {
            $elem2->updateAttributes(array('name'=>$elem2->getAttribute('name').'_'.$lang,'lang'=>$lang));
          }
          $t->fb_preDefElements[$key] = $elem2;


        }
      }
      $t->fb_fieldsToRender = $iFields;
      $t->fb_createSubmit = false;
      $t->fb_addFormHeader = true;
      $t->fb_formHeaderText = $lang;
      $out[$lang] = $t;
    }
    return $out;
  }

  protected function getDefaultLang($obj)
  {
    return T::getLocale();
  }

  protected function getLocales($obj)
  {
    return Config::getAllLocales();
  }
}

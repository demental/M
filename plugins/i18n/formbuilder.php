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
      $this->postGenerateFormGrouped($form,$fb,$info,$langs,$obj);
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

  protected function getLocales($obj)
  {
    return Config::getAllLocales();
  }
}

<?php

class Plugin_user_formbuilder {
  public function before_form($fb)
  {
    $defs = $obj->_getPluginsDef();
    $defs = $defs['user'];
    $obj->fb_preDefElements[$defs['pwd']] = MyQuickForm::createElement('password',$defs['pwd'],$obj->fb_fieldLabels[$defs['pwd']]);
    $obj->fb_excludeFromAutoRules[]=$defs['pwd'];
  }
  public function after_form($form, $fb) {
    $defs = $obj->_getPluginsDef();
    $defs = $defs['user'];
        if($form->elementExists($fb->elementNamePrefix.$defs['pwd'].$fb->elementNamePostfix)) {

            $pwd = $form->getElement($fb->elementNamePrefix.$defs['pwd'].$fb->elementNamePostfix);
            $pwd->setLabel($obj->fb_fieldLabels[$defs['pwd']].__('(Vérification)'));
            $pwd->setValue('');
            $pwd2 = MyQuickForm::createElement('password',$fb->elementNamePrefix.$defs['pwd'].'2'.$fb->elementNamePostfix,
              $obj->fb_fieldLabels[$defs['pwd']]);

            if(empty($obj->id)) {
              $form->addRule($fb->elementNamePrefix.$defs['pwd'].$fb->elementNamePostfix,__('user.register.error.required_password'),'required');
              $form->addRule($fb->elementNamePrefix.$defs['pwd'].'2'.$fb->elementNamePostfix,__('user.register.error.required_password'),'required');
            }
            $form->insertElementBefore($pwd2,$fb->elementNamePrefix.$defs['pwd'].$fb->elementNamePostfix);
            $form->addRule(array($fb->elementNamePrefix.$defs['pwd'].'2'.$fb->elementNamePostfix,$fb->elementNamePrefix.$defs['pwd'].$fb->elementNamePostfix),__('user.register.error.unmatched_password'),'compare');
            $form->addFormRule(array($this,'checkUniqueLogin'));

        }
        $form->setDefaults(array($fb->elementNamePrefix.$defs['pwd'].$fb->elementNamePostfix=>'',$fb->elementNamePrefix.$defs['pwd'].'2'.$fb->elementNamePostfix=>''));
        $this->_obj = $fb->_do;
  }
  public function checkUniqueLogin($values) {
    $defs = $this->_obj->_getPluginsDef();
    $defs = $defs['user'];
    $test = DB_DataObject::factory($this->_obj->tableName());
    $test->whereAdd("id!='".$this->_obj->id."'");
    $test->{$defs['login']} = $values[$defs['login']];
    if($test->find(true)) {
      return array($defs['login']=>__('user.register.error.already_exists'));
    }
    return true;
  }
  public function before_save(&$values, $fb) {
        $defs = $this->_obj->_getPluginsDef();
        $defs = $defs['user'];
        if(!is_array($fb->userEditableFields) || count($fb->userEditableFields)==0) {
              $fb->populateOptions();
        }
        if(count($fb->userEditableFields) ==0) {
          $fb->userEditableFields = $fb->fieldsToRender;
        }
      if(empty($values[$fb->elementNamePrefix.$defs['pwd'].$fb->elementNamePostfix])) {
          if($index = array_search($defs['pwd'],$fb->userEditableFields)) {
              unset($fb->userEditableFields[$index]);
          }
      } elseif($defs['passEncryption']) {
          $values[$fb->elementNamePrefix.$defs['pwd'].$fb->elementNamePostfix] = call_user_func($defs['passEncryption'],$values[$fb->elementNamePrefix.$defs['pwd'].$fb->elementNamePostfix]);
      } else {
        $values[$fb->elementNamePrefix.$defs['pwd'].$fb->elementNamePostfix] = $this->encrypt($values[$fb->elementNamePrefix.$defs['pwd'].$fb->elementNamePostfix],$this->_obj)->return;
      }
  }
}

<?php
// ====================
// = Ownership plugin
// = Deprecated DO NOT USE !!
// ====================
if(!defined('NORMALUSER')){
	define ('NORMALUSER',0);
}
if(!defined('ADMINUSER')){
	define ('ADMINUSER',1);
}
if(!defined('ROOTUSER')){
	define ('ROOTUSER',2);
}

require_once 'M/DB/DataObject/Plugin.php';
class DB_DataObject_Plugin_Ownership extends DB_DataObject_Plugin
{
  public $plugin_name='ownership';
  protected $userAdminModeForDBDOFBFE;
  
	function preGenerateForm(&$fb,&$obj)
	{
		if($obj->filterowner ||!defined('ROOT_ADMIN_URL')) {
      if(!is_array($obj->fb_fieldsToRender)) {
        $obj->fb_fieldsToRender = array_keys($obj->table());
      }
			$index=array_search($obj->ownerShipField,$obj->fb_fieldsToRender);
			if($index!==false) {
				$obj->fb_fieldsToRender[$index]='';
			}
		} elseif(defined('ROOT_ADMIN_URL') && $this->userIsInAdminMode()) {
			$obj->fb_fieldsToRender[]=$obj->ownerShipField;
			$obj->fb_fieldLabels[$obj->ownerShipField]="Géré par";
			if(!is_array($obj->fb_userEditableFields)){
    				$obj->fb_userEditableFields=$obj->fb_fieldsToRender;
            }
    		$index=array_search($obj->ownerShipField,$obj->fb_userEditableFields);
    		if($index!==false) {
    			$obj->fb_userEditableFields[$index]='';
    		}
		}
	}

	function prepareLinkedDataObject(&$linkedDataObject, $field,&$obj)
	{
		if($obj->filterowner){
			$linkedDataObject->filterowner=$obj->filterowner;
		}
	}
	function insert(&$obj)
	{
		if($obj->filterowner){
			$obj->{$obj->ownerShipField}=$obj->filterowner;
		}
	}
	function getSingleMethods() {
        if($this->userIsInAdminMode()) {
            return array('updateOwner'=>array('title'=>'Changer le gérant','plugin'=>$this->plugin_name));
        }
	}
	function getBatchMethods() {
        if($this->userIsInAdminMode()) {
            return array('batchUpdateOwner'=>array('title'=>'Changer le gérant','plugin'=>$this->plugin_name));
        } 
	}
	function find($autoFetch=false,&$obj)
	{
		if($obj->filterowner){
			$obj->{$obj->ownerShipField}=$obj->filterowner;
		}
	}
	function count(&$obj)
	{
		if($obj->filterowner){
			$obj->{$obj->ownerShipField}=$obj->filterowner;
		}
	}
	function userIsInAdminMode($mode=null){
		if($mode !==null){
			$this->userAdminModeForDBDOFBFE=$mode;
		}
		if($this->userAdminModeForDBDOFBFE>NORMALUSER){
			return true;
		} else {
			return false;
		}	
	}
	function prepareBatchUpdateOwner(&$obj) {
	    return $this->prepareUpdateOwner($obj);
	}
	function batchUpdateOwner(&$obj, $owner) {
	    while ( $obj->fetch() )
	    {
	       $this->updateOwner($obj,$owner);
	    }
	}
	function prepareUpdateOwner(&$obj) {
	    $o = clone($obj);
	    $o->fb_fieldsToRender=array($obj->ownerShipField);
	    $o->fb_userEditableFields=array($obj->ownerShipField);
		$fb=& DB_DataObject_FormBuilder::create($o);
        $fb->preGenerateFormCallback = 'fake';
        $fb->postGenerateFormCallback = 'fake';
		$form=& $fb->getForm();
		$selectOwner=$form->getElement($obj->ownerShipField);
	    return array($selectOwner);
	}
	function updateOwner(&$obj, $owner){
		if(!key_exists($obj->ownerShipField,$obj->table())){
			return;
		}	
		require_once 'DB/DataObject/FormBuilder.php';
		if(($obj->filterowner || !empty($owner)) && !in_array($obj->tableName(),$obj->_alreadyUpdatedOwner)){
			$obj->{$obj->ownerShipField}=$owner;
			$ownername = $obj->getLink($obj->ownerShipField)->__toString();
			$obj->update();
			$obj->_alreadyUpdatedOwner[]=$obj->tableName();
			$pk=DB_DataObject_FormBuilder::_getPrimaryKey($obj);
			$obj->say($obj->__toString().' est maintenant géré par '.$ownername);
			foreach($obj->reverseLinks() as $link=>$field){
                list($linkTab, $linkField) = explode(':', $link);
	            if (!empty($obj->$field) && !in_array($linkTab,$obj->_alreadyUpdatedOwner)) {
                    
					$linkDo=& DB_DataObject::factory($linkTab);
  					$linkDo->$linkField=$obj->$field;
  					if($linkDo->find() && isset($linkDo->ownerShipField)){
                        $obj->say('-------- propagation sur '.$linkTab.' --------');

	  					while($linkDo->fetch()){
	  						$linkDo->_alreadyUpdatedOwner=$obj->_alreadyUpdatedOwner;
	  						$linkDo->getPlugin('ownership')->updateOwner($linkDo,$owner);
	  					}
						}
					}
				}
			}
			foreach($obj->links() as $field=>$link){
    		list($linkTab, $linkField) = explode(':', $link);
    		if(!in_array($linkTab,$obj->_alreadyUpdatedOwner)){
  				$linkDo=& DB_DataObject::factory($linkTab);
      			if(isset($linkDO->ownerShipField)){
					if($linkDo->get($obj->$field)){
						$linkDo->_alreadyUpdatedOwner=$obj->_alreadyUpdatedOwner;
						$linkDo->getPlugin('ownership')->updateOwner($linkDo,$owner);
					}    
  	            }
			}			    
		 }
	  }	
	
}
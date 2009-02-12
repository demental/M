<?php
// ============================
// = Rich text editor plugin
// = Deprecated
// ============================
require_once "M/DB/DataObject/Plugin.php";

class DB_DataObject_Plugin_Rich extends DB_DataObject_Plugin
{
    public $plugin_name='rich';
	var $_dataObject;

	function preGenerateForm(&$fb,&$obj)
	{
	    foreach($obj->richFields as $field) {
	      if(!is_array($field)) {
	        $obj->fb_fieldAttributes[$field].= 'class="rich"';
        }
	    }
        if(class_exists('Mtpl')) {
            Mtpl::addJS(
                    array(
                    'wymeditor/jquery.wymeditor',
                    'wymeditor/jquery.wymeditor.mozilla',
                    'wymeditor/jquery.wymeditor.opera',
                    'wymeditor/lang/fr'
                    )
                    );
            Mtpl::addCSS('wymeditor/skins/c2s/screen');
            $options = is_array($obj->richFields['options'])?$obj->richFields['options']:array();
            Mtpl::addJSinline('$("textarea.rich").wymeditor({'.$this->parseJSOptions($options).'});','ready');
        }
    }
    function parseJSOptions($opt) {
      $out='';
      $aj=',';
      $out='sLang:"'.T::getLang().'"';
      foreach ($opt as $k=>$v) {
        $out.=$aj.$k.':"'.$v.'"';
        $aj=',';
      }
      return $out;
    }
}
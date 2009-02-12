<?php

// ===============================
// = Wiki fields handling plugin =
// = Might be deprecated or at least needs refactoring
// ===============================

require_once 'M/DB/DataObject/Plugin.php';

function previewornot($data){
	if(!empty($data['wiki_showpreview'])){
		$errors['previewButton']="mode prévisualisation. Aucun enregistrement n'a été effectué";
		return $errors;
	}
	return true;
}

class DB_DataObject_Plugin_Wiki extends DB_DataObject_Plugin
{
    public $plugin_name='wiki';
	var $_dataObject;
	
	function register(&$obj)
	{
		$this->_dataObject = &$obj;
	}

	function preGenerateForm(&$fb,&$obj)
	{
		foreach($obj->wikiFields as $k){			
			$obj->fb_fieldLabels[$k]['note']='Ce champ supporte la syntaxe wiki. <a href="javascript:popup(\'syntaxe_wiki.html\')">Aide sur la syntaxe wiki</a>';
		}
	}
	function postGenerateForm(&$form,&$fb,&$obj)
	{
		$addWiki=false;
		foreach($obj->wikiFields as $k){
			$field=$fb->elementNamePrefix.$k.$fb->elementNamePostfix;
			if($form->elementExists($field)){
				$addWiki=true;
			$js='<script type="text/javascript" src="js/toolbar.js"></script><script type="text/javascript">if (document.getElementById) {
					var tb = new dcToolBar(document.getElementById(\''.$field.'_w\'),
					\'wiki\',\'images/wikitoolbar/\');
					tb.btStrong(\'Forte emphase\');
					tb.btEm(\'Emphase\');
					tb.btIns(\'Inséré\');
					tb.btDel(\'Supprimé\');
					tb.btQ(\'Citation en ligne\');
					tb.btCode(\'Code\');
					tb.addSpace(10);
					tb.btBr(\'Saut de ligne\');
					tb.addSpace(10);
					tb.btBquote(\'Bloc de citation\');
					tb.btPre(\'Texte préformaté\');
					tb.btList(\'Liste non ordonnée\',\'ul\');
					tb.btList(\'Liste ordonnée\',\'ol\');
					tb.addSpace(10);
					tb.btLink(\'Lien\',
						\'URL ?\',
						\'Langue ?\',
						\'fr\');
					tb.btImgLink(\'Image externe\',
						\'URL ?\');
					tb.addSpace(10);
					tb.btImg(\'Image interne\',\''.(defined('ROOT_ADMIN_URL')?ROOT_ADMIN_URL:SITE_URL).'images-popup.php?target='.$field.'_w\');
					tb.draw(\'Vous pouvez utiliser les raccourcis suivants pour enrichir votre présentation.\');
				}
				</script>';
			$prev=& HTML_QuickForm::createElement('static',null,null,'<div style="border:1px solid #ccc;padding:5px;"><h3 style="margin:-5px;padding:0;background:#aaa;color:#fff">Prévisualisation du champ '.(is_array($fb->fieldLabels[$k])?$fb->fieldLabels[$k][0]:$fb->fieldLabels[$k]).'</h3>'.modifier_wikiparser($form->exportValue($fb->elementNamePrefix.$k.$fb->elementNamePostfix)).'</div>');
			$form->insertElementBefore($prev,$field);
			$f=& $form->getElement($field);
			$f->updateAttributes(array('id'=>$field.'_w','style'=>'width:100%'));					
			$f->_label['unit']=$js;
		}
		}
		if($addWiki){
			$form->addElement('hidden','wiki_showpreview',0);
			$prev=& $form->getElement('wiki_showpreview');
			$prev->setValue(0);
			$form->addElement('button','previewButton','Prévisualiser',array('onclick'=>"this.form.wiki_showpreview.value=1;this.value='Veuillez patienter';submit()"));
			$form->addFormRule('previewornot');	
		}
	}
	function preProcessForm(&$values,&$fb,&$obj)
	{
		return;
	}
	function prepareLinkedDataObject(&$linkedDataObject, $field,&$obj)
	{
		return;
	}
	function postProcessForm(&$v,&$fb,&$obj)
	{
		return;
	}
	function insert(&$obj)
	{
		return;
	}
	function update(&$obj)
	{
		return;
	}
	function prefetch(&$obj)
	{
		return;
	}
	function postfetch(&$obj)
	{
		return;
	}
	function find($autoFetch=false,&$obj)
	{
		return;
	}
	function count(&$obj)
	{
	  return;
	}
	function delete(&$obj)
	{
		return;
	}
  function dateOptions($field, &$fb,&$obj) {
		return;
	}
}

require_once 'classes/wiki2xhtmlbasic.php';
require_once 'classes/wiki2xhtml.php';
if(!function_exists('modifier_wikiparser')) {
    function modifier_wikiparser($string)
    {
    	$wiki=new wiki2xhtml();
    	$result=$wiki->transform($string);
    	return $result;
    }
}
<?php
// ===============================================
// = WTF is this....
// = anyway... not in use for now...
// ===============================================

class M_Office_liveedit extends M_Office_Controller
{
    // @param   string the string to search for
    // @param   string (table for which to expand view)
    // Expand 
    
    function M_Office_liveedit($url) {
        M_Office_Controller::M_Office_Controller();
        $this->url=$url;
        $this->module=str_replace(SITE_URL,'',$url);
    }
    function processRequest() {
      
      $ret='<h3>Modifier '.$this->module.'</h3>';
      $ret.='<ul>'.
      '<li><a href="'.M_Office_Util::getQueryParams(array('table'=>'cms','liveedit'=>$this->module)).'" rel="modpage">Modifier page</a></li>'.
      '<li><a href="'.M_Office_Util::getQueryParams(array('table'=>'cms','liveedit'=>$this->module,'field'=>'title')).'" rel="modtitle">Modifier titre</a></li>'.
      '<li><a href="'.M_Office_Util::getQueryParams(array('table'=>'cms','liveedit'=>$this->module,'field'=>'head')).'" rel="modheader">Modifier entête</a></li>'.
      '<li><a href="'.M_Office_Util::getQueryParams(array('table'=>'cms','liveedit'=>$this->module,'field'=>'body')).'" rel="modbody">Modifier corps</a></li>'.
      '</ul>
      <h3>Ajouter une page</h3>'.
      '<ul>'.
      '<li><a href="truc">En dessous</a></li>'.
      '<li><a href="truc">Même niveau</a></li>'.
      '
      </ul>
      <h3>Vider le cache</h3>
      <ul>'.
      '<li><a href="truc">tout le site</a></li>'.
      '<li><a href="truc">cette page</a></li>'.
      '</ul>'.
      '<input type="checkbox" name="clearcacheandreload" checked="checked" />... et recharger la page';

      return $ret;
     }
 }
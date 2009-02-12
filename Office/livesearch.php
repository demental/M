<?php
class M_Office_livesearch extends M_Office_Controller
{
    // @param   string the string to search for
    // @param   string (table for which to expand view)
    // Expand 
    
    function M_Office_livesearch($searchtext,$expandTable=null) {
        M_Office_Controller::M_Office_Controller();
        $this->searchtext=$searchtext;
        $this->expand=$expandTable!==null;
        $this->expandTable=$expandTable;
        M_Office::$dsp='__defaut/ajaxindex';
    }
    function processRequest() {
      $this->assign('__action','livesearch');
      $this->assign('searchText',$this->searchtext);
        $searchin=$this->expand?
            array($this->expandTable)
            :
            $this->getGlobalOption('searchInTables','frontendhome');  
        if(!is_array($searchin) || count($searchin)==0) {
            return '<p>Aucun domaine de recherche</p>';
        }
        $out=array();
        foreach($searchin as $table) {
            $obj = M_Office_Util::doForTable($table);

            if(method_exists($obj,'livesearch')) {
                $obj->livesearch($this->searchtext);  
                $out[$table]=$obj;
            }
        }
        foreach($out as $table=>$obj) {
            $ret.='<dl><dt>'.$table.'</dt>';
            $cnt=0;
            foreach($obj as $rec) {
                $ret.='<dd><a href="'.M_Office_Util::getQueryParams(array('module'=>$table,'record'=>$rec->id),array('livesearch')).'">'.$rec->livesearchText().'</a></dd>';
                $cnt++;
                if($cnt>10){break;}
            }
            if($cnt==0) {
              $ret.='<dd><em>Aucun résultat</em></dd>';
            }
            $ret.='</dl>';
        }
        $this->assign('output',$ret);
     }
 }
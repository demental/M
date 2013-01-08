<?php
/**
* M PHP Framework
* @package      M
* @subpackage   M_Office
*/
/**
* M PHP Framework
*
* Handles the autocomplete "spotlight-like" search field
*
* @package      M
* @subpackage   M_Office
* @author       Arnaud Sellenet <demental@sat2way.com>

* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class M_Office_livesearch extends M_Office_Controller
{
    // @param   string the string to search for
    // @param   string (table for which to expand view)
    // Expand

    function __construct($searchtext,$expandTable=null) {
        parent::__construct();
        $this->searchtext=trim($searchtext);
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
          return array('message' => __('No search domain'));
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
            $cnt=0;
            foreach($obj as $rec) {
              $ret[$table][] = array('url' => M_Office_Util::doURL($rec, $table, array(),array('livesearch', 'format')), 'text' => $rec->livesearchText());
              $cnt++;
              if($cnt>10){break;}
            }
            if($cnt==0) {
              $ret[$table] = array();
            }
        }

        $this->assign('output',$this->format($ret, $_GET['format']));
     }
     public function format($ret , $format)
     {
       if($format == 'json') return json_encode($ret);
       if($ret['message']) {
         return '<p>'.$ret['message'].'</p>';
       } else {

         foreach($ret as $table => $results) {
         $out.='<dl>';
           $out.= '<dt>'.$table.'</dt>';
           if(count($results) == 0) {
             $out .= '<dd><em>No results</em></dd>';
           } else {
             foreach($results as $info) {
               $out.= '<dd><a href="'.$info['url'].'">'.$info['text'].'</a></dd>';
             }
           }
         $out.='</dl>';
         }

       }
       return $out;
     }
 }
<?php

require_once('Structures/DataGrid/Column.php');

$GLOBALS['STRUCTURES_DATAGRID_LIMITCOLUMN_FORMATTERS'] = array();

class Structures_DataGrid_LimitColumn extends Structures_DataGrid_Column {
    function Structures_DataGrid_LimitColumn($columnName, $fieldName,
                                             $orderBy = null, $attribs = array(),
                                             $autoFillValue = null,
                                             $formatter = null,
                                             $length = 100) {
        if ($formatter !== null) {
            $GLOBALS['STRUCTURES_DATAGRID_LIMITCOLUMN_FORMATTERS'][md5($formatter)] = $formatter;
        }
        parent::Structures_DataGrid_Column($columnName, $fieldName, $orderBy, $attribs, $autoFillValue,
                                           get_class($this).'::limitLengthJS($field = '.$fieldName.',
                                                                             $length = '.$length.($formatter !== null ? ',
                                                                             $origFormatter = '.md5($formatter) : '').')');
    }

    function limitLengthJS($params) {
        static $firstCall = true;
        if (!isset($params['length'])) {
            $params['length'] = 100;
        }
        if ($firstCall) {
            $ret = '
<script language="javascript">
function expand(id) {
  if (contentLong[id]) {
    document.getElementById(id).innerHTML = shortContent[id];
  } else {
    document.getElementById(id).innerHTML = longContent[id];
  }
  contentLong[id] = !contentLong[id];
}
var longContent = new Array();
var shortContent = new Array();
var contentLong = new Array();
</script>
';
            $firstCall = false;
        } else {
            $ret = '';
        }
        if (isset($params['origFormatter'])) {
            $col = new Structures_DataGrid_Column('', null, null, null, null, $GLOBALS['STRUCTURES_DATAGRID_LIMITCOLUMN_FORMATTERS'][$params['origFormatter']]);
            $content = $col->formatter($params['record']);
        } else {
            $content = $params['record'][$params['field']];
        }
        if (strlen($content) > $params['length']) {
            list($usec, $sec) = explode(' ', microtime());
            $id = $params['field'].md5(rand(0, 1000).$sec.$usec);
            $shortContent = substr($content, 0, $params['length']).'...<span style="font-size: 70%;">Click to Expand</span>';
            $ret .= '
<script language="javascript">
longContent["'.$id.'"] = "'.str_replace('"', '\\"', str_replace(array("\r\n", "\r", "\n"), '\\n', $content)).'";
shortContent["'.$id.'"] = "'.str_replace('"', '\\"', str_replace(array("\r\n", "\r", "\n"), '\\n', $shortContent)).'";
contentLong["'.$id.'"] = false;
</script>
<div id="'.$id.'" onclick="expand(\''.$id.'\')">'.$shortContent.'</div>';
        } else {
            $ret .= $content;
        }
        return $ret;
    }

}

?>
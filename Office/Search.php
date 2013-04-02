<?php
/**
* M PHP Framework
* @package      M
* @subpackage   M_Office
*/
/**
* M PHP Framework
*
* Search helper for M_Office App
*
* @package      M
* @subpackage   M_Office
* @author       Arnaud Sellenet <arnaud AT sellenet POINT fr>
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

/**
* This object takes a DO as parameter in its constructor, then provides helper methods to ease search filters.
**/

class M_Office_Search {

	public function __construct(DB_DataObject $do, $values)
	{
		$this->do = $do;
		if(!is_array($values)) throw new Exception('Values must be an array !');
		$this->values = $values;
		$this->db = $do->getDatabaseConnection();
	}

	/**
	 * @param mixed ($arg1, [$arg2, $arg3 ....]) fields to filter
	 */
	public function filterDate($args)
	{
		$num = func_num_args();
		if($num > 1) {
			foreach(func_get_args() as $field) {
				$this->filterDate($field);
			}
			return;
		}
		$form_field = $args;
		if(!$this->values[$form_field]) return;
		$sql_field = $this->sanitize_field($form_field);
    $clause = '';
    $date1 = date('Y-m-d', strtotime($this->values[$form_field]['firstdate']));
    $date2 = date('Y-m-d', strtotime($this->values[$form_field]['seconddate']));
    $searchType = $this->values[$form_field]['firstselect'];

	  switch($searchType){
			case "lastmonth":     $clause = $sql_field . " like '" . date('Y-m',time()-2592000)."-%'";break;
			case "currentmonth":  $clause = $sql_field . " like '" . date('Y-m',time())."-%'";break;
			case "is":            $clause = $sql_field . " like '" . $date1 . "%'";break;
			case "after":         $clause = $sql_field . " >= '". $date1 . "'";break;
			case "before":        $clause = $sql_field . " <= '". $date1 . "'";break;
			case "between":       $clause = $sql_field . " >= '". $date1 . "' AND ".$sql_field . " <= '" . $date2 . "'";break;
    }
		if(!empty($clause)) $this->do->whereAdd($clause);
	}
	public function searchInFields($form_field, $search_in)
	{
	  if(!$this->values[$form_field]) return;
    $clause = '';
    $sql_value = $this->db->quote('%'.$this->values[$form_field].'%');
    array_map($search_in, array($this, 'sanitize_field'));
    foreach ($search_in as $field) {
      $clause .= "$aj$field like $sql_value";
      $aj = ' OR ';
    }
    $this->do->whereAdd($clause);
  }
  protected function sanitize_field($field)
  {
  	return $this->db->quoteIdentifier($this->do->tableName()). '.' . $this->db->quoteIdentifier($field);
  }
}
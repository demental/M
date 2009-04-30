<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 * Interface for storing custom queries of exporter Plugin.
 * The DAO linked to the table that will store these queries will have to implement this interface
 */

interface iQueryStorable {
  /**
   * returns an associative array to populate a <SELECT> html element. The keys should be the database ID and the value the name of the stored query
   * @param string table name upon which queries are associated
   * @return array key/value pairs
   **/
  public function getKeyValuePairs($table);
  /**
   * retrieves a stored query by its ID
   * @param string ID of the stored query record
   * @return string SQL query
   **/ 
  public function getQueryByID($id);
  /**
   * stores a query against a table and gives it a label
   * @param $query string SQK query to store
   * @param $table main table of the query
   * @param $label human-readable name for the query
   */
  public function store($query, $table, $label);
}
 
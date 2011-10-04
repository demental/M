<?php 
/**
 * Inspired by factory_girl
 */

class DAO {
	/**
	 * Gets a built DO object , saves it and returns it
	 * @param string table name
	 * @param array (optional) array of key/values to override default faketory
	 * @return DB_DataObject_Pluggable
	 */
	public static function faketory($tablename,$overridefields = array())
	{
		$do = self::build($tablename,$overridefields);
		$do->insert();
		return $do;
	}
	/**
	 * returns a built DO object, not saved (except linked records that need to)
	 * use values that are defined in project/faketories/...
	 * @param string table name
	 * @param array (optional) array of key/values to override default faketory
	 * @return DB_DataObject_Pluggable	
	 */

	public static function build($tablename,$overridefields = array())
	{
		$do = DB_DataObject::factory($tablename);
		require APP_ROOT.PROJECT_NAME.'faketories/'.ucfirst($tablename).'.php';
		foreach($overridefields as $k=>$v) {
			if($v instanceOf DB_DataObject_Pluggable) {
				$do->setLinkObj($v,$k);
			} else {
				$do->$k = $v;
			}
		}
		return $do;
	}
}
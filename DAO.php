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
	 * Returns a DO object.
	 * if the object containing the overridefields values is found in the database then it is returned.
	 * Otherwise a new fake object is created.
	 * unlike faketory, $overridefields is mandatory
	 * @param string table name
	 * @param string key/value pairs for search criteria
	 */
	public function faketory_or_find($tablename,$overridefields)
	{
		$do = DB_DataObject::factory($tablename);
		$do->setFrom($overridefields);
		if($do->find(true)) return $do;
		return self::faketory($tablename,$overridefields);
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
		extract($overridefields);
		require APP_ROOT.PROJECT_NAME.'/tests/Faketories/'.ucfirst($tablename).'.php';

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
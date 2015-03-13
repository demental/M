<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   log.php
 * @author       Arnaud Sellenet <demental at github>
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 * allows to log inserts and updates
 */

class Plugins_Log_DB extends M_Plugin
{
  protected static $username;
  protected static $logger;
  public function getEvents()
  {
    return array('log','getlogger');
  }

  public function log($message, $messagecode, DB_DataObject $obj)
  {
    self::getLogger()->info(vsprintf('[%1$s %2$s %5$s %3$s] %3$s %4$s %1$s ID %2$s',array($obj->tableName(),$obj->pk(),self::getUsername(),$message,$messagecode)));
  }
  public static function getUsername()
  {
    if(!self::$username) {
      foreach(User::getInstances() as $instance) {
        if($instance->isLoggedIn()) {
          self::$username = $instance->getDBDO()->__toString();
        }
      }
      if(!self::$username) {
        self::$username='Anonymous';
      }
    }
    return User::getInstance('office')->getDBDO()->__toString();
  }
  public function getLogger($obj = null)
  {
    if(!self::$logger) {
      self::$logger = Log::getInstance();
    }
    if($obj instanceOf DB_DataObject) {
      return self::returnStatus(self::$logger);
    }
    return self::$logger;
  }
  public function setLogger($logger)
  {
    self::$logger = $logger;
  }
}

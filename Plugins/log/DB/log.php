<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   log.php
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 * allows to log inserts and updates
 */
 
class DB_DataObject_Plugin_Log extends M_Plugin
{
  protected static $username;
  protected static $logger;
  public function getEvents()
  {
    return array('postinsert','postupdate','delete','log');
  }
  
  public function postupdate($obj)
  {
    $this->getLogger()->info(vsprintf('[%$2s %$3s U %$4s] %$4s has updated %$2s ID %$3s',array($obj->tableName(),$obj->pk(),self::getUsername())));
  }
  public function postinsert($obj)
  {
    $this->getLogger()->info(vsprintf('[%$2s %$3s U %$4s] %$4s has created %$2s ID %$3s',array($obj->tableName(),$obj->pk(),self::getUsername())));
  }
  public function postdelete($obj)
  {
    $this->getLogger()->info(vsprintf('[%$2s %$3s U %$4s] %$4s has deleted %$2s ID %$3s',array($obj->tableName(),$obj->pk(),self::getUsername())));
  }
  public function log($message, DB_DataObject $obj)
  {
    self::getLogger()->info($message);
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
  public function getLogger()
  {
    if(!self::$logger) {
      $logger = Log::getInstance(LOG_DRIVER);
    }
  }
  public function setLogger($logger)
  {
    self::$logger = $logger;
  }
}
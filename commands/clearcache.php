<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   clearcache.php
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 * command to clear project cache
 */


class Command_clearcache extends Command
{
  public function shortHelp()
  {
    $this->line('Clears project caches');
  }
  public function longHelp($params)
  {
    $this->line('Clears project caches');
    $this->line('Either one or both of these parameters can be passed :');
    $this->line('html: removes all caches in each app cache dir');
    $this->line('config: removes all caches in each app cache/config dir');    
    $this->line('assets: removes all caches in documentRoot cache dir');
    $this->line('all: removes html, config and assets cache');
  }
  public function execute($params)
  {
    foreach($params as $par) {
      $fun = 'clear'.$par.'cache';
      if(method_exists($this,$fun)) {
        call_user_func(array($this,$fun));
      }
    }
  }
  public function clearAllCache()
  {
    $this->clearAssetsCache();
    $this->clearConfigCache();    
    $this->clearHtmlCache();    
  }
  public function clearAssetsCache()
  {
      $this->_emptyfolder(APP_ROOT.WEB_FOLDER.'/cache',false);
  }
  public function clearConfigCache()
  {
    $root = APP_ROOT.PROJECT_NAME.'/';
    foreach(FileUtils::getFolders($root,'',false) as $folder) {
      $this->_emptyfolder($root.$folder.'/cache/config',false);
    }
  }
  public function clearHtmlcache()
  {
    $root = APP_ROOT.PROJECT_NAME.'/';
    foreach(FileUtils::getFolders($root,'',false) as $folder) {
      $this->_emptyfolder($root.$folder.'/cache',false);
    }
  }
  protected function _emptyfolder($cacheFolder,$recursive)
  {
    if(is_dir($cacheFolder)) {
      $nbfiles = 0;
      foreach(FileUtils::getAllFiles($cacheFolder,'',false) as $file) {
        if(is_file($file)) {
          unlink($file);
          $nbfiles++;
        }
      }
      $this->line('Clearing dir '.$cacheFolder);
      $this->line(' ....... ['.$nbfiles.' files]');
      
    }
  }
}

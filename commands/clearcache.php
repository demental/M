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
    $this->line('No parameters eligible for now');
  }
  public function execute($params)
  {
    $root = APP_ROOT.PROJECT_NAME.'/';
    foreach(FileUtils::getFolders($root) as $folder) {
      $cacheFolder = $root.$folder.'/cache';
      if(is_dir($cacheFolder)) {
   //     $this->line('Clearing cache folder '.$folder.'/cache/');
        foreach(FileUtils::getAll($cacheFolder) as $file) {
          $this->line($file);
//          $this->line('removing '.$file);
          if(is_file($file)) {
            unlink($file);
          }
        }
      }
    }
    
  }
}

<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   clearcache.php
 * @author       Arnaud Sellenet <demental@sat2way.com>
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
    $this->line('assets: removes all caches FILES in documentRoot cache dir');
    $this->line('web: removes all caches FILES AND FOLDERS in documentRoot cache and cache dir');
    $this->line('all: removes html, config and assets cache');
  }
  public function execute($params = array())
  {
    if(count($params)==0) {
      $this->clearAllCache();
    }
    foreach($params as $par) {
      $fun = 'clear'.$par.'cache';
      if(method_exists($this,$fun)) {
        call_user_func(array($this,$fun));
      }
    }
  }
  public function clearAllCache()
  {
    $this->clearConfigCache();
    $this->clearHtmlCache();
    $this->clearWebCache();
  }
  public function clearAssetsCache()
  {
    $this->_emptyfolder(APP_ROOT.'public/cache',false);
    $this->_regenerateAssets();
  }
  public function clearWebCache()
  {
    $this->_emptyfolder(APP_ROOT.'public/cache',true);
    $this->_regenerateAssets();
  }
  public function clearConfigCache()
  {
    $root = APP_ROOT.'app/';
    foreach(FileUtils::getFolders($root,'',false) as $folder) {
      $this->_emptyfolder($root.$folder.'/cache/config',false);
    }
  }
  public function clearHtmlcache()
  {
    $root = APP_ROOT.'app/';
    foreach(FileUtils::getFolders($root,'',false) as $folder) {
      $this->_emptyfolder($root.$folder.'/cache',false);
    }
  }
  protected function _emptyfolder($cacheFolder,$recursive, &$nbfiles=0, $silent=false)
  {
    if(is_dir($cacheFolder)) {
      foreach(FileUtils::getAll($cacheFolder,'',false) as $file) {

        if(is_file($file)) {
          unlink($file);
          $nbfiles++;
        } elseif(is_dir($file) && $recursive) {
          $this->_emptyfolder($file,true,$nbfiles,true);
          rmdir($file);
        }
      }
      if(!$silent) {
        $this->line('Clearing dir '.$cacheFolder);
        $this->line(' ....... ['.$nbfiles.' files]');
      }
    }
  }
  protected function _regenerateAssets()
  {
    $this->header('Regenerating assets');
    $version_file = APP_ROOT.'app/ASSETSVERSION';
    $assetsversion = (int)file_get_contents($version_file);
    $assetsversion++;
    file_put_contents($version_file, $assetsversion);
    $assetsfolder = APP_ROOT.'public/assets/';
    $jsfolder = $assetsfolder.'js/';
    foreach(FileUtils::getFolders($jsfolder) as $folder) {
      if(preg_match('`^\.`',$folder)) continue;
      $this->line('Regenerating '.$folder.' javascript asset');
      $out='';
      foreach(FileUtils::getAllFiles($jsfolder.$folder) as $file) {
        $out.=file_get_contents($file)."\n";
      }
      if(MODE=='production') {
        $out = JSmin::minify($out);
      }
      $version = (self::getOption('assetsurlrewriting')) ? '' : $assetsversion;
      file_put_contents(APP_ROOT.'public/cache/'.$folder.$version.'.js',$out);
    }
    // css

    $cssfolder = $assetsfolder.'css/';
    foreach(FileUtils::getFolders($cssfolder) as $folder) {
      if(preg_match('`^\.`',$folder)) continue;
      $this->line('Regenerating '.$folder.' CSS asset');
      $out='';
      foreach(FileUtils::getAllFiles($cssfolder.$folder) as $file) {
        $out.=file_get_contents($file)."\n";
      }
      if(MODE=='production') {
        $out = CSSmin::minify($out);
      }
      $version = (self::getOption('assetsurlrewriting')) ? '' : $assetsversion;
      file_put_contents(APP_ROOT.'public/cache/'.$folder.$version.'.css',$out);
    }
  }
}

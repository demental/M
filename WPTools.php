<?php
require_once 'Zend/XmlRpc/Client.php';
/**
 * Wordpress integration into the M framework
 * Requires that yuo install the extendedapi plugin into wordpress.
 * You also need to activate the following :
 * * get_page_by_path
 * * do_shortcode
 * * wpautop
 */
class WPTools {
  protected static $_inited  = false;
  protected static $_settings = array(
    'wp_filepath'=>''
  );
  public static $namespace = 'extapi';
  public static $wp_root;
  public static $wp_login;
  public static $wp_password;
  public function getPage($pageID, $apply_shortcodes = false)
  {
    $options = array(
        'caching' =>true,
        'cacheDir' => APP_ROOT.PROJECT_NAME.'/'.APP_NAME.'/cache/',
        'lifeTime' => 3600,
        'fileNameProtection'=>true,
		);

		$cache = new Cache_Lite($options);
    $cacheName = 'wptool'.Config::get('wp_root').$pageID.'_'.$apply_shortcodes;
		if($_cachedData = $cache->get($cacheName)) {
      return unserialize($_cachedData);
    }

    $client = new Zend_XmlRpc_Client(self::$wp_root.'/xmlrpc.php');
    $c = (object)$client->getProxy(self::$namespace)->callWpMethod(self::$wp_login, self::$wp_password,'get_page_by_path', array($pageID));
    $c->post_content = $client->getProxy(self::$namespace)->callWpMethod(self::$wp_login, self::$wp_password, 'wpautop', array($c->post_content));
    if($apply_shortcodes) {
      $c->post_content = $client->getProxy(self::$namespace)->callWpMethod(self::$wp_login, self::$wp_password,'do_shortcode', array($c->post_content));
    }
    $cache->save(serialize($c));
    return $c;
  }
}

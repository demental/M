<?php
/**
* M PHP Framework
* @package      M
* @subpackage   skeleton
*/
/**
* M PHP Framework
*
* This file includes a M_Setup class that fires configuration data that occurs after cache (typically ).
* Modify the setUpEnv() method to fit your needs
*
* @package      M
* @subpackage   skeleton
* @author       Arnaud Sellenet <demental at github>

* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class M_Setup implements iSetup {
  public function setUpEnv()
  {
    $this->setUpDatabase();
    define('WEBROOT_FOLDER', APP_ROOT.'public/');

    // ================================
    // = Absolute path to media files =
    // ================================
    define('IMAGES_UPLOAD_FOLDER', APP_ROOT.'public/media/');

    // ===============================
    // = relative uri to media files =
    // ===============================
    define('WWW_IMAGES_FOLDER', 'media/');
    define('UPLOAD_AUTHORIZED_EXTENSIONS',"jpg,jpeg");

    // ==============================
    // = log file & global encoding =
    // ==============================
    $cfgArr['logfile'] = APP_ROOT.'app.log';
    $cfgArr['encoding'] = 'utf-8';

    // ======================
    // = i18n configuration =
    // ======================
    $cfgArr['translate_db'] = 'translate'; // used by DB_DataObject_Plugin_International (deprecated @see M/DB/DataObject/Plugin/International.php for more details)
    $cfgArr['installedLangs'] = array('fr','en');
    $cfgArr['defaultLang']='fr';
    Config::load($cfgArr);



    // ==============================
    // = Mail configuration options =
    // ==============================
    $mailopt = &PEAR::getStaticProperty('Mail', 'global');
    $mailopt['template_dir'] = array(
      APP_ROOT.'app/_shared/templates/_mails/',
      APP_ROOT.'app/'.APP_NAME.'/templates/_mails/');
    $mailopt['encoding']='utf-8';
    $mailopt['drivers'] = explode(',',CONFIG_MAILDRIVERS);    
    $mailopt['log_folder']=APP_ROOT.'mail_logs/';
    $mailopt['from']='noreply@mymaildomain.com';
    $mailopt['fromname']='Support';

    // =============================
    // = Pdf configuration options =
    // =============================
    $pdfopt = &PEAR::getStaticProperty('MPdf', 'global');
    $pdfopt['template_dir'] = APP_ROOT.'/_shared/templates/_pdf/';
  }
  public function setUpDatabase()
  {
     /**
     * DB_DataObject configuration
     *
     */

    $options = & PEAR :: getStaticProperty('DB_DataObject', 'options');
    $options = array (  'database' => DB_URI,
                        'schema_location' => APP_ROOT.'app/models/',
                        'class_location' => APP_ROOT.'app/models/',
                        'require_prefix' => 'DataObjects/',
                        'class_prefix' => 'DataObjects_',
                        'debug' => key_exists('debug',$_GET)?1:0,
                        'extends' =>'DB_DataObject_Pluggable',
                        'extends_location'=>'M/DB/DataObject/Pluggable.php',
                        'db_driver'=>'MDB2',
                        'quote_identifiers'=>true,
                        'generator_no_ini'=>true,
                    );
    $db_options = & PEAR::getStaticProperty('MDB2','options');
    $db_options['portability']=MDB2_PORTABILITY_ALL ^ MDB2_PORTABILITY_FIX_CASE;

  }
}

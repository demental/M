<?php
/**
* M PHP Framework
* @package      M
* @subpackage   DB_DataObject_Advgenerator
*/
/**
* M PHP Framework
*
* DB_DataObject_Generator extension,
* Main goal is to generate the links() method instead of using the parent one which parses the .links.ini file
*  = better readability as you can see the links from the table you're working on
*  = better performance as no ini parsing is necessary anymore
* Also creates a reverseLinks() method for reverse links to increase performance in the "related records" block
* in M_Office_EditRecord()
*
* @package      M
* @subpackage   DB_DataObject_Advgenerator
* @author       Arnaud Sellenet <demental@sat2way.com>

* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

require_once 'DB/DataObject/Generator.php';

class DB_DataObject_Advgenerator extends DB_DataObject_Generator {
    // ===============================================================
    // = Adds links() and reverselinks() methods into generated code
    // = if not present outside autogenerated input
    // = prevents DB_DataObject from parsing links.ini file each time a new object is factored
    // = @param string $input already present input
    // = @return string generated methods
    // ===============================================================
    public static function hasCustomLinksMethod($input) {
      return empty($input)?false:eregi('function links *\(.+###END_AUTOCODE',$input)?false:true;
    }
    function derivedHookFunctions($input = "")
    {
      $addlinks = !self::hasCustomLinksMethod($input);
      $addreverselinks = empty($input)?true:eregi('function reverseLinks *\(.+###END_AUTOCODE',$input)?true:false;

        if($addlinks) {
          $l = $this->_getIniLinks($this->table);
          $links="\n";
          if(is_array($l)) {
            foreach($l as $from=>$to) {
                $links.="\t\t\t'$from'=>'$to',\n";
            }
            // This is for i18n plugin. Auto-generate link with the i18n table even if not present in the links.ini
            if(ereg('^(.+)_i18n$',$this->table,$tab)) {
              $links.="      'i18n_record_id'=>'".$tab[1].":id',\n";
            }
            elseif(ereg('^(.+)_l10n$',$this->table,$tab)) {
              $links.="      'l10n_record_id'=>'".$tab[1].":id',\n";
            }

          }
        }
        if($addreverselinks) {
          $r = $this->_getReverseIniLinks($this->table);
          $reverselinks = "\n";
          if(is_array($r)) {
            foreach($r as $rlink=>$field) {
                $reverselinks.="      '$rlink'=>'$field',\n";
            }
          }
        }
        return '
        '.($addlinks?'
    function links() {
      // links generated from .links.ini file
      return array('.$links.'
      );
    }':'').
    ($addreverselinks?'
    function reverseLinks() {
      // reverseLinks generated from .links.ini file
      return array('.$reverselinks.'
      );
    }':'');
    }
    // =============================
    // = Creates reverseLinks array
    // = @param string $table table name
    // = @return array reverselinks
    // =============================
    function _getReverseIniLinks($table) {
            $out = array();
            global $_DB_DATAOBJECT;
            $lks = $_DB_DATAOBJECT['LINKS'][$this->_database];
            if(!is_array($lks)) {
              return $out;
            }
            foreach($lks as $aTable=>$links) {
                foreach($links as $field=>$link) {
                    $linkSchema = explode(':',$link);
                    if($linkSchema[0]==$table) {
                        $out[$aTable.':'.$field] = $linkSchema[1];
                    }
                }
            }
            return $out;
    }
    // =============================
    // = Creates links array
    // = @param string $table table name
    // = @return array links
    // =============================

    function _getIniLinks($table) {
        global $_DB_DATAOBJECT;

        $databaseIdentifier = 'database';
        if(key_exists('table_'.$table,$_DB_DATAOBJECT['CONFIG'])) {
          $databaseIdentifier = 'database_'.$_DB_DATAOBJECT['CONFIG']['table_'.$table];
        }
//        $databaseIdentifier = 'database'.($this->_database?'_'.$this->_database:'');
        if (!isset($_DB_DATAOBJECT['LINKS'][$this->_database])) {
            $schemas = isset($_DB_DATAOBJECT['CONFIG']['schema_location']) ?
                array("{$_DB_DATAOBJECT['CONFIG']['schema_location']}/{$databaseIdentifier}.ini") :
                array() ;

            if (isset($_DB_DATAOBJECT['CONFIG']["ini_{$this->_database}"])) {
                $schemas = is_array($_DB_DATAOBJECT['CONFIG']["ini_{$databaseIdentifier}"]) ?
                    $_DB_DATAOBJECT['CONFIG']["ini_{$this->_database}"] :
                    explode(PATH_SEPARATOR,$_DB_DATAOBJECT['CONFIG']["ini_{$databaseIdentifier}"]);
            }



            foreach ($schemas as $ini) {

                $links =
                    isset($_DB_DATAOBJECT['CONFIG']["links_{$databaseIdentifier}"]) ?
                        $_DB_DATAOBJECT['CONFIG']["links_{$databaseIdentifier}"] :
                        str_replace('.ini','.links.ini',$ini);

                if (empty($_DB_DATAOBJECT['LINKS'][$this->_database]) && file_exists($links) && is_file($links)) {
                    /* not sure why $links = ... here  - TODO check if that works */
                    $_DB_DATAOBJECT['LINKS'][$this->_database] = parse_ini_file($links, true);
                    if (!empty($_DB_DATAOBJECT['CONFIG']['debug'])) {
                        $this->debug("Loaded links.ini file: $links","links",1);
                    }
                } else {
                    if (!empty($_DB_DATAOBJECT['CONFIG']['debug'])) {
                        $this->debug("Missing links.ini file: $links","links",1);
                    }
                }
            }
        }


        // if there is no link data at all on the file!
        // we return null.
        if (!isset($_DB_DATAOBJECT['LINKS'][$this->_database])) {

            return '';
        }

        if (isset($_DB_DATAOBJECT['LINKS'][$this->_database][$table])) {

            return $_DB_DATAOBJECT['LINKS'][$this->_database][$table];
        }

        return array();

    }
    /**
     * start()
     * Override original DB/DataObject/Generator.php start() method moving database routing to another method.
     * This allows to use the generator just for retreiving tables used in the project :
     *
     */
    function start()
    {

        $generators = $this->getGenerators();
        $class = get_class($this);
        foreach($generators as $t) {
            foreach(get_class_methods($class) as $method) {
                if (substr($method,0,8 ) != 'generate') {
                    continue;
                }
                $this->debug("calling $method");
                $t->$method();
            }
        }
        $this->debug("DONE\n\n");
    }
    /**
     * generates an array of generators, one for each database in the project
     * @return array of DB_DataObject_Advgenerators
     */
    public function getGenerators()
    {
      $options = &PEAR::getStaticProperty('DB_DataObject','options');
      $db_driver = empty($options['db_driver']) ? 'DB' : $options['db_driver'];
      $databases = array();
      foreach($options as $k=>$v) {
          if (substr($k,0,9) == 'database_') {
              $databases[substr($k,9)] = $v;
          }
      }

      if (isset($options['database'])) {
          if ($db_driver == 'DB') {
              require_once 'DB.php';
              $dsn = DB::parseDSN($options['database']);
          } else {
              require_once 'MDB2.php';
              $dsn = MDB2::parseDSN($options['database']);
          }

          if (!isset($database[$dsn['database']])) {
              $databases[$dsn['database']] = $options['database'];
          }
      }

      $output = array();
      foreach($databases as $databasename => $database) {
          if (!$database) {
              continue;
          }
          $output[] = $this->factory($database,$databasename,$db_driver);
      }
      return $output;
    }
    /**
    * factory()
    * @access public
    * @return DB_DataObject_Advgenerator instance including tables list for $databasename
    **/
    public function factory($database,$databasename,$db_driver)
    {
      $this->debug("CREATING FOR $databasename\n");
      $class = get_class($this);
      $t = new $class;
      $t->_database_dsn = $database;


      $t->_database = $databasename;
      if ($db_driver == 'DB') {
          require_once 'DB.php';
          $dsn = DB::parseDSN($database);
      } else {
          require_once 'MDB2.php';
          $dsn = MDB2::parseDSN($database);
      }

      if (($dsn['phptype'] == 'sqlite') && is_file($databasename)) {
          $t->_database = basename($t->_database);
      }
      $t->_createTableList();
      return $t;
    }

	/**
  * Origin override
  */
  function _generateClassTable($input = '')
  {
      // title = expand me!
      $foot = "";
      $head = "<?php\n/**\n * Table Definition for {$this->table}\n";
      $head .= $this->derivedHookPageLevelDocBlock();
      $head .= " */\n";
      $head .= $this->derivedHookExtendsDocBlock();


      // requires
      $head .= "require_once '{$this->_extendsFile}';\n\n";
      // add dummy class header in...
      // class
      $head .= $this->derivedHookClassDocBlock();
      $head .= "class {$this->classname} extends {$this->_extends} \n{";

      $body =  "\n    ###START_AUTOCODE\n";
      $body .= "    /* the code below is auto generated do not remove the above tag */\n\n";
      // table

      $p = str_repeat(' ',max(2, (18 - strlen($this->table)))) ;

      $options = &PEAR::getStaticProperty('DB_DataObject','options');


      $var = (substr(phpversion(),0,1) > 4) ? 'public' : 'var';
      $var = !empty($options['generator_var_keyword']) ? $options['generator_var_keyword'] : $var;


      $body .= "    {$var} \$__table = '{$this->table}';  {$p}// table name\n";


      // if we are using the option database_{databasename} = dsn
      // then we should add var $_database = here
      // as database names may not always match..

      if (empty($GLOBALS['_DB_DATAOBJECT']['CONFIG'])) {
          DB_DataObject::_loadConfig();
      }

       // Only include the $_database property if the omit_database_var is unset or false

      if (isset($options["database_{$this->_database}"]) && empty($GLOBALS['_DB_DATAOBJECT']['CONFIG']['generator_omit_database_var'])) {
          $p = str_repeat(' ',   max(2, (16 - strlen($this->table))));
          $body .= "    {$var} \$_database = '{$this->_database}';  {$p}// database name (used with database_{*} config)\n";
      }


      if (!empty($options['generator_novars'])) {
          $var = '//'.$var;
      }

      $defs = $this->_definitions[$this->table];

      // show nice information!
      $connections = array();
      $sets = array();

      foreach($defs as $t) {
          if (!strlen(trim($t->name))) {
              continue;
          }
          if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $t->name)) {
              echo "*****************************************************************\n".
                   "**               WARNING COLUMN NAME UNUSABLE                  **\n".
                   "** Found column '{$t->name}', of type  '{$t->type}'            **\n".
                   "** Since this column name can't be converted to a php variable **\n".
                   "** name, and the whole idea of mapping would result in a mess  **\n".
                   "** This column has been ignored...                             **\n".
                   "*****************************************************************\n";
              continue;
          }

          $p = str_repeat(' ',max(2,  (30 - strlen($t->name))));

          $length = empty($t->len) ? '' : '('.$t->len.')';
          $body .="    {$var} \${$t->name};  {$p}// {$t->type}$length".($t->flags?"  {$t->flags}":"")."\n";

          // can not do set as PEAR::DB table info doesnt support it.
          //if (substr($t->Type,0,3) == "set")
          //    $sets[$t->Field] = "array".substr($t->Type,3);
          $body .= $this->derivedHookVar($t,strlen($p));
      }

      $body .= $this->derivedHookPostVar($defs);

      // THIS IS TOTALLY BORKED old FC creation
      // IT WILL BE REMOVED!!!!! in DataObjects 1.6
      // grep -r __clone * to find all it's uses
      // and replace them with $x = clone($y);
      // due to the change in the PHP5 clone design.

      if ( substr(phpversion(),0,1) < 5) {
          $body .= "\n";
          $body .= "    /* ZE2 compatibility trick*/\n";
          $body .= "    function __clone() { return \$this;}\n";
      }

      // simple creation tools ! (static stuff!)
      $body .= "\n";
      $body .= "    /* Static get */\n";
      $body .= "    function staticGet(\$k,\$v=NULL) { return DB_DataObject::staticGet('{$this->classname}',\$k,\$v); }\n";

      // generate getter and setter methods
      $body .= $this->_generateGetters($input);
      $body .= $this->_generateSetters($input);

      /*
      theoretically there is scope here to introduce 'list' methods
      based up 'xxxx_up' column!!! for heiracitcal trees..
      */

      // set methods
      //foreach ($sets as $k=>$v) {
      //    $kk = strtoupper($k);
      //    $body .="    function getSets{$k}() { return {$v}; }\n";
      //}

      if (!empty($options['generator_no_ini'])) {
          $def = $this->_generateDefinitionsTable();  // simplify this!?
          $body .= $this->_generateTableFunction($def['table']);
          $body .= $this->_generateKeysFunction($def['keys']);
          $body .= $this->_generateSequenceKeyFunction($def);
          $body .= $this->_generateDefaultsFunction($this->table, $def['table']);
      }  else if (!empty($options['generator_add_defaults'])) {
          // I dont really like doing it this way (adding another option)
          // but it helps on older projects.
          $def = $this->_generateDefinitionsTable();  // simplify this!?
          $body .= $this->_generateDefaultsFunction($this->table,$def['table']);

      }
      $body .= $this->derivedHookFunctions($input);

      $body .= "\n    /* the code above is auto generated do not remove the tag below */";
      $body .= "\n    ###END_AUTOCODE\n";


      // stubs..

      if (!empty($options['generator_add_validate_stubs'])) {
          foreach($defs as $t) {
              if (!strlen(trim($t->name))) {
                  continue;
              }
              $validate_fname = 'validate' . $this->getMethodNameFromColumnName($t->name);
              // dont re-add it..
              if (preg_match('/\s+function\s+' . $validate_fname . '\s*\(/i', $input)) {
                  continue;
              }
              $body .= "\n    function {$validate_fname}()\n    {\n        return false;\n    }\n";
          }
      }




      $foot .= "}\n";
      $full = $head . $body . $foot;

      if (!$input) {
          return $full;
      }
      if (!preg_match('/(\n|\r\n)\s*###START_AUTOCODE(\n|\r\n)/s',$input))  {
          return $full;
      }
      if (!preg_match('/(\n|\r\n)\s*###END_AUTOCODE(\n|\r\n)/s',$input)) {
          return $full;
      }


      /* this will only replace extends DB_DataObject by default,
          unless use set generator_class_rewrite to ANY or a name*/

      $class_rewrite = 'DB_DataObject';
      $options = &PEAR::getStaticProperty('DB_DataObject','options');
      if (empty($options['generator_class_rewrite']) || !($class_rewrite = $options['generator_class_rewrite'])) {
          $class_rewrite = 'DB_DataObject';
      }
      if ($class_rewrite == 'ANY') {
          $class_rewrite = '[a-z_]+';
      }

      $input = preg_replace(
          '/(\n|\r\n)class\s*[a-z0-9_]+\s*extends\s*' .$class_rewrite . '\s*(\n|\r\n)\{(\n|\r\n)/si',
          "\nclass {$this->classname} extends {$this->_extends} \n{\n",
          $input);

      $ret =  preg_replace(
          '/(\n|\r\n)\s*###START_AUTOCODE(\n|\r\n).*(\n|\r\n)\s*###END_AUTOCODE(\n|\r\n)/s',
          $body,$input);

      if (!strlen($ret)) {
          return PEAR::raiseError(
              "PREG_REPLACE failed to replace body, - you probably need to set these in your php.ini\n".
              "pcre.backtrack_limit=1000000\n".
              "pcre.recursion_limit=1000000\n"
              ,null, PEAR_ERROR_DIE);
     }

      return $ret;
  }

}
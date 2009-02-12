<?php
// ===========================================================
// = This is a registry class (@see registry design pattern) =
// ===========================================================
class Mreg
{
        static private $_registry = array ();
 
        /**
         * Enregistre un objet dans le registre
         *
         * @param string $key
         * @param objet $obj
         */
        static private function forceSet ($key, $obj)
        {
          self::$_registry[$key] = $obj;
        }
        static public function set ($key, $obj)
        {
                // Verifie les doublons de clefs
                if (array_key_exists ($key, self::$_registry)) {
                        throw new Exception ("{$key} alerady exists");
                }
               
                // Verifie s’il s’agit bien d’un objet
                if (!is_object ($obj) && !is_array($obj)) {
                        throw new Exception ("Only objects or arrays can be registered");
                }
               
                // pas de probleme, on enregistre
                self::$_registry[$key] = $obj;
        }
       
        /**
         * Restaure un objet enregistré
         *
         * @param string $key
         * @return objet
         */
        static public function &get ($key)
        {
                if (!array_key_exists ($key, self::$_registry)) {
                        throw new Exception ("no {$key} object");
                }
               
                return self::$_registry[$key];
        }
        static public function append($key,$array)
        {
          if(!is_array($array)) {
            throw new Exception ("Mreg can only append arrays (tried to append a non-array to {$key})");            
          }
          try {
            $res = self::get($key);
          } catch(Exception $e) {
            $res = array();
          }
          if(!is_array($res)) {
            throw new Exception ("registered element {$key} is not an array. Cannot append");
          }
          $res = array_merge($res,$array);
          try {
            self::forceSet($key,$res);
          } catch(Exception $e) {
            // do nothing more
          }
        }
}
<?php

// ===============================
// = nested tree handling plugin =
// = The table needs the following fields :
// = parent_id, left, right, level (depth)
// = allows fast fetching across multiple levels, while table manipulation (insert - update - delete) is much slower
// = accurate for big trees, not for small ones (eg categories with 2/3 levels)
// ===============================

require_once 'M/DB/DataObject/Plugin.php';
class DB_DataObject_Plugin_tree extends DB_DataObject_Plugin
{
        public $plugin_name='tree';
        private $_doRebuild = true;
        /**
         * DB_DataObject overloads
         **/
         function postinsert(&$obj) {
             $this->rebuild($obj);
         }
         function insert(&$obj) {
            if(!$obj->{$obj->treeFields['parent']}) {
                $obj->{$obj->treeFields['parent']} = 0;
            }
        }
         function update(&$obj) {
            if(!$obj->{$obj->treeFields['parent']}) {
                $obj->{$obj->treeFields['parent']} = 0;
            }
        }
         function postupdate(&$obj) {
             $this->rebuild($obj);
         }
         function postdelete(&$obj) {
            /*
             $desc = $this->getDescendants($obj,$obj->id);
             foreach($desc as $enfant) {
                 $enfant->delete();
             }*/
             $this->rebuild($obj);             
         }
        /**
         * A utility function to return an array of the fields
         * that need to be selected in SQL select queries
         *
         * @return  array   An indexed array of fields to select
         */
        function _getFields(&$obj)
        {
            return array('id', $obj->treeFields['parent'], $obj->treeFields['sort'],
                         $obj->treeFields['left'], $obj->treeFields['right'], $obj->treeFields['level']);
        }
 
        /**
         * Fetch the node data for the node identified by $id
         *
         * @param   int     $id     The ID of the node to fetch
         * @return  object          An object containing the node's
         *                          data, or null if node not found
         */
        function getNode(&$obj, $id)
        {
            $res = DB_DataObject::factory($obj->tableName());
            $res->selectAdd();
            $res->selectAdd(join(',', $this->_getFields($obj)));
            if($res->get($id)) {
                return $res;
            }
            return null;
        }
 
        /**
         * Fetch the descendants of a node, or if no node is specified, fetch the
         * entire tree. Optionally, only return child data instead of all descendant
         * data.
         *
         * @param   int     $id             The ID of the node to fetch descendant data for.
         *                                  Specify an invalid ID (e.g. 0) to retrieve all data.
         * @param   bool    $includeSelf    Whether or not to include the passed node in the
         *                                  the results. This has no meaning if fetching entire tree.
         * @param   bool    $childrenOnly   True if only returning children data. False if
         *                                  returning all descendant data
         * @return  array                   The descendants of the passed now
         */
        function &getDescendants(&$obj, $id = 0, $includeSelf = false, $childrenOnly = false)
        {

 
            $node = $this->getNode($obj, $id);
            if (is_null($node)) {
                $nleft = 0;
                $nright = 0;
                $parent_id = 0;
            }
            else {
                $nleft = $node->{$obj->treeFields['left']};
                $nright = $node->{$obj->treeFields['right']};
                $parent_id = $node->{$obj->treeFields['parent']};
            }
 
            if ($childrenOnly) {
                if ($includeSelf) {
                    $query = sprintf('select %s from %s where %s = %d or %s = %d order by %s',
                                     join(',', $this->_getFields($obj)),
                                     $obj->tableName(),
                                     'id',
                                     $parent_id,
                                     $obj->treeFields['parent'],
                                     $parent_id,
                                     $obj->treeFields['left']);
                }
                else {
                    $query = sprintf('select %s from %s where %s = %d order by %s',
                                     join(',', $this->_getFields($obj)),
                                     $obj->tableName(),
                                     $obj->treeFields['parent'],
                                     $parent_id,
                                     $obj->treeFields['left']);
                }
            }
            else {
                if ($nleft > 0 && $includeSelf) {
                    $query = sprintf('select %s from %s where %s >= %d and %s <= %d order by %s',
                                     join(',', $this->_getFields($obj)),
                                     $obj->tableName(),
                                     $obj->treeFields['left'],
                                     $nleft,
                                     $obj->treeFields['right'],
                                     $nright,
                                     $obj->treeFields['left']);
                }
                else if ($nleft > 0) {
                    $query = sprintf('select %s from %s where %s > %d and %s < %d order by %s',
                                     join(',', $this->_getFields($obj)),
                                     $obj->tableName(),
                                     $obj->treeFields['left'],
                                     $nleft,
                                     $obj->treeFields['right'],
                                     $nright,
                                     $obj->treeFields['left']
                                     );
                }
                else {
                    $query = sprintf('select %s from %s order by %s',
                                     join(',', $this->_getFields($obj)),
                                     $obj->tableName(),
                                     $obj->treeFields['left']
                                     );
                }
            }
            
            $res = DB_DataObject::factory($obj->tableName());
            $res->query($query);
            return $res;
        }
 
        /**
         * Fetch the children of a node, or if no node is specified, fetch the
         * top level items.
         *
         * @param   int     $id             The ID of the node to fetch child data for.
         * @param   bool    $includeSelf    Whether or not to include the passed node in the
         *                                  the results.
         * @return  array                   The children of the passed node
         */
/*        function getChildren(&$obj, $id = 0, $includeSelf = false)
        {
            return $this->getDescendants($obj, $id, $includeSelf, true);
        }
 */
        /**
         * Fetch the path to a node. If an invalid node is passed, an empty array is returned.
         * If a top level node is passed, an array containing on that node is included (if
         * 'includeSelf' is set to true, otherwise an empty array)
         *
         * @param   int     $id             The ID of the node to fetch child data for.
         * @param   bool    $includeSelf    Whether or not to include the passed node in the
         *                                  the results.
         * @return  array                   An array of each node to passed node
         */
        function &getBranch(&$obj, $includeSelf = false)
        {
            if(!$obj->{$obj->treeFields['parent']}) {
                if($includeSelf) {
                    return $obj;
                } else {
                    return array();
                }
            }
            if ($includeSelf) {
                $query = sprintf('select %s from %s where %s <= %d and %s >= %d order by %s',
                                 join(',', $this->_getFields($obj)),
                                 $obj->tableName(),
                                 $obj->treeFields['left'],
                                 $obj->{$obj->treeFields['left']},
                                 $obj->treeFields['right'],
                                 $obj->{$obj->treeFields['right']},
                                 $obj->treeFields['level']);
            }
            else {
                $query = sprintf('select %s from %s where %s < %d and %s > %d order by %s',
                                 join(',', $this->_getFields($obj)),
                                 $obj->tableName(),
                                 $obj->treeFields['left'],
                                 $obj->{$obj->treeFields['left']},
                                 $obj->treeFields['right'],
                                 $obj->{$obj->treeFields['right']},
                                 $obj->treeFields['level']);
            }
 
            $result = DB_DataObject::factory($obj->tableName());
            $result->query($query);
            return $result;
        }
        
        /**
         * Check if one node descends from another node. If either node is not
         * found, then false is returned.
         *
         * @param   int     $descendant_id  The node that potentially descends
         * @param   int     $ancestor_id    The node that is potentially descended from
         * @return  bool                    True if $descendant_id descends from $ancestor_id, false otherwise
         */
        function isDescendantOf(&$obj, $descendant_id, $ancestor_id)
        {
            $node = $this->getNode($obj, $ancestor_id);
            if (is_null($node)) {
                return false;
            }
            $result = DB_DataObject::factory($obj->tableName());
            $result->id = $descendant_id;
            $result->whereAdd(sprintf('%s > %d',$obj->treeFields['left'],
            $node->{$obj->treeFields['left']}
            ));
            $result->whereAdd(sprintf('%s < %d',$obj->treeFields['right'],
            $node->{$obj->treeFields['right']}
            ));
            return $result->count() > 0;
 
            return false;
        }
 
        /**
         * Check if one node is a child of another node. If either node is not
         * found, then false is returned.
         *
         * @param   int     $child_id       The node that is possibly a child
         * @param   int     $parent_id      The node that is possibly a parent
         * @return  bool                    True if $child_id is a child of $parent_id, false otherwise
         */
        function isChildOf(&$obj, $child_id, $parent_id)
        {
            $result = DB_DataObject::factory($obj->tableName());
            $result->id = $child_id;
            $result->{$obj->treeFields['parent']} = $parent_id;
            return $result->count()>0;
        }
        function doRebuild($bool) {
            $this->_doRebuild = false;
        }
        function isRoot(&$obj) {
            return !$obj->{$obj->treeFields['parent']};
        }
        /**
         * Find the number of descendants a node has
         *
         * @param   int     $id     The ID of the node to search for. Pass 0 to count all nodes in the tree.
         * @return  int             The number of descendants the node has, or -1 if the node isn't found.
         */
        function numDescendants(&$obj, $id)
        {
            if ($id == 0) {
                $result = DB_DataObject::factory($obj->tableName());
                return (int)$result->count();
            }
            else {

                    return ($obj->{$obj->treeFields['right']} - $obj->{$obj->treeFields['left']} - 1) / 2;
                }
            return -1;
        }
        function hasChildren(&$obj) {
            return $this->numDescendants($obj,$obj->id)>0;
        }
        function &getParent(&$obj,$toRoot = true) {
            $parent = DB_DataObject::factory($obj->tableName());
            $parent->whereAdd($obj->treeFields['left'].' < '.$obj->{$obj->treeFields['left']}.' AND '.$obj->treeFields['right'].' > '.$obj->{$obj->treeFields['right']});
            $parent->orderBy($obj->treeFields['left'].' DESC');
            $parent->limit(0,1);
            if($parent->find(true)) {
                if(!$toRoot && $this->isRoot($parent)) {
                    return false;
                }
                return $parent;
            }
            return false;
        }
        function &getSiblings($obj) {
          if($parent = $this->getParent($obj,true)) {
            return $parent->getPlugin('tree')->getChildren($parent,$parent->id);
          }
          return $obj;
        }
        function &getPath(&$obj,$includeRoot = true,$includeSelf=false) {
          $query = sprintf('select * from %s where %s <%s %d and %s >%s %d %s order by %s',
                           $obj->tableName(),
                           $obj->treeFields['left'],
                           $includeSelf?'=':'',
                           $obj->{$obj->treeFields['left']},
                           $obj->treeFields['right'],
                           $includeSelf?'=':'',
                           $obj->{$obj->treeFields['right']},
                           $includeRoot?'':' AND '.$obj->treeFields['left'].'>0',
                           $obj->treeFields['left']);

            $path = DB_DataObject::factory($obj->tableName());
            $path->query($query);
            return $path;
        }

        function getMaxLevel(&$obj) {
            $o = DB_DataOBject::factory($obj->tableName());
            $o->query('SELECT max('.$obj->treeFields['level'].') as '.$obj->treeFields['level'].' from '.$obj->tableName().' WHERE gauche>'.$obj->{$obj->treeFields['left']}.' AND droite<'.$obj->{$obj->treeFields['right']});
            $res = $o->getDatabaseResult();


            return $res->fetchOne(0);
        }
        /**
         * Find the number of children a node has
         *
         * @param   int     $id     The ID of the node to search for. Pass 0 to count the first level items
         * @return  int             The number of descendants the node has, or -1 if the node isn't found.
         */
        function numChildren(&$obj, $id)
        {
            $result = DB_DataObject::factory($obj->tableName());
            $result->{$obj->treeFields['parent']} = $id;
            return (int)$result->count();
        }
 
        /**
         * Fetch the tree data, nesting within each node references to the node's children
         *
         * @return  array       The tree with the node's child data
         */
        function getTreeWithChildren($obj)
        {
            $idField = 'id';
            $parentField = $obj->treeFields['parent'];
            $result = DB_DataObject::factory($obj->tableName());
            $result->orderBy($obj->treeFields['sort']);
            $result->find();
            // create a root node to hold child data about first level items
            $root = new stdClass;
            $root->$idField = 0;
            $root->children = array();
 
            $arr = array($root);
 
            // populate the array and create an empty children array
            while ($result->fetch()) {
                $arr[$result->$idField] = clone($result);
                $arr[$result->$idField]->children = array();
                $arr[0]->children[$result->$idField]=$result->$idField;
            }
 
            // now process the array and build the child data
            foreach ($arr as $id => $row) {
                if (isset($row->$parentField))
                    $arr[$row->$parentField]->children[$id] = $id;
            }
 
            return $arr;
        }
 
        /**
         * Rebuilds the tree data and saves it to the database
         */
        function rebuild(&$obj)
        {
            if(!$this->_doRebuild) {return;}
            $n = 0; // need a variable to hold the running n tally
            $level = 0; // need a variable to hold the running level tally
            $roots = $this->getChildren($obj, 0);
            $data = array();

            while($roots->fetch()) {
                $this->_generateTreeData($obj, $roots->id, $data, $level, $n);
            }
            foreach ($data as $id => $row) {
                $record = DB_DataObject::factory($obj->tableName());
                $record->query(sprintf('UPDATE %s SET %s = %d, %s = %d, %s = %d WHERE %s = "%s"',
                                    $obj->tableName(),
                                    $obj->treeFields['left'],
                                    $row['left'],
                                    $obj->treeFields['right'],
                                    $row['right'],
                                    $obj->treeFields['level'],
                                    $row['level'],
                                    'id',
                                    $id));
            }
        }
        
        /**
         * returns the dataobject with all the roots (multi-tree)
         **/
         function &getChildren(&$obj, $parent){
             $do = & DB_DataObject::factory($obj->tableName());
             if(!$parent) {
                 $do->whereAdd($obj->treeFields['parent'].' IS NULL OR '.$obj->treeFields['parent'].'="0"');
             } else {
                 $do->{$obj->treeFields['parent']} = $parent;
             }
             $do->orderBy($obj->treeFields['sort']);
             $do->find();
             return $do;
         }
            function &getRoot(&$obj) {
                $obj = $this->getChildren($obj, 0);
                $obj->fetch();
                return $obj;
            }     
         /**
         * Generate the tree data. A single call to this generates the n-values for
         * 1 node in the tree. This function assigns the passed in n value as the
         * node's nleft value. It then processes all the node's children (which
         * in turn recursively processes that node's children and so on), and when
         * it is finally done, it takes the update n-value and assigns it as its
         * nright value. Because it is passed as a reference, the subsequent changes
         * in subrequests are held over to when control is returned so the nright
         * can be assigned.
         *
         * @param   array   &$arr   A reference to the data array, since we need to
         *                          be able to update the data in it
         * @param   int     $id     The ID of the current node to process
         * @param   int     $level  The nlevel to assign to the current node
         * @param   int     &$n     A reference to the running tally for the n-value
         */
        function _generateTreeData(&$obj,$parent, &$arr, $level, &$n)
        {
            $parentlevel = $level;
            $left = (int)$n;

            $n++;
            $children = $this->getChildren($obj,$parent);
            while($children->fetch()) {
                $chleft = $n;
                $this->_generateTreeData($obj,$children->id, $arr, $level+1,$n);
                $arr[$children->id] = array('left'=>$chleft,'right'=>$n,'level'=>$level+1);
                $n++;
            }
            $arr[$parent] = array('left'=>$left,'right'=>$n,'level'=>$parentlevel);            
        }
        function globalRebuild(&$obj) {
            $this->rebuild($obj);
        }
        function getGlobalMethods() {
          return array('globalRebuild'=>array('title'=>'Reconstruire arbre','plugin'=>$this->plugin_name));
        }
    }
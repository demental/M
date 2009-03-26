<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   DB_DataObject_Plugin_Exporter
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 * The main goal of this plugin is to provide a batch action to export
 * data from a group by query
 * @todo add ability to group by more than one field
 */


class DB_DataObject_Plugin_Exporter extends DB_DataObject_Plugin {
  public $plugin_name='exporter';
  
  /**
   * Adds the "batchExport" batch method to the batch actions dropdown
   */
  public function getBatchMethods()
  {
    return array('batchExport'=>array('title'=>'Export','plugin'=>$this->plugin_name));
  }
  /**
   * Populating action data
   */
  public function prepareBatchExport($form,$obj)
  {

    foreach($obj->exporterProperties['groupableFields'] as $field) {
      $label = $obj->fb_fieldLabels[$field]?$obj->fb_fieldLabels[$field]:$field;
      $groupfields[$field] = $label;
    }
    $form->addElement('select','groupby',__('Group By'),$groupfields);
    $form->addElement('select','format',__('Format'),array('CSV'=>'CSV','HTML'=>'HTML'));
  }
  /**
   * exporting action
   */
  public function batchExport($obj,$data)
  {
    $result = array();
    // If query is already done, we group them the PHP way
    // @todo find the assertion to check if query already done
    if(1!=1) {
      // Double loop, first one populating data 
      while($obj->fetch()) {
        $temp[$obj->{$field}]++;
      }
      // second one, setting array to a Structures_DataGrid_Rendere_CSV compliant format
      foreach($temp as $key=>$val) {
        $result[] = array($data['groupby']=>$key,'Q'=>$val);
      }
    } else {
      // Otherwise, we add a 'group by' clause (much more performant)
      // And only select the groupby field and a count field
      $obj->selectAdd();
      $obj->selectAdd($data['groupby'].', count(1) as cnt');
      $obj->groupBy($data['groupby']);
      $obj->find();
      while($obj->fetch()) {
        $result[] = array($data['groupby']=>$obj->{$data['groupby']},'Q'=>$obj->cnt);
      }
    }
    require_once 'Structures/DataGrid.php';
    $s = new Structures_DataGrid();
    $s->bind($result);
    // @todo finetune options depending on choosen export format
    $s->setRenderer($data['format'],array('filename'=>'export'.ucfirst($obj->tableName()).'_'.$data['groupby'].'.csv'));
    $s->render();exit;
  }
}
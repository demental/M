<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   archiver.php
 * @author       Arnaud Sellenet <demental at github>
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 * Plugin that provides archiving features. Mostly used in office application
 */


class Plugins_Archiver_db extends M_Plugin
{
  public function getEvents()
  {
    return array('frontendsearch','postpreparesearchform','getbatchmethods','batch_archiverarchive');
  }
  public function postPrepareSearchForm($form,$obj)
  {
    $archiver_archived = MyQuickForm::createElement('select','archiver_archived',__('Archived'),array(''=>__('No'),'B'=>__('Both'),'O'=>__('Yes')));
    $form->insertElementBefore($archiver_archived,'__submit__');
  }
  public function frontendsearch($values,$obj)
  {
    switch($values['archiver_archived']) {
      case 'O':
        $obj->whereAdd($obj->tableName().'.archiver_archived=1');
      break;
      case 'B':
      break;
      default:
        $obj->whereAdd($obj->tableName().'.archiver_archived!=1');
      break;
    }
  }
  public function getbatchmethods($arr,$obj)
  {
    $arr['batch_archiverarchive']=array('title'=>__('Put into archive'),'plugin'=>'archiver');
    return self::returnStatus($arr);
  }
  public function batch_archiverarchive($obj)
  {
    while($obj->fetch()) {
      $obj->archiver_archived=1;
      $obj->update();
    }
  }
}

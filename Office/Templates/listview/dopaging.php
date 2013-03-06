<script type="text/javascript">
function jsdoaction(form,select){
  if(select.selectedIndex==0){
    return false;
  }
  if($(select).val()=='delete'){
    if(confirm("<?php _e('Are you sure you want to delete these records ?')?>")){
      form.choice.value="Yes";
      form.submit();
    } else {
      return false;
    }
  }
  form.submit();
}
function deleteCheckboxClicked(checkbox) {
  el = checkbox.parentNode;
  while (el && el.nodeName != "TR") {
    el = el.parentNode;
  }
  if (el && el.nodeName == "TR") {
    if (checkbox.checked) {
      el.oldClassName = el.className;
      el.className = "delete";
    } else {
      el.className = el.oldClassName;
      el.oldClassName = "";
    }
  }
}
</script>
<?php $pager = $dg->getPaging()?>

<?php $this->startCapture('pager')?>
<?php echo $pager['first']?>
<?php echo $pager['back']?>
<?php echo $pager['pages']?>
<?php echo $pager['next']?>
<?php echo $pager['last']?>
<?php $this->endCapture('pager')?>
<div class="pager">
<?php echo $this->getCapture('pager')?>
</div>
<table class="datagrid">
  <tr>
    <?php $this->i('listview/dopaging/actionsheader',array('edit'=>$edit, 'selectable' => $selectable))?>
    <?php foreach($dg->columns as $field=>$type):?>
      <th><a href="<?php echo $dg->do->getPlugin('pager')->getSortLink($field)?>"><?php echo $dg->fieldNames[$field]?></a></th>
    <?php endforeach?>
    <?php $this->i('listview/dopaging/end_header', null, true)?>
  </tr>
  <?php $do = $dg->do?>
  <?php $pk = MyFB::_getPrimaryKey($do)?>
  <?php foreach($do as $rec):?>
  <?php $col=$col=='odd'?'even':'odd'?>
  <tr class="<?php echo $col?>">
    <?php if($selectable):?>
      <?php $this->i('listview/dopaging/selector',array('do'=>$do))?>
    <?php endif?>
    <?php $this->i('listview/dopaging/actions',array('edit'=>$edit,'do'=>$do,'pk'=>$pk))?>
    <?php foreach($dg->columns as $field => $type):?>
      <?php $this->i(array('listview/dopaging/field_'.$field, 'listview/dopaging/type_'.$type, 'listview/dopaging/field'), array('do' => $do, 'field' => $field, 'type' => $type) , true)?>
    <?php endforeach?>
    <?php $this->i('listview/dopaging/end_row', array('do' => $do, 'field' => $field, 'type' => $type) , true)?>
  </tr>
  <?php endforeach?>
</table>
<div class="pager">
<?php echo $this->getCapture('pager')?>
</div>
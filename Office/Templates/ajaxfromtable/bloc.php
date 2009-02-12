<?php $this->i('ajaxfromtable/ajaxheader')?>
<ul class="linklist">
  <?php foreach($do as $aDo):?>
    <?php $this->i('ajaxfromtable/ajaxline',array('table'=>$table,'field'=>$field,'value'=>$value,'do'=>$aDo,'pk'=>$pk,'edit'=>$edit,'delete'=>$delete,'layouted'=>true))?>
  <?php endforeach?>
  <li id="endList__<?php echo $table?>" style="display:none"></li>
</ul>
<?php if($add):?>
  <?php $this->i('ajaxfromtable/addform',array('form'=>&$addform,'do'=>DB_DataObject::factory($do->tableName())))?>
<?php endif?>
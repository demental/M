<?php if($layouted):?>
    <li class="linkRow" id="<?php echo $table.$do->pk()?>">
    <?php endif?>
  <span class="actions">
  <?php if($edit):?>
  <a href="<?php echo M_Office_Util::getQueryParams(array('module'=>$table,'filterField'=>$field,'filterValue'=>$value,'editFromTableRecord'=>$do->{$pk},'ajaxfromtable'=>1),array_keys($_GET),false)?>" class="editlinelink" rel="<?php echo $table.$do->{$pk}?>">
    <?php echo Icons::getIcon('edit')?>
  </a>
  <?php endif?>
  <?php if($delete):?>
  <a href="<?php echo M_Office_Util::getQueryParams(array('module'=>$table,'deleteFromTableRecord'=>$do->$pk,'ajaxfromtable'=>1),array_keys($_GET),false)?>"  class="deletelinelink"  rel="<?php echo $table.$do->{$pk}?>">
    <?php echo Icons::getIcon('delete')?>
  </a>
  <?php endif?>
</span>
<span><?php echo $this->i('recordhtmlsummary',array('record'=>$do))?></span>
<?php if($layouted):?>
</li>
<?php endif?>
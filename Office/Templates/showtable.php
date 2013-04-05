<?php if($search):?>
  <?php $this->i('form-discrete',array('form'=>$search))?>
<?php endif?>
<ul class="gactions">
  <?php foreach($globalActions as $action):?>
    <li><a href="<?php echo $action['url']?>" <?php echo $action['attributes']?>><?php echo $action['title']?></a></li>
  <?php endforeach?>
</ul>
<?php $this->i('showtable_beforelistview', null, true)?>
<br style="clear:left" />
<br />
<span style="float:right;display:block"><?php _e('Total records')?> : <b><?php echo $dg->totalItems;?></b></span>
<?php if($selectable):?>
  <form id="showTableForm" method="post" action="<?php echo M_Office_Util::getQueryParams(array(),array(),false)?>">
    <?php echo M_Office_Util::hiddenFields(array(),true)?>
<?php endif?>
<?php $this->i('listview/'.$__listview,array('dg'=>$dg,'pager'=>$pager,'fields'=>$fields,'selectable'=>$selectable,'edit'=>$edit))?>
<?php if($selectable):?>
  <br />
  <a href="javascript:void(0)" rel="checkboxes"><?php _e('Check all')?></a>/<a href="javascript:void(0)"  rel="uncheckboxes"><?php echo __('Uncheck all')?></a>
  <br />
  <?php echo __('Scope')?>
  <select name="__actionscope">
    <option value="checked"><?php _e('Checked items')?></option>
    <option value="all"><?php _e('Search results')?></option>
  </select>
  :
  <select name="doaction" onchange="jsdoaction(this.form,this)">
    <option value=""></option>
    <?php foreach($batchActions as $act=>$val):?>
      <option value="<?php echo $act?>"><?php echo $val['title']?></option>
    <?php endforeach?>
  </select>
  <input type="hidden" name="choice" value="" />
</form>
<?php endif?>
<?php $this->i('listview/js',array('dg'=>$dg,'pager'=>$pager,'fields'=>$fields,'selectable'=>$selectable,'edit'=>$edit))?>
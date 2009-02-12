<?php if($search):?>
  <?php $this->i('form-discrete',array('form'=>$search))?>
<?php endif?>
<ul class="gactions">
  <?php foreach($globalActions as $action):?>
    <li><a href="<?php echo $action['url']?>" <?php echo $action['attributes']?>><?php echo $action['title']?></a></li>
  <?php endforeach?>  
</ul>
<br style="clear:left" />
<br />
<span style="float:right;display:block">Total enregistrements : <b><?php echo $dg->totalItems;?></b></span>
<?php if($selectable):?>
  <form id="showTableForm">
<?php endif?>  
<?php $this->i('listview/'.$__listview,array('dg'=>$dg,'pager'=>$pager,'fields'=>$fields,'selectable'=>$selectable,'edit'=>$edit))?>
<?php if($selectable):?>
  <br />
  <a href="javascript:void(0)" rel="checkboxes"><?php echo __('Check all')?></a>/<a href="javascript:void(0)"  rel="uncheckboxes"><?php echo __('Uncheck all')?></a>
  <br />
  <?php echo __('Scope')?> 
  <select name="__actionscope">
    <option value="checked"><?php echo __('Checked items')?></option>
    <option value="all"><?php echo __('Search results')?></option>
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
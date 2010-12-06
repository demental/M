<?php $this->comment('@todo : add CSS and JS as assets')?>
<ul class="taglist">
<?php foreach($focus->getTags() as $tag):?>
<li>
  <form method="post" action="<?php echo M_Office::URL('tag:taghelper/remove',array('tagid'=>$tag->id,'focustable'=>$focus->tableName(),'focusid'=>$focus->pk()))?>">

    <input type="hidden" name="target" value="<?php echo M_Office::URL()?>" />
    <input type="image" src="/images/icons/cross.png" /></form>
<?php echo $tag?>
</li>    
<?php endforeach?>
<li>
    <form method="post" action="<?php echo M_Office::URL('tag:taghelper/addbystrip',array('focustable'=>$focus->tableName(),'focusid'=>$focus->pk()))?>">
      <input type="hidden" name="target" value="<?php echo M_Office::URL()?>" />
      <input type="text" name="strip" id="addtag_<?php echo $focus->tableName()?>_<?php echo $focus->pk()?>" size="20"/>&nbsp;<input type="submit" name="__submit__" value="+" />
      </form>
</li>
</ul>
<?php echo $this->c('tag:taghelper','autocomplete',array('field'=>'addtag_'.$focus->tableName().'_'.$focus->pk()))?>
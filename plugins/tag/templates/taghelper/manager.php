<?php $this->comment('@todo : add CSS and JS as assets')?>
<div class="taglist">
<?php foreach($focus->getTags() as $tag):?>

<span class="badge badge-default" title="<?php _e('On %s',array($tag->tagged_at_for($focus)))?>">
  <form method="post" action="<?php echo M_Office::URL('tag:taghelper/remove',array('tagid'=>$tag->id,'focustable'=>$focus->tableName(),'focusid'=>$focus->pk(),'focusmodule'=>$module))?>">
    <input type="hidden" name="target" value="<?php echo M_Office::URL()?>" />
    <button class="btn-xs btn-danger"><i class="fa fa-times"></i></button>
  </form>
<?php echo $tag?>
</span>
<?php endforeach?>
  <span class="badge badge-primary">
  <form method="post" action="<?php echo M_Office::URL('tag:taghelper/addbystrip',array('focustable'=>$focus->tableName(),'focusid'=>$focus->pk(),'focusmodule'=>$module))?>">
    <input type="hidden" name="target" value="<?php echo M_Office::URL()?>" />
    <input type="text" name="strip" id="addtag_<?php echo $focus->tableName()?>_<?php echo $focus->pk()?>" size="20"/>&nbsp;<button class="btn btn-xs btn-primary"><i class="fa fa-plus"></i></button>
    </form>
    </span>
</div>
<?php echo $this->c('tag:taghelper','autocomplete',array('field'=>'addtag_'.$focus->tableName().'_'.$focus->pk()))?>

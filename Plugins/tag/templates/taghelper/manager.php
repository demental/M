<?php $this->comment('@todo : add CSS and JS as assets')?>
<style type="text/css">
.taglist {
  overflow:hidden;
}
.taglist li {
  border:1px solid #888;
  background:#ccc;
  color:#444;
  -moz-border-radius:15px;
  display:inline-table;

  margin:.5em .5em;
  padding:.2em .5em .1em 1em;
  font-weight:bold;
}
.taglist form {
  margin:0;padding:0;display:inline;
  float:right;
}
.taglist input {
  border:none;  
  margin:0;
}
</style>
<ul class="taglist">
<?php foreach($focus->getTags() as $tag):?>
<li>
  <form method="post" action="<?php echo M_Office::URL('tag:taghelper/remove',array('tagid'=>$tag->id,'focustable'=>$focus->tableName(),'focusid'=>$focus->pk()))?>">

    <input type="hidden" name="target" value="<?php echo M_Office::URL()?>" />
    <input type="image" src="/images/icons/cross.png" /></form>
<?php echo $tag?>
</li>    
<?php endforeach?>
<li>&nbsp;
    <form method="post" action="<?php echo M_Office::URL('tag:taghelper/addbystrip',array('focustable'=>$focus->tableName(),'focusid'=>$focus->pk()))?>">
      <input type="hidden" name="target" value="<?php echo M_Office::URL()?>" />
      <input type="text" name="strip" id="addtag_<?php echo $focus->tableName()?>_<?php echo $focus->pk()?>" size="20"/>&nbsp;<input type="submit" name="__submit__" value="+" />
      </form>
</li>
</ul>
<?php echo $this->c('tag:taghelper','autocomplete',array('field'=>'addtag_'.$focus->tableName().'_'.$focus->pk()))?>
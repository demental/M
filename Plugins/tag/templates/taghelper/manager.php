<ul>
<?php foreach($focus->getTags() as $tag):?>
<li>
  <form method="post" action="<?php echo M_Office::URL('pluginspool/tag',array('__tagmodule'=>'taghelper','__tagaction'=>'remove','tagid'=>$tag->id,'focustable'=>$focus->tableName(),'focusid'=>$focus->pk()))?>">
    <input type="hidden" name="target" value="<?php echo M_Office::URL()?>" />
    <input type="image" src="/images/icons/bin_closed.png" /><?php echo $tag?></form></li>
<?php endforeach?>
<ul class="taglist">
<?php foreach($focus->getTags() as $tag):?>
<li>
  <a title="<?php _e('On %s',array($tag->tagged_at_for($focus)))?>"><?php echo $tag?></a>
</li>
<?php endforeach?>
</ul>

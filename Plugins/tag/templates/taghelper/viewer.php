<ul class="taglist">
<?php foreach($focus->getTags() as $tag):?>
<li>
  <a title="<?php _e('On %s',array($tag->link_tagged_at))?>"><?php echo $tag?></a>
</li>    
<?php endforeach?>
</ul>
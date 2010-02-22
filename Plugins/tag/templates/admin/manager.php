<h1><?php _e('Tag manager')?></h1>
<p>If you want to delete a tag (red cross), <strong>be aware that deleting a tag will also remove its add/remove history !</strong></p>
<p>The lock allows to "archive" a tag. An archived tag does not appear in search forms for cleaner display. This operation can be reversed at any time. No data loss.</p>
<table class="datagrid tagmanager">
  <thead>
    <tr><th>&nbsp;</th><th>Strip</th><th>Currently tagged</th><th>Was added</th><th>Was removed</th><th>&nbsp;</th></tr>
  </thead>
  <tbody>

  <?php foreach($tags as $tag):?>
    <tr>
      <td>
        <a href="<?php echo M_Office::URL('tag:admin/switchlock',array('id'=>$tag->id))?>">
          <img src="/images/icons/lock<?php if(!$tag->archived):?>_open<?php endif?>.png" alt="archive on/off"/>
      <td><?php echo $tag->strip?></td>
      <td><?php echo $tag->nbtagged()?></td>
      <td><?php echo $tag->nbwasAdded()?> times</td>
      <td><?php echo $tag->nbwasRemoved()?> times</td>      
      <td><a class="del" href="<?php echo M_Office::URL('tag:admin/delete',array('id'=>$tag->id))?>"><img src="/images/icons/cross.png" /></a></td>
    </tr>
  <?php endforeach?>
  </tbody>
</table>
<script type="text/javascript">  
$(function(){
  $('.tagmanager a.del').click(function(){
    return confirm('Are you sure ???? THIS CANNOT BE UNDONE !!');
  })
})
</script>
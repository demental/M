<table class="datagrid">
<?php foreach($photos as $photo):?>
  <tr>
    <td>
      <img src="<?php echo $photo->atSize(200)?>" alt="<?php echo $photo->atSize(200) ?>" />
    </td>
    <td>
      <?php echo $photo->getOwner()?>
    </td>
  </tr>    
<?php endforeach?>
</table>
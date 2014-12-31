<style type="text/css">
  .tag_history li {
  }
  .tag_history li span {
    padding: 0 1em;
  }
</style>
<div class="tag_history">
  <ul>
  <?php foreach($history as $item):?>
    <li>
    <date><?php echo $item->date?></date>
    <?php if($item->direction == 'add'):?>
      <span class="badge badge-success"><i class="icon icon-plus-sign"></i> <?php echo $item->tag()?></span>
    <?php else:?>
      <span class="badge badge-important"><i class="icon icon-minus-sign"></i> <?php echo $item->tag()?></span>
    <?php endif?>
    </li>

  <?php endforeach?>
  </ul>
</div>

<?php if(method_exists($record,'toHtml')):?>
  <?php echo $record->toHtml()?>
<?php else:?>
 <?php echo $record?>
<?php endif?>
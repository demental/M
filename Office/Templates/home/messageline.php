<li><strong><?php echo date('d/m H:i',strtotime($mess->date))?></strong>
  <?php if($mess->by):?> by <?php echo $mess->getLink('by')?><?php endif?>
  
   : <?php echo nl2br($mess->content)?></li>
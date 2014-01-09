<h2><i class="icon icon-warning-sign"></i> <?php echo $message ?></h2>
<?php if('development' == MODE):?>
  <pre><?php echo $error->getTraceAsString()?></pre>
<?php endif?>

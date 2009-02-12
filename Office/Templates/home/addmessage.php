<?php $f = $this->rf($form,'static')?>
<form <?php echo $f['attributes']?>>
  <?php echo $f['hidden']?>
  <?php echo date('d/m H:i')?>
  <?php echo $f['content']['html']?><?php echo $f['__submit__']['html']?>
</form>

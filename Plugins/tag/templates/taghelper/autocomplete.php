<link rel="stylesheet" type="text/css" href="/css/jquery.autocomplete.css" />
<?php if($local):?>
<?php else:?>
<?php endif?>
<?php foreach($tags as $tag):?>
<?php $arr[]=$tag->strip?>
<?php endforeach?>

<?php $this->startCapture('js')?>

  data = <?php echo json_encode($arr)?>;
  $('#<?php echo $field?>').autocomplete({source: data});

<?php $this->endCapture(); Mtpl::addJSinline($this->getCapture('js'))?>
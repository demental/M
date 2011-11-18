<link rel="stylesheet" type="text/css" href="/css/jquery.autocomplete.css" />
<script type="text/javascript" src="/js/jquery.autocomplete.min.js"></script>
<?php if($local):?>
<?php else:?>
<?php endif?>
<?php foreach($tags as $tag):?>
<?php $arr[]=$tag->strip?>
<?php endforeach?>

<script type="text/javascript">
$(function(){
  data = <?php echo json_encode($arr)?>;
  $('#<?php echo $field?>').autocomplete(data);
})
</script>
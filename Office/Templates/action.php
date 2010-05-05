<?php $this->i('form-discrete',array('form'=>$actionform,'cancel'=>true,'cancelurl'=>M_Office_Util::getQueryParams(array(),array('doSingleAction','glaction','doaction'))))?>
<?php if($isdownload):?>
<script type="text/javascript">
var nextDone = false;
$('form').bind('submit',function(){
  if(!nextDone) {
    $('<input type="submit" value=">> <?php _e('Next')?>" name="__submitnext__"/>').appendTo($(this).find('.submitbutton'));
  }
  nextDone = true;
});
</script>
<?php endif?>
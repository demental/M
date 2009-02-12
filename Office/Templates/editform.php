<?php $this->i('form-discrete',array('form'=>$form,'do'=>$do))?>
<div id="relatedafter">
    <br style="clear:both" />
	<?php if(is_array($ajaxFrom['after'])):?>
	<?php foreach($ajaxFrom['after'] as $list):?>
	    <?php echo $list?>
	<?php endforeach?>
	<?php endif?>
</div>

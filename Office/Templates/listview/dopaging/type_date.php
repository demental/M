<td>
	<?php if($do->$field):?>
		<?php echo date(Config::get('date_format'), strtotime($do->$field))?>
	<?php else:?>
		-
	<?php endif?>
</td>
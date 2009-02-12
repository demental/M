	<div style="text-align: center; clear: both;">
		<?php if($regenerate): ?>
				<a href="<?php echo M_Office_Util::getQueryParams(array('regenerate' => 1),
																									array('record', 'page', 'delete', 'table'))?>">Regénérer les modèles</a>

		<?php endif ?>
	</div>
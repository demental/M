<?php
Log::info('Init tag plugin');
$dispatchOpt = &PEAR::getStaticProperty('Dispatcher','global');
$dispatchOpt['all']['modulepath'][]='M/Plugins/tag/modules/';

$moduleOpt = &PEAR::getStaticProperty('Module','global');
$moduleOpt['template_dir'][]='M/Plugins/tag/templates/';
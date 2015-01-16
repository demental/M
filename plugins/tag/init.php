<?php
Log::info('Init tag plugin');
Mreg::append('autoload',
  array('tagtrigger'=> dirname(__FILE__).'lib/tagTrigger.php'));

M::addPath('module', dirname(__FILE__).'/modules/');

M::addPath('template', dirname(__FILE__).'/templates/');

M::addPath('model', dirname(__FILE__).'/models/');


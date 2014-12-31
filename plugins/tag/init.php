<?php
Log::info('Init tag plugin');
Mreg::append('autoload',
  array('tagtrigger'=> dirname(__FILE__).'lib/tagTrigger.php'));

<?php
Log::info('Init tag plugin');
Mreg::append('autoload',
  array('tagtrigger'=>'M/plugins/tag/lib/tagTrigger.php'));

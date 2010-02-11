<?php
Log::info('Init tag plugin');
Mreg::append('autoload',
  array('tagtrigger'=>'M/Plugins/tag/lib/tagTrigger.php'));
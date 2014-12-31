<?php

Mreg::append('autoload',
  array('wptools'=> dirname(__FILE__).'/lib/wptools.php'));

T::$config['switch_callbacks'][]='WPTools::on_switch_lang';

WPTools::on_switch_lang(T::getLang());

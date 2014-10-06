<?php
require_once 'M/tests/test_helper.php';

class TestOfStrings extends PHPUnit_Framework_TestCase {
  public function setup()
  {
  }
  public function testSnake()
  {
    $this->assertEquals('here_i_am',Strings::snake('Here I am'));
    $this->assertEquals('here_i_am',Strings::snake('Here-I am'));
    $this->assertEquals('here_i_am',Strings::snake('Here I am!'));
    $this->assertEquals('here_i_am',Strings::snake(',Here I am'));
    $this->assertEquals('here_i_am',Strings::snake(',Here,   I am'));
    $this->assertEquals('me_voila',Strings::snake('Me voilà'));
  }
  public function testUnspacify()
  {
    $this->assertEquals('Here_I_am',Strings::unspacify('Here I am'));
    $this->assertEquals('Here_I_am',Strings::unspacify('Here-I am'));
    $this->assertEquals('Here_I_am!',Strings::unspacify('Here I am!'));
    $this->assertEquals(',Here_I_am',Strings::unspacify(',Here I am'));
    $this->assertEquals(',Here,_I_am',Strings::unspacify(',Here,   I am'));
    $this->assertEquals('Me_voilà',Strings::unspacify('Me voilà'));

  }
  public function testPascal()
  {
    $this->assertEquals('HereIAm',Strings::pascal('Here I am'));
    $this->assertEquals('HereIAm',Strings::pascal('Here-I am'));
    $this->assertEquals('HereIAm',Strings::pascal('Here I am!'));
    $this->assertEquals('HereIAm',Strings::pascal(',Here I am'));
    $this->assertEquals('MeVoila',Strings::pascal('Me voilà'));
  }
  public function testCamel()
  {
    $this->assertEquals('hereIAm',Strings::camel('Here I am'));
    $this->assertEquals('hereIAm',Strings::camel('Here-I am'));
    $this->assertEquals('hereIAm',Strings::camel('Here I am!'));
    $this->assertEquals('hereIAm',Strings::camel(',Here I am'));
    $this->assertEquals('meVoila',Strings::camel('Me voilà'));
  }

}

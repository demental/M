<?php

class TestOfMArray extends UnitTestCase {
  public function setup()
  {
  }
  public function testArray_merge_recursive_unique()
  {
    $array1 = array(
    'element1',
    'element2'=>array('subkey1'=>'a','subkey2'=>'b'),
    'element3'=>'a',
    array(1,2,3,'key'=>'a')
    );
    $array2 = array(
      'otherelement1',
      'element2'=>array('subkey1'=>'z','subkey3'=>'x'),
      'element3'=>'b',
      array(4)
      );
    $expected = array(
      'otherelement1',
      'element2'=>array('subkey1'=>'z','subkey2'=>'b','subkey3'=>'x'),
      'element3'=>'b',
      array(4,2,3,'key'=>'a')
      );
      $this->assertEqual(MArray::array_merge_recursive_unique($array1,$array2),$expected);
  }
}
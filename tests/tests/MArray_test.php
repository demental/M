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
  public function testMultisumWithAssociativeArrays()
  {
    $arr1 = array('orange'=>1,'blue'=>3,'red'=>2);
    $arr2 = array('orange'=>2,'green'=>1);
    $result = array('orange'=>3,'blue'=>3,'red'=>2,'green'=>1);
    $this->assertEqual(MArray::multisum($arr1,$arr2),$result);
  }
  public function testMultisumWithIndexedArrays() {
    $arr1 = array(1,2,0,4);
    $arr2 = array(3,1,3);
    $result = array(4,3,3,4);
    $this->assertEqual(MArray::multisum($arr1,$arr2),$result);    
  }  
}
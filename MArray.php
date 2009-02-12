<?php
// ====================================
// = Static class providing array related helper methods
// ====================================
class MArray {
  public static function array_merge_recursive_unique( $first, $second, $greedy=false) {
     $inter = array_intersect_assoc(array_keys($first), array_keys($second)); # shaired keys
  # the idea next, is to strip and append from $second into $first
     foreach ( $inter as $key ) {
     # recursion if both are arrays
       if ( is_array($first[$key]) && is_array($second[$key]) ) {
           $first[$key] = self::array_merge_recursive_unique($first[$key], $second[$key]);
       }
     # non-greedy array merging:
       else if ( is_array($first[$key] && !$greedy ) ) {
           $first[$key][] = $second[$key];
       }
       else if ( is_array($second[$key]) && !$greedy ) {
           $second[$key][] = $first[$key];
           $first[$key] = $second[$key];
       }
     # overwrite...
       else {
           $first[$key] = $second[$key];
       }
       unset($second[$key]);
     }
  # merge the unmatching keys onto first
     return array_merge($first, $second);
  }
  
}
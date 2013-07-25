<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   MArray
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 *
 * Static class providing array related helper methods
 *
 */
class MArray {

	/**
	 *
	 * Merge array two arrays recursively
	 * @see testing : M/tests/MArray_test.php
	 *
	 * @access	public
	 * @return	Merged array	array
	 * @static
	 *
	 */
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

  public static function multisum($arr1,$arr2)
  {
    $result = array();
    $keys = array_keys(array_merge($arr1,$arr2));

    foreach($keys as $key) {
      $result[$key] = $arr1[$key]+$arr2[$key];
    }
    return $result;
  }

  public static function flatten_keys($array, $prefix = '')
  {
      $result = array();

      foreach ($array as $key => $value)
      {
          $new_key = $prefix . (empty($prefix) ? '' : '.') . $key;

          if (is_array($value))
          {
              $result = array_merge($result, self::flatten_keys($value, $new_key));
          }
          else
          {
              $result[$new_key] = $value;
          }
      }

      return $result;
    }
}
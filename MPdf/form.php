<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   form.php
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 * PDF form management using forge_fdf (@see pdftk)
 * requirements : needs the pdftk program to be installed on the server
 * EXPERIMENTAL
 */

class MPdf_Form extends MPdf {

  // @protected stores the raw pdf result
  protected $rawdata;
  /**
   * Fills the form with data passed as array, writes to a temporary file and returns the file path
   * this is experimental for now, the API is not designed yet....
   * @param $data array. Must be (pretty sure) a one-dimension assoc array.
   */
  public function fillWithData($data)
  {
    foreach( $arr as $key => $value ) {
      // translate tildes back to periods
      $this->strings[ strtr($key, '~', '.') ]= $value;
    }

    $this->fields_hidden= array();
    $this->fields_readonly= array();
    $this->fdf_data_names = array();
  }
	public function fetch()
	{
		if($this->fetched) {return $this->rawdata;}
    $params = FDF_Forge::forge_fdf( '',
    		 $this->strings,
    		 $this->names,
    		 $this->hidden,
    		 $this->readonly );
    $this->rawdata = exec( 'pdftk test.pdf fill_form '. $fdf_fn. ' output - flatten' );
		$this->fetched = 1;
	}
	public function serve($filename)
	{
    $fdf_fn= tempnam( TMP_PATH, 'fdf' );

		$this->write($fdf_fn);
    header( 'Content-type: application/pdf' );
    header( 'Content-disposition: attachment; filename='.basename($filename,'.pdf').'.pdf' );
    echo $this->rawdata;
    die();
	}
	public function write($filename)
	{
		$this->fetch();
		$fp = fopen($filename,'w+');
		fwrite($fp,$this->rawdata);
		fclose($fp);
	}

}
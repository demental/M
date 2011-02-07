<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   form.php
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 * PDF form management using forge_fdf (@see pdftk)
 * requirements : needs the pdftk program to be installed on the server
 * EXPERIMENTAL
 */
if(!defined('PDFTK_BIN_PATH')) {
  define('PDFTK_BIN_PATH','pdftk');
}
class MPdf_Form extends MPdf {

  // @protected stores the raw pdf result
  protected $_rawdatafile;

  protected $_datafile;
  //
  public $sourcefile;
  
  public function __construct($sourcefile)
  {
    $this->sourcefile = $sourcefile;
  }
  /**
   * Fills the form with data passed as array, writes to a temporary file and returns the file path
   * this is experimental for now, the API is not designed yet....
   * @param $data array. Must be (pretty sure) a one-dimension assoc array.
   */
  public function fillWithData($data)
  {
    foreach( $data as $key => $value ) {
      // translate tildes back to periods
      $this->strings[ strtr($key, '~', '.') ]= utf8_decode($value);
    }

    $this->hidden= array();
    $this->readonly= array();
    $this->names = array();
    return $this;
  }
	public function fetch()
	{
		if($this->fetched) {return $this->rawdata;}
    $params = FDF_Forge::forge_fdf( '',
    		 $this->strings,
    		 $this->names,
    		 $this->hidden,
    		 $this->readonly );
       $fdf_fn= tempnam( TMP_PATH, 'fdf' );
       $this->_rawdatafile= tempnam( TMP_PATH, 'fdfpdf' );
       $fp= fopen( $fdf_fn, 'w+' );
       if( $fp ) {
         fwrite( $fp, $params );
         fclose( $fp );
       } else {
         die('could not save '.$fdf_fn);
       }
    
    $com = PDFTK_BIN_PATH.' "'.$this->sourcefile.'" fill_form '. $fdf_fn. ' output - flatten > '.$this->_rawdatafile ;

    exec($com);
		$this->fetched = 1;
	}
	public function serve($filename)
	{
    $this->fetch();
    header( 'Content-type: application/pdf' );
    header( 'Content-disposition: attachment; filename='.basename($filename,'.pdf').'.pdf' );
    readfile($this->_rawdatafile);
    die();
	}
	public function write($filename)
	{
		$this->fetch();
		copy($this->_rawdatafile,$filename);
	}

}
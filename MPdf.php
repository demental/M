<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   MPdf
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

require_once 'lib/dompdf/dompdf_config.inc.php';
require_once 'lib/dompdf/lib/class.pdf.php';

/**
 * 
 * PDF generation class using dompdf external library and Mtpl as the template engine
 *
 */
class MPdf extends Maman {
	public $template;
	protected $view;
	protected $pdf;
	function __construct($config,$settings = null) {
		if(!is_array($settings)) {
			$settings = array();
		}
		$defaults = array('format'=>'a4','orientation'=>'portrait');
		foreach($defaults as $setting=>$default) {
			if(!$settings[$setting]) {
				$settings[$setting] = $default;
			}
		}
		$this->setConfig($config);
		$this->view = new Mtpl($this->getConfig('template_dir'));
		$this->pdf = new DOMPDF();
		$this->pdf->set_paper($settings['format'],$settings['orientation']);

	}
	public static function merge($mpdfs,$filename,$outputmode = 'I')
	{
		// TODO Add options
		require_once 'lib/fpdi/fpdi.php';
		$p = new fpdi();
		$n=0;
		foreach($mpdfs AS $mpdf) {
			if(is_a($mpdf,'MPdf')){
				$mpdf->write(TMP_PATH.'/temmpdf'.$n.'.pdf');
				$file = TMP_PATH.'/temmpdf'.$n.'.pdf';
			} else {
				$file=$mpdf;
			}
			$n++;
			$files[]=$file;
			$pagecount = $p->setSourceFile($file);
			for ($i = 1; $i <= $pagecount; $i++) {
				$tplidx = $p->ImportPage($i);
				$p->AddPage();
				$p->useTemplate($tplidx);
			}
		}
		foreach($files as $v){
			//  			@unlink($v);
		}
		$p->output($filename,$outputmode);
	}
	function setVars($vars) {
		$this->fetched = false;
		$this->view->assignArray($vars);
	}
	function setTemplate($tpl) {
		$this->template = $tpl;
	}
	/**
	 * Returns a MPdf_Form instance
	 * @param string source file
	 * 
	 */
	public static function &formfactory($formfile) {
	  $pdf = new MPdf_Form($formfile);
	  return $pdf;
	}
	public static function &factory ( $template,$settings = null )
	{
		$opt = array('all'=>PEAR::getStaticProperty('MPdf','global'));
		$pdf = new MPdf($opt,$settings);
		$pdf->setConfigValue('template',$template);
		return $pdf;
	}
	public function serve($filename)
	{
		$this->fetch();
		$this->pdf->stream($filename);
	}
	public function write($filename)
	{
		$this->fetch();
		$res = $this->pdf->output();
		$fp = fopen($filename,'w+');
		fwrite($fp,$res);
		fclose($fp);
	}
	public function fetch()
	{
		if($this->fetched) {return;}
		$html = utf8_decode($this->view->fetch($this->getConfig('template')));
		//      $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');

		$this->pdf->load_html($html);
		$this->pdf->render();
		$this->fetched = 1;
	}
	public function &getPdf()
	{
		return $this->pdf;
	}
	public function getContents()
	{
		return $this->view->fetch($this->getConfig('template'));
	}
	public function __toString()
	{
		$this->fetch();
		return $this->pdf->output();
	}
	public function __destruct()
	{
		unset($this->view);
		unset($this->pdf);
	}
}
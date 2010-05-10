<?php
/**
* M PHP Framework
* @package      M
* @subpackage   HTML_QuickForm_advandate
*/
/**
* M PHP Framework
*
* QuickForm element to create a date range multiselect
*
* @package      M
* @subpackage   HTML_QuickForm_advandate
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

require_once 'HTML/QuickForm/group.php';
require_once 'HTML/QuickForm/select.php';
require_once 'HTML/QuickForm/date.php';
class HTML_QuickForm_advandate extends HTML_QuickForm_group
{
    var $_options = array(
        'language'         => 'en',
        'format'           => 'dmY',
        'minYear'          => 2001,
        'maxYear'          => 2020,
        'addEmptyOption'   => false,
        'emptyOptionValue' => '',
        'emptyOptionText'  => '&nbsp;',
        'optionIncrement'  => array('i' => 1, 's' => 1)
    );

	/**
	 * international values for the first select
	 * @access array
	 */
	 
	 var $_locale = array(
	 						'en'=>array(	'firstselect'=>array(	'none'=>'',
																	'is'=>'is',
																	'currentmonth'=>'is in the current month',
																	'lastmonth'=>'is in the last month',

	 																'before'=>'before',
	 																'after'=>'after',
	 																'between'=>'between',
							 										'inthelast'=>'in the last past'
							 									),
	 										'betweenseparator'=>'and',
	 										'units'=>array(	'Y'=>'Years',
	 														'm'=>'months',
	 														'd'=>'days',
	 														'H'=>'hours',
	 														'i'=>'minutes'
	 														)
	 										
	 										
	 										),
	 						'fr'=>array(	'firstselect'=>array(	'none'=>'',
																	'is'=>'est',
																	'currentmonth'=>'est du mois courant',
																	'lastmonth'=>'est du mois dernier',
	 																'before'=>'est antérieure à',
	 																'after'=>'est postérieure à',
	 																'between'=>'est comprise entre le',
							 										'inthelast'=>'est dans les dernier(e)s'
							 									),
	 										'betweenseparator'=>' et le ',
	 										'units'=>array(	'Y'=>'années',
	 														'm'=>'mois',
	 														'd'=>'jours',
	 														'H'=>'heures',
	 														'i'=>'minutes'
	 														)
	 										)
	 						);

   /**
    * These complement separators, they are appended to the resultant HTML
    * @access   private
    * @var      array
    */
    var $_wrap = array('', '');


	/**
	 * styles for the initial display of elements
	 * @access 	private
	 * @var		array
	 */
	 var $_style=array();

    // }}}
    // {{{ constructor

   /**
    * Class constructor
    * 
    * @access   public
    * @param    string  Element's name
    * @param    mixed   Label(s) for an element
    * @param    array   Options to control the element's display
    * @param    mixed   Either a typical HTML attribute string or an associative array
    */
    function HTML_QuickForm_advandate($elementName = null, $elementLabel = null, $options = array(), $attributes = null)
    {
        $this->HTML_QuickForm_element($elementName, $elementLabel, $attributes);
        $this->_persistantFreeze = true;
        $this->_appendName = true;
        $this->_type = 'advandate';
        // set the options, do not bother setting bogus ones
        if (is_array($options)) {
            foreach ($options as $name => $value) {
                if ('language' == $name) {
                    $this->_options['language'] = isset($this->_locale[$value])? $value: 'en';
					
                } elseif (isset($this->_options[$name])) {
                    if (is_array($value)) {
                        $this->_options[$name] = @array_merge($this->_options[$name], $value);
                    } else {
                        $this->_options[$name] = $value;
                    }
                }
            }
        }
    }

    // }}}
    // {{{ _createElements()

    function _createElements()
    {
        $this->_separator = null;
        $this->_elements = array();
        $separator =  '';
        $locale    =& $this->_locale[$this->_options['language']];
        $this->_elements[0] =& new HTML_QuickForm_select('firstselect', null, $locale['firstselect']);
    		$this->_elements[1]=& new HTML_QuickForm_date('firstdate', null, $this->_options,$this->_style[1]);
    		$this->_elements[2]=& new HTML_QuickForm_static(null,null,'<span ' . $this->_style[2] .' id="'.$this->_escapeString($this->getName()).'_dates_separator">'.$locale['betweenseparator'].'</span>');
    		$this->_elements[3]=& new HTML_QuickForm_date('seconddate', null, $this->_options,$this->_style[3]);
    		$this->_elements[4]=& new HTML_QuickForm_select('nbunits', null, $this->_createOptionList(1,30,1),$this->_style[4]);
    		$this->_elements[5]=& new HTML_QuickForm_select('unit', null, $locale['units'],$this->_style[5]);
    }
   function toHtml()
    {
    	$this->_js = '';
    	       
		if (!defined('HTML_QUICKFORM_ADVANDATE_EXISTS')) {
			$this->_js .="

			    var advandate_Update = function(groupName) {
					  value=$('select[name=\"'+groupName+'[firstselect]\"]').val();
						firstD = $('select[name^=\"'+groupName+'[firstdate]\"]');
            $(firstD).hide();
						secD = $('select[name^=\"'+groupName+'[seconddate]\"]');
            $(secD).hide();
						nbunits=$('select[name=\"'+groupName+'[nbunits]\"]');
            $(nbunits).hide();
						unit=$('select[name=\"'+groupName+'[unit]\"]');
            $(unit).hide();
						separator=\$('#".$this->_escapeString($this->getName())."_dates_separator');
						separator.hide();
					if(value=='before' || value=='after' || value=='is') {
            $(firstD).show();
					} else {
						if (value=='between'){
              $(firstD).show();
              $(secD).show();              
							separator.show();
						} else {
							if (value=='inthelast'){
                $(nbunits).show();
                $(unit).show();
                
							} else {
								if (value=='lastmonth'){
									// Nothing to show
								}
							}
						}
					}				
				}";
			
			
				define('HTML_QUICKFORM_ADVANDATE_EXISTS',TRUE);
		}
        include_once('HTML/QuickForm/Renderer/Default.php');
        $renderer =& new HTML_QuickForm_Renderer_Default();
        $renderer->setElementTemplate('{element}');
        parent::accept($renderer);        
        return "<script type=\"text/javascript\">\n//<![CDATA[\n\$(function(){" . (empty($this->_js)? '': $this->_js) . "\nadvandate_Update('".$this->_escapeString($this->getName())."');
        $('select[name=\"".$this->_escapeString($this->getName())."[firstselect]\"]').bind('change',function(){advandate_Update('".$this->_escapeString($this->getName())."')});
        });//]]>\n</script>" . $renderer->toHtml();
}               


  /**
    * Quotes the string so that it can be used in Javascript string constants   
    *
    * @access private
    * @param  string
    * @return string
    */
    function _escapeString($str)
    {
        return strtr($str,array(
            "\r"    => '\r',
            "\n"    => '\n',
            "\t"    => '\t',
            "'"     => "\\'",
            '"'     => '\"',
            '\\'    => '\\\\'
        ));
    }

    // }}}
    // {{{ _createOptionList()

   /**
    * Creates an option list containing the numbers from the start number to the end, inclusive
    *
    * @param    int     The start number
    * @param    int     The end number
    * @param    int     Increment by this value
    * @access   private
    * @return   array   An array of numeric options.
    */
    function _createOptionList($start, $end, $step = 1)
    {
        for ($i = $start, $options = array(); $start > $end? $i >= $end: $i <= $end; $i += $step) {
            $options[$i] = $i;
        }
        return $options;
    }

    // }}}
    // {{{ setValue()

    function setValue($value)
    {
        if (empty($value)) {
  			$value=array('firstselect'=>'none');
        } elseif(is_array($value) && key_exists('d',$value)) {
        	// $value is a date array. Move it to the firstdate array. This needs to get better handled (TODO)
        	$value=array('firstselect'=>'','firstdate'=>$value,'seconddate'=>$value);
        }	 
			switch($value['firstselect']){
				case 'is':
				case 'before':
				case 'after':
					$this->_style[0]=$this->_style[1]=array('style'=>'display:inline');
					$this->_style[3]=$this->_style[4]=$this->_style[5]=array('style'=>'display:none');
					$this->_style[2]='style="display:none"';
					break;
				case 'between':
					$this->_style[0]=$this->_style[1]=$this->_style[3]=array('style'=>'display:inline');
					$this->_style[2]='style="display:inline"';
					$this->_style[4]=$this->_style[5]=array('style'=>'display:none');
				break;
				case 'inthelast':
					$this->_style[0]=$this->_style[4]=$this->_style[5]=array('style'=>'display:inline');
					$this->_style[1]=$this->_style[3]=array('style'=>'display:none');
					$this->_style[2]='style="display:none"';
				break;
				default:
				$this->_style[1]=$this->_style[3]=$this->_style[4]=$this->_style[5]=array('style'=>'display:none');
				$this->_style[2]='style="display:none"';
				break;
			}								
        parent::setValue($value);
    }

	function exportvalue($submitvalues,$assoc=true)
	{
		$value=parent::exportvalue($submitvalues,$assoc);
        if($assoc) {
            $value = $value[$this->getName()];
        }
		switch($value['firstselect']){
			case 'lastmonth':
				$value['firstdate']=date("Y-m", mktime(0, 0, 0, date('m')-1, 1, date('Y')));
				unset($value['seconddate']);
				unset($value['unit']);
				unset($value['nbunits']);
				break;
			case 'currentmonth':
					$value['firstdate']=date("Y-m", mktime(0, 0, 0, date('m'), 1, date('Y')));
					unset($value['seconddate']);
					unset($value['unit']);
					unset($value['nbunits']);
					break;				
			case 'is':
			case 'before':
			case 'after':
				$value['firstdate']=$value['firstdate']['Y'].'-'.$value['firstdate']['m'].'-'.$value['firstdate']['d'];
				unset($value['seconddate']);
				unset($value['unit']);
				unset($value['nbunits']);
			break;
			case 'between':
				$value['firstdate']=$value['firstdate']['Y'].'-'.$value['firstdate']['m'].'-'.$value['firstdate']['d'];
				$value['seconddate']=$value['seconddate']['Y'].'-'.$value['seconddate']['m'].'-'.$value['seconddate']['d'];
				unset($value['unit']);
				unset($value['nbunits']);
			break;
			case 'inthelast':
				$date=date('Y-m-d H:i:s');
				$value['firstselect']='after';
				$$value['unit']=$value['nbunits'];
				$target = mktime(date("H")-$H, date("i")-$i, 0, date("m")-$m , date("d")-$d, date("Y")-$Y);
				$value['firstdate']=date('Y-m-d',$target);
				unset($value['seconddate']);
				unset($value['unit']);
				unset($value['nbunits']);
				
		}
		if($assoc) {
            $value = array($this->getName()=>$value);
        }
		return $value;
	}
    // }}}
    // {{{ accept()

    function accept(&$renderer, $required = false, $error = null)
    {
        $renderer->renderElement($this, $required, $error);
    }

    // }}}
    // {{{ onQuickFormEvent()

    function onQuickFormEvent($event, $arg, &$caller)
    {
        if ('updateValue' == $event) {
            // we need to call setValue(), 'cause the default/constant value
            // may be in fact a timestamp, not an array
            return HTML_QuickForm_element::onQuickFormEvent($event, $arg, $caller);
        } else {
            return parent::onQuickFormEvent($event, $arg, $caller);
        }
    }

    // }}}
}
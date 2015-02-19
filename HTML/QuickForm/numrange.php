<?php
/**
* M PHP Framework
* @package      M
* @subpackage   HTML_QuickForm_numrange
*/
/**
* M PHP Framework
*
* QuickForm element to create a numeric range (for search purpose)
*
* @package      M
* @subpackage   HTML_QuickForm_numrange
* @author       Arnaud Sellenet <demental@sat2way.com>

* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class HTML_QuickForm_numrange extends HTML_QuickForm_group
{
    var $_options = array(
        'language'         => 'en',
    );

	/**
	 * translate for the first select
	 * @access array
	 */
	 
	 var $_locale = array(
	 						'en'=>array(	'firstselect'=>array(
																	'='=>'is equal to',
																	'-%'=>'starts with',
																	'%-'=>'ends with',																	
	 																'<'=>'strictly less than',
	 																'>'=>'strictly more than',
	 																'<='=>'equal or less than',
	 																'>='=>'equal or more than',
	 																'between'=>'strictly between',
                                  'betweeninc'=>'between(including limits)'                                  
							 									),
	 										'betweenseparator'=>'and'	 										
	 										),
	 						'fr'=>array(	'firstselect'=>array(
																	'='=>'est égal à',
																	'-%'=>'commence par',
																	'%-'=>'finit par',																	
	 																'<'=>'est strictement inférieur à',
	 																'>'=>'est strictement supérieur à',
	 																'<='=>'est inférieur ou égal à',
	 																'>='=>'est supérieur ou égal à',
	 																'between'=>'est strictement compris entre',
                                  'betweeninc'=>'est compris entre (bornes incluses)'                                  
							 									),
	 										'betweenseparator'=>'et'
	 										),
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
    function HTML_QuickForm_numrange($elementName = null, $elementLabel = null, $options = array(), $attributes = null)
    {
        $this->HTML_QuickForm_element($elementName, $elementLabel, $attributes);
        $this->_persistantFreeze = true;
        $this->_appendName = true;
        $this->_type = 'numrange';
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
        $this->_elements[0] =& HTML_QuickForm::createElement('select','firstselect', null, $locale['firstselect'],array('onChange'=>'numrange_Update(this.form,\'' . $this->_escapeString($this->getName()) . '\')','id'=>'firstselect_'.$this->_escapeString($this->getName())));
    		$this->_elements[1]=& HTML_QuickForm::createElement('text','firstval', null, $this->_options,$this->_style[1]);
    		$this->_elements[2]=& HTML_QuickForm::createElement('static',null,null,'<span ' . $this->_style[2] .' id="'.$this->_escapeString($this->getName()).'_texts_separator">'.$locale['betweenseparator'].'</span>');
    		$this->_elements[3]=& HTML_QuickForm::createElement('text','secondval', null, $this->_options,$this->_style[3]);
    }
   function toHtml()
    {
    	$this->_js = '';
    	       
		if (!defined('HTML_QUICKFORM_NUMRANGE_EXISTS')) {
			$this->_js .="function numrange_Update(form, groupName) {
					index=form[groupName+'[firstselect]'].selectedIndex;
					value=form[groupName+'[firstselect]'][index].value;
						firstd=form[groupName+'[firstval]'];
						firstd.style.display='none';
						secondd=form[groupName+'[secondval]'];
						secondd.style.display='none';
						separator=\$('#".$this->_escapeString($this->getName())."_texts_separator');
						separator.hide();
					if(value=='=' || value=='<' || value=='>' || value=='<=' || value=='>=' || value=='%-' || value=='-%') {
						firstd.style.display='inline';
					} else {
							firstd.style.display='inline';
							secondd.style.display='inline';
							separator.show();
					}				
				}
				";
			
			
				define('HTML_QUICKFORM_NUMRANGE_EXISTS',TRUE);
		}
        include_once('HTML/QuickForm/Renderer/Default.php');
        $renderer =& new HTML_QuickForm_Renderer_Default();
        $renderer->setElementTemplate('{element}');
        parent::accept($renderer);        
        return (empty($this->_js)? '': "<script type=\"text/javascript\">\n//<![CDATA[\n" . $this->_js . ";				\$(function(){\$('#firstselect_".$this->_escapeString($this->getName())."').trigger('change');});//]]>\n</script>") . $renderer->toHtml();
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

    function setValue($value)
    {
      if(!is_array($value)) {
  			$value=array('firstselect'=>'%-');        
      } elseif(empty($value['firstselect'])) {
  			$value['firstselect']='%-';
      } else {
        $value=array('firstselect'=>$value['firstselect'],'firstval'=>$value['firstval'],'secondval'=>$value['secondval']);
      }	 
			switch($value['firstselect']){
				case 'betweeninc':
				case 'between':
					$this->_style[0]=$this->_style[2]=$this->_style[1]=$this->_style[3]=array('style'=>'display:inline');
				break;
        default:
					$this->_style[0]=$this->_style[1]=array('style'=>'display:inline');
					$this->_style[2]=$this->_style[3]=array('style'=>'display:none');
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
			case 'between':
			case 'betweeninc':
			break;
      default:
				unset($value['secondval']);
			break;
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
            return HTML_QuickForm_element::onQuickFormEvent($event, $arg, $caller);
        } else {
            return parent::onQuickFormEvent($event, $arg, $caller);
        }
    }

    // }}}
}

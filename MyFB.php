<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   myFB
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */


/**
 *
 * PEAR_DB_DataObject_FormBuilder tweaks
 *
 */
class myFB extends DB_DataObject_FormBuilder
{

	function DB_DataObject_FormBuilder(&$do, $options = false)
	{
		DB_DataObject_FormBuilder::DB_DataObject_FormBuilder($do, $options = false);
	}

	function &create(&$do, $options = false, $driver = 'MyQuickForm', $mainClass = 'MyFB',$driverPath='M/MyQuickForm.php')
	{
		if (!is_a($do, 'db_dataobject')) {
			$err =& PEAR::raiseError('DB_DataObject_FormBuilder::create(): Object does not extend DB_DataObject.',
			DB_DATAOBJECT_FORMBUILDER_ERROR_NODATAOBJECT);
			return $err;
		}

		if (!class_exists($mainClass)) {
			$err =& PEAR::raiseError('DB_DataObject_FormBuilder::create(): Main class "'.$mainClass.'" not found',
			DB_DATAOBJECT_FORMBUILDER_ERROR_UNKNOWNDRIVER);
			return $err;
		}
		$fb = new $mainClass($do, $options);
		$className = 'db_dataobject_formbuilder_'.strtolower($driver);

		if (!class_exists($className)) {
			/*$exists = false;
			 foreach (split(PATH_SEPARATOR, get_include_path()) as $path) {
			 if (file_exists($path.'/'.$fileName)
			 && is_readable($path.'/'.$fileName)) {
			 $exists = true;
			 break;
			 }
			 }*/
			$fp = @fopen($driverPath, 'r', true);
			if ($fp === false) {
				$err =& PEAR::raiseError('DB_DataObject_FormBuilder::create(): File "'.$fileName.
                                       '" for driver class "'.$className.'" not found or not readable.',
				DB_DATAOBJECT_FORMBUILDER_ERROR_UNKNOWNDRIVER);
				return $err;
			}
			fclose($fp);
			include_once($driverPath);
			if (!class_exists($className)) {
				$err =& PEAR::raiseError('DB_DataObject_FormBuilder::create(): Driver class "'.$className.
                                       '" not found after including "'.$fileName.'".',
				DB_DATAOBJECT_FORMBUILDER_ERROR_UNKNOWNDRIVER);
				return $err;
			}
		}

		$fb->_form = new $className($fb);
	  $fb->ruleViolationMessage = __('The value you have entered is not valid.');
    $fb->requiredRuleMessage = __('The following field is required.');

		return $fb;
	}
	function _getSelectOptions($table,
	$displayFields = false,
	$selectAddEmpty = false,
	$field = false,
	$valueField = false,
	$emptyLabel = false,
	$maindo = null,
	$preferHtml = false) {

	  if($this->_cacheOptions) {

      $cacheName = $this->_cacheOptions['name'].'_'.$this->_do->tableName().'_'.$table.'_'.$field;
      $options = array(
        'caching' =>true,
        'cacheDir' => $this->_cacheOptions['cacheDir'],
        'lifeTime' => 3600,
        'fileNameProtection'=>false,
      );

      $cache = new Cache_Lite($options);
  		if($_cachedData = $cache->get($cacheName)) {
        return $_cachedData;
      }
    }

		if(is_null($maindo)) {
			$maindo = $this;
		} else {
			if(empty($maindo->prepareLinkedDataObjectCallback)) {
				$maindo->prepareLinkedDataObjectCallback = array($maindo,'prepareLinkedDataObject');
			}
		}

		$opts = DB_DataObject::factory($table);
		if (is_a($opts, 'db_dataobject')) {
			if ($this->isCallableAndExists($maindo->prepareLinkedDataObjectCallback)) {
				call_user_func_array($maindo->prepareLinkedDataObjectCallback, array(&$opts, $field));
			}
			if ($valueField === false) {
				$valueField = $this->_getPrimaryKey($opts);
			}
			if (strlen($valueField)) {
				if ($displayFields === false) {
					if (isset($opts->fb_linkDisplayFields)) {
						$displayFields = $opts->fb_linkDisplayFields;
					} elseif ($this->linkDisplayFields){
						$displayFields = $this->linkDisplayFields;
					} else {
						$displayFields = array($valueField);
					}
				}

				if (isset($opts->fb_linkOrderFields)) {
					$orderFields = $opts->fb_linkOrderFields;
				} elseif ($this->linkOrderFields){
					$orderFields = $thus->linkOrderFields;
				} else {
					$orderFields = $displayFields;
				}
				$orderStr = '';
				$first = true;
				foreach ($orderFields as $col) {
					if ($first) {
						$first = false;
					} else {
						$orderStr .= ', ';
					}
					$orderStr .= $col;
				}
				if ($orderStr) {
					$opts->orderBy($orderStr);
				}
				$list = array();

				// FIXME!
				if ($selectAddEmpty) {
					$list[''] = $emptyLabel !== false ? $emptyLabel : $this->selectAddEmptyLabel;
				}
				// FINALLY, let's see if there are any results
				if (isset($opts->_DB_resultid) || $opts->find() > 0) {
					while ($opts->fetch()) {
						$list[$opts->$valueField] = $this->getDataObjectString($opts, $displayFields, null,1,$preferHtml);
					}
				}

				$opt =  $list;
			} else {
				$opt =  array();
			}
			if(is_array($maindo->selectAddEmptyLabel) && key_exists($field,$maindo->selectAddEmptyLabel) && $selectAddEmpty) {
				$opt['']=$this->selectAddEmptyLabel[$field];
			}
			if($cache) {
			  $cache->save($opt);
			}
			return $opt;
		}

		$this->debug('Error: '.get_class($opts).' does not inherit from DB_DataObject');
		return array();
	}
	function getDataObjectString(&$do, $displayFields = false, $linkDisplayLevel = null, $level = 1,$preferHtml = false) {
		if(DB_DataObject_FormBuilder::isCallableAndExists(array($do,'toHtml')) && $preferHtml) {
			return $do->toHtml();
		}if(DB_DataObject_FormBuilder::isCallableAndExists(array($do,'__toString'))) {
			return $do->__toString();
		} else {

			return parent::getDataObjectString($do, $displayFields, $linkDisplayLevel, $level);
		}
	}

	function &_generateForm($action = false, $target = '_self', $formName = false, $method = 'post')
	{
		if ($formName === false) {
			$formName = strtolower(get_class($this->_do));
		}
		if ($action === false) {
			$action = $_SERVER['PHP_SELF'];
		}

		// Retrieve the form object to use (may depend on the current renderer)
		$this->_form->_createFormObject($formName, $method, $action, $target);

		// Initialize array with default values
		//$formValues = $this->_do->toArray();

		if ($this->addFormHeader) {
			// Add a header to the form - set addFormHeader property to false to prevent this
			$this->_form->_addFormHeader(is_null($this->formHeaderText) ? $this->prettyName($this->_do->tableName()) : $this->formHeaderText);
		}

		// Go through all table fields and create appropriate form elements
		$keys = $this->_do->keys();

		// Reorder elements if requested, will return _getFieldsToRender if no reordering is needed
		$elements = $this->_reorderElements();

		//get elements to freeze
		$user_editable_fields = $this->_getUserEditableFields();
		if (is_array($user_editable_fields)) {
			$elements_to_freeze = array_diff(array_keys($elements), $user_editable_fields);
		} else {
			$elements_to_freeze = array();
		}

		if (!is_array($links = $this->_do->links())) {
			$links = array();
		}
		$pk = $this->_getPrimaryKey($this->_do);
		$rules = array();
		foreach ($elements as $key => $type) {
			// Check if current field is primary key. And primary key hiding is on. If so, make hidden field
			if (in_array($key, $keys) && $this->hidePrimaryKey == true) {
				$formValues[$key] = $this->_do->$key;
				$element =& $this->_form->_createHiddenField($key);
			} else {
				unset($element);
				// Try to determine field types depending on object properties
				$notNull = $type & DB_DATAOBJECT_NOTNULL;
				if (in_array($key, $this->dateFields)) {
					$type = DB_DATAOBJECT_DATE;
				} elseif (in_array($key, $this->timeFields)) {
					$type = DB_DATAOBJECT_TIME;
				} elseif (in_array($key, $this->textFields)) {
					$type = DB_DATAOBJECT_TXT;
				} elseif (in_array($key, $this->enumFields)) {
					$type = DB_DATAOBJECT_FORMBUILDER_ENUM;
				} elseif (in_array($key, $this->booleanFields)) {
					$type = DB_DATAOBJECT_BOOL;
				}
				if ($notNull || in_array($key, $this->fieldsRequired)) {
					$type |= DB_DATAOBJECT_NOTNULL;
				}
				if (isset($this->preDefElements[$key])
				&& (is_object($this->preDefElements[$key])
				|| is_array($this->preDefElements[$key]))) {
					// Use predefined form field, IMPORTANT: This may depend on the used renderer!!
					$element =& $this->preDefElements[$key];
				} elseif (isset($links[$key])) {
					// If this field links to another table, display selectbox or radiobuttons
					if(in_array($key,$elements_to_freeze)) {
						$do=&$this->_do->getLink($key);
						if(!is_object($do)) {
							$string='';
						} else {

							$string = $this->getDataObjectString($do);
						}

						$element = &$this->_form->_createStaticField($key,$string);

					} else {
						$isRadio = isset($this->linkElementTypes[$key]) && $this->linkElementTypes[$key] == 'radio';
						$opt = $this->getSelectOptions($key,
						false,
						false,//    $isRadio?false:!($type & DB_DATAOBJECT_NOTNULL),
						$isRadio ? $this->radioAddEmptyLabel : $this->selectAddEmptyLabel);
						if ($isRadio) {
							$element =& $this->_form->_createRadioButtons($key, $opt);
						} else {
							$element =& $this->_form->_createSelectBox($key, $opt);
						}
						unset($opt);
					}
				}

				// No predefined object available, auto-generate new one
				$elValidator = false;
				$elValidRule = false;

				// Auto-detect field types depending on field's database type
				switch (true) {
					case ($type & DB_DATAOBJECT_BOOL):
						if ($this->useAccessors
						&& method_exists($this->_do, 'get' . $key)) {
							$formValues[$key] = $this->_do->{'get'.$key}();
						} else {
							$formValues[$key] = $this->_do->$key;
						}
						if ($formValues[$key] === 'f') {
							$formValues[$key] = 0;
						}
						if (!isset($element)) {
							$element =& $this->_form->_createCheckbox($key, null, null, $this->getFieldLabel($key));
						}
						break;
					case ($type & DB_DATAOBJECT_INT):
						if ($this->useAccessors
						&& method_exists($this->_do, 'get' . $key)) {
							$formValues[$key] = $this->_do->{'get'.$key}();
						} else {
							$formValues[$key] = $this->_do->$key;
						}
						if (!isset($element)) {
							$element =& $this->_form->_createIntegerField($key);
							$elValidator = 'numeric';
						}
						break;
					case (($type & DB_DATAOBJECT_DATE) && ($type & DB_DATAOBJECT_TIME)):
						$this->debug('DATE & TIME CONVERSION using callback for element '.$key.' ('.$this->_do->$key.')!', 'FormBuilder');
						if ($this->useAccessors
						&& method_exists($this->_do, 'get' . $key)) {
							$fieldValue = $this->_do->{'get'.$key}();
						} else {
							$fieldValue = $this->_do->$key;
						}
						if ($this->isCallableAndExists($this->dateFromDatabaseCallback)) {
							$formValues[$key] = call_user_func($this->dateFromDatabaseCallback, $fieldValue);
						} else {
							$this->debug('WARNING: dateFromDatabaseCallback callback not callable', 'FormBuilder');
							$formValues[$key] = $fieldValue;
						}
						if (!isset($element)) {
							$element =& $this->_form->_createDateTimeElement($key);
						}
						break;
					case ($type & DB_DATAOBJECT_DATE):
						$this->debug('DATE CONVERSION using callback for element '.$key.' ('.$this->_do->$key.')!', 'FormBuilder');
						if ($this->useAccessors
						&& method_exists($this->_do, 'get' . $key)) {
							$fieldValue = $this->_do->{'get'.$key}();
						} else {
							$fieldValue = $this->_do->$key;
						}
						if ($this->isCallableAndExists($this->dateFromDatabaseCallback)) {
							$formValues[$key] = call_user_func($this->dateFromDatabaseCallback, $fieldValue);
						} else {
							$this->debug('WARNING: dateFromDatabaseCallback callback not callable', 'FormBuilder');
							$formValues[$key] = $fieldValue;
						}
						if (!isset($element)) {
							$element =& $this->_form->_createDateElement($key);
						}
						break;
					case ($type & DB_DATAOBJECT_TIME):
						$this->debug('TIME CONVERSION using callback for element '.$key.' ('.$this->_do->$key.')!', 'FormBuilder');
						if ($this->useAccessors
						&& method_exists($this->_do, 'get' . $key)) {
							$fieldValue = $this->_do->{'get'.$key}();
						} else {
							$fieldValue = $this->_do->$key;
						}
						if ($this->isCallableAndExists($this->dateFromDatabaseCallback)) {
							$formValues[$key] = call_user_func($this->dateFromDatabaseCallback, $fieldValue);
						} else {
							$this->debug('WARNING: dateFromDatabaseCallback callback not callable', 'FormBuilder');
							$formValues[$key] = $fieldValue;
						}
						if (!isset($element)) {
							$element =& $this->_form->_createTimeElement($key);
						}
						break;
					case ($type & DB_DATAOBJECT_TXT || $type & DB_DATAOBJECT_BLOB):
						if ($this->useAccessors
						&& method_exists($this->_do, 'get' . $key)) {
							$formValues[$key] = $this->_do->{'get'.$key}();
						} else {
							$formValues[$key] = $this->_do->$key;
						}
						if (!isset($element)) {
							$element =& $this->_form->_createTextArea($key);
						}
						break;
					case ($type & DB_DATAOBJECT_STR):
						if ($this->useAccessors
						&& method_exists($this->_do, 'get' . $key)) {
							$formValues[$key] = $this->_do->{'get'.$key}();
						} else {
							$formValues[$key] = $this->_do->$key;
						}
						if (!isset($element)) {
							// If field content contains linebreaks, make textarea - otherwise, standard textbox
							if (isset($this->_do->$key) && strlen($this->_do->$key) && strstr($this->_do->$key, "\n")) {
								$element =& $this->_form->_createTextArea($key);
							} else {
								$element =& $this->_form->_createTextField($key);
							}
						}
						break;
					case ($type & DB_DATAOBJECT_FORMBUILDER_CROSSLINK):
						unset($element);
						// generate crossLink stuff
						/*if ($pk === false) {
						return PEAR::raiseError('A primary key must exist in the base table when using crossLinks.');
						}*/


						$crossLink = $this->crossLinks[$key];
						$groupName  = $this->_sanitizeFieldName($key);
						unset($crossLinkDo);
						$crossLinkDo = DB_DataObject::factory($crossLink['table']);
						if (PEAR::isError($crossLinkDo)) {
							throw new Exception($crossLinkDo->getMessage());
						}

						if (!is_array($crossLinkLinks = $crossLinkDo->links())) {
							$crossLinkLinks = array();
						}

						list($linkedtable, $linkedfield) = explode(':', $crossLinkLinks[$crossLink['toField']]);
						list($fromtable, $fromfield) = explode(':', $crossLinkLinks[$crossLink['fromField']]);
						//if ($fromtable !== $this->_do->tableName()) error?

						  $all_options      = $this->_getSelectOptions($linkedtable, false, false, false, $linkedfield,false,$crossLinkDo,isset($crossLinkDo->fb_crossLinkExtraFields));




						$selected_options = array();
						if (isset($this->_do->$fromfield)) {
							$crossLinkDo->{$crossLink['fromField']} = $this->_do->$fromfield;
							if ($this->isCallableAndExists($this->prepareLinkedDataObjectCallback)) {
								call_user_func_array($this->prepareLinkedDataObjectCallback, array(&$crossLinkDo, $key));
							}
							if ($crossLinkDo->find() > 0) {
								while ($crossLinkDo->fetch()) {
									$selected_options[$crossLinkDo->{$crossLink['toField']}] = clone($crossLinkDo);
								}
							}
						}
						if (isset($crossLink['type']) && $crossLink['type'] == 'select') {
							unset($element);
							$element =& $this->_form->_createSelectBox($groupName, $all_options, true);
							$formValues[$groupName] = array_keys($selected_options); // set defaults later

							// ***X*** generate checkboxes
						} else {
							$element = array();
							$rowNames = array();
							foreach ($all_options as $optionKey => $value) {
								if (isset($selected_options[$optionKey])) {
									if (!isset($formValues[$groupName])) {
										$formValues[$groupName] = array();
									}
									$formValues[$groupName][$optionKey] = $optionKey;
								}

								$crossLinkElement = $this->_form->_createCheckbox($groupName.'['.$optionKey.']', $value, $optionKey);
								$elementNamePrefix = $this->elementNamePrefix.$groupName.'__'.$optionKey.'_';
								$elementNamePostfix = '_'.$this->elementNamePostfix;//']';

								if (isset($crossLinkDo->fb_crossLinkExtraFields)) {
									$row = array(&$crossLinkElement);
									if (isset($selected_options[$optionKey])) {
										$extraFieldDo = $selected_options[$optionKey];
									} else {
										unset($extraFieldDo);
										$extraFieldDo = DB_DataObject::factory($crossLink['table']);
									}
									unset($tempFb);
									$tempFb =& DB_DataObject_FormBuilder::create($extraFieldDo,
									false,
                                                                                              'QuickForm',
									get_class($this));
									$extraFieldDo->fb_fieldsToRender = $crossLinkDo->fb_crossLinkExtraFields;
									$extraFieldDo->fb_elementNamePrefix = $elementNamePrefix;
									$extraFieldDo->fb_elementNamePostfix = $elementNamePostfix;
									$extraFieldDo->fb_linkNewValue = false;
									$this->_extraFieldsFb[$elementNamePrefix.$elementNamePostfix] =& $tempFb;
									$tempForm = $tempFb->getForm();
									$colNames = array('');
									foreach ($crossLinkDo->fb_crossLinkExtraFields as $extraField) {
										if ($tempForm->elementExists($elementNamePrefix.$extraField.$elementNamePostfix)) {
											$tempEl =& $tempForm->getElement($elementNamePrefix.$extraField.$elementNamePostfix);
											$colNames[$extraField] = $tempEl->getLabel();
										} else {
											$tempEl =& $this->_form->_createStaticField($elementNamePrefix.$extraField.$elementNamePostfix,
                                                                                                     'Error - element not found for extra field '.$extraField);
										}
										$row[] =& $tempEl;
										if (!isset($formValues[$groupName.'__extraFields'])) {
											$formValues[$groupName.'__extraFields'] = array();
										}
										if (!isset($formValues[$groupName.'__extraFields'][$optionKey])) {
											$formValues[$groupName.'__extraFields'][$optionKey] = array();
										}
										$formValues[$groupName.'__extraFields'][$optionKey][$extraField] = $tempEl->getValue();
										unset($tempEl);
									}
									$element[] = $row;
									unset($tempFb, $tempForm, $extraFieldDo, $row);
									$rowNames[] = '<label for="'.$crossLinkElement->getAttribute('id').'">'.$value.'</label>';
									$crossLinkElement->setText('');
								} elseif ($crossLink['collapse']) {
									$element[] = array(&$crossLinkElement);
									$rowNames[] = '<label for="'.$crossLinkElement->getAttribute('id').'">'.$value.'</label>';
									$crossLinkElement->setText('');
									$colNames = array();
								} else {
									$element[] =& $crossLinkElement;
								}
								unset($crossLinkElement);
							}
							if (isset($crossLinkDo->fb_crossLinkExtraFields) || $crossLink['collapse']) {
								$this->_form->_addElementGrid($groupName, array_values($colNames), $rowNames, $element);
							} else {
								$this->_form->_addElementGroup($element, $groupName, $this->crossLinkSeparator);
							}
							if ($crossLink['collapse']) {
								$this->_form->_collapseRecordList($groupName);
							}
							unset($element);
							unset($rowNames);
							unset($colNames);
						}
						break;
					case ($type & DB_DATAOBJECT_FORMBUILDER_TRIPLELINK):
						unset($element);
						/*if ($pk === false) {
						 return PEAR::raiseError('A primary key must exist in the base table when using tripleLinks.');
						 }*/
						$tripleLink = $this->tripleLinks[$key];
						$elName  = $this->_sanitizeFieldName('__tripleLink_'.$tripleLink['table'].
                                                                          '_'.$tripleLink['fromField'].
                                                                          '_'.$tripleLink['toField1'].
                                                                          '_'.$tripleLink['toField2']);
						$freeze = array_search($elName, $elements_to_freeze);
						unset($tripleLinkDo);
						$tripleLinkDo = DB_DataObject::factory($tripleLink['table']);
						if (PEAR::isError($tripleLinkDo)) {
							throw new Exception($tripleLinkDo->getMessage());
						}

						if (!is_array($tripleLinkLinks = $tripleLinkDo->links())) {
							$tripleLinkLinks = array();
						}

						list($linkedtable1, $linkedfield1) = explode(':', $tripleLinkLinks[$tripleLink['toField1']]);
						list($linkedtable2, $linkedfield2) = explode(':', $tripleLinkLinks[$tripleLink['toField2']]);
						list($fromtable, $fromfield) = explode(':', $tripleLinkLinks[$tripleLink['fromField']]);
						//if ($fromtable !== $this->_do->tableName()) error?
						$all_options1 = $this->_getSelectOptions($linkedtable1, false, false, false, $linkedfield1);
						$all_options2 = $this->_getSelectOptions($linkedtable2, false, false, false, $linkedfield2);
						$selected_options = array();
						if (isset($this->_do->$fromfield)) {
							$tripleLinkDo->{$tripleLink['fromField']} = $this->_do->$fromfield;
							if ($this->isCallableAndExists($this->prepareLinkedDataObjectCallback)) {
								call_user_func_array($this->prepareLinkedDataObjectCallback, array(&$tripleLinkDo, $key));
							}
							if ($tripleLinkDo->find() > 0) {
								while ($tripleLinkDo->fetch()) {
									$selected_options[$tripleLinkDo->{$tripleLink['toField1']}][] = $tripleLinkDo->{$tripleLink['toField2']};
								}
							}
						}
						$columnNames = array();
						foreach ($all_options2 as $key2 => $value2) {
							$columnNames[] = $value2;
						}
						$rows = array();
						$rowNames = array();
						$formValues[$key] = array();
						foreach ($all_options1 as $key1 => $value1) {
							$rowNames[] = $value1;
							$row = array();
							foreach ($all_options2 as $key2 => $value2) {
								unset($tripleLinkElement);
								$tripleLinkElement = $this->_form->_createCheckbox($elName.'['.$key1.']['.$key2.']',
                                                                                                '',
								$key2
								//false,
								//$freeze
								);
								if (isset($selected_options[$key1])) {
									if (in_array($key2, $selected_options[$key1])) {
										$tripleLinkName = '__tripleLink_'.$tripleLink['table'].
                                                         '_'.$tripleLink['fromField'].
                                                         '_'.$tripleLink['toField1'].
                                                         '_'.$tripleLink['toField2'];
										if (!isset($formValues[$tripleLinkName][$key1])) {
											$formValues[$tripleLinkName][$key1] = array();
										}
										$formValues[$tripleLinkName][$key1][$key2] = $key2;
									}
								}
								$row[] =& $tripleLinkElement;
							}
							$rows[] =& $row;
							unset($row);
						}
						$this->_form->_addElementGrid($elName, $columnNames, $rowNames, $rows);
						unset($columnNames, $rowNames, $rows);
						break;
					case ($type & DB_DATAOBJECT_FORMBUILDER_ENUM):
						if ($this->useAccessors
						&& method_exists($this->_do, 'get' . $key)) {
							$formValues[$key] = $this->_do->{'get'.$key}();
						} else {
							$formValues[$key] = $this->_do->$key;
						}
						if (!isset($element)) {
							$isRadio = isset($this->linkElementTypes[$key])
							&& $this->linkElementTypes[$key] == 'radio';
							if (isset($this->enumOptions[$key])) {
								$options = $this->enumOptions[$key];
							} else {
								if ($this->isCallableAndExists($this->enumOptionsCallback)) {
									$options = call_user_func($this->enumOptionsCallback, $this->_do->__table, $key);
								} else {
									$options =& PEAR::raiseError('enumOptionsCallback is an invalid callback');
								}
								if (PEAR::isError($options)) {
									return $options;
								}
							}
							/*if (array_keys($options) === range(0, count($options)-1)) {
							 $newOptions = array();
							 foreach ($options as $value) {
							 $newOptions[$value] = $value;
							 }
							 $options = $newOptions;
							 }*/
							if (in_array($key, $this->selectAddEmpty)
							|| !($type & DB_DATAOBJECT_NOTNULL) && !$isRadio) {
								$options = array('' => ($isRadio
								? $this->radioAddEmptyLabel
								: $this->selectAddEmptyLabel))
								+ $options;
							}
							if (!$options) {
								return PEAR::raiseError('There are no options defined for the enum field "'.$key.'". You may need to set the options in the enumOptions option or use your own enumOptionsCallback.');
							}
							$element = array();
							if ($isRadio) {
								$element =& $this->_form->_createRadioButtons($key, $options);
							} else {
								$element =& $this->_form->_createSelectBox($key, $options);
							}
							unset($options);
						}
						break;
					case ($type & DB_DATAOBJECT_FORMBUILDER_REVERSELINK):
						unset($element);
						$element = array();
						$elName = $this->_sanitizeFieldName('__reverseLink_'.$this->reverseLinks[$key]['table'].'_'.$this->reverseLinks[$key]['field']);
						unset($do);
						$do = DB_DataObject::factory($this->reverseLinks[$key]['table']);
						if ($this->isCallableAndExists($this->prepareLinkedDataObjectCallback)) {
							call_user_func_array($this->prepareLinkedDataObjectCallback, array(&$do, $key));
						}
						if (!is_array($rLinks = $do->links())) {
							$rLinks = array();
						}
						$rPk = $this->_getPrimaryKey($do);
						//$rFields = $do->table();
						list($lTable, $lField) = explode(':', $rLinks[$this->reverseLinks[$key]['field']]);
						$formValues[$elName] = array();
						if ($this->reverseLinks[$key]['collapse']) {
							$table = $rowNames = array();
						}
						if(is_array($do->fb_linkOrderFields)) {
							$orderStr = implode(', ',$do->fb_linkOrderFields);
							if ($orderStr) {
								$do->orderBy($orderStr);
							}
						}

						/*
						 if (isset($this->linkElementTypes[$elName])
						 && $this->linkElementTypes[$elName] == 'subForm') {
						 // Do this to find only reverseLinks with the correct foreign key.
						 $do->{$this->reverseLinks[$key]['field']} = $this->_do->{$this->_getPrimaryKey($this->_do)};
						 }
						 */
						if ($do->find()) {
							while ($do->fetch()) {
								$label = $this->getDataObjectString($do);
								if ($do->{$this->reverseLinks[$key]['field']} == $this->_do->$lField) {
									$formValues[$elName][$do->$rPk] = $do->$rPk;
								} elseif ($rLinked =& $do->getLink($this->reverseLinks[$key]['field'])) {
									$label .= '<b>'.$this->reverseLinks[$key]['linkText'].$this->getDataObjectString($rLinked).'</b>';
								}
								if (isset($this->linkElementTypes[$elName])
								&& $this->linkElementTypes[$elName] == 'subForm') {
									unset($subFB, $subForm, $subFormEl);
									$subFB =& DB_DataObject_FormBuilder::create($do,
									false,
                                                                                             'QuickForm',
									get_class($this));
									$this->reverseLinks[$key]['FBs'][] =& $subFB;
									$subFB->elementNamePrefix = $elName;
									$subFB->elementNamePostfix = '_'.count($this->reverseLinks[$key]['FBs']);
									$subFB->createSubmit = false;
									$subFB->formHeaderText = $this->getDataObjectString($do);//$this->getFieldLabel($elName).' '.count($this->reverseLinks[$key]['FBs']);
									$do->fb_linkNewValue = false;
									$subForm =& $subFB->getForm();
									$this->reverseLinks[$key]['SFs'][] = $subForm;
									$subFormEl =& $this->_form->_createSubForm($elName.count($this->reverseLinks[$key]['FBs']), null, $subForm);
									$element[] =& $subFormEl;
								} else {
									if ($this->reverseLinks[$key]['collapse']) {
										$table[] = array($this->_form->_createCheckbox($elName.'['.$do->$rPk.']', '', $do->$rPk));
										$rowNames[] = $label;
									} else {
										$element[] =& $this->_form->_createCheckbox($elName.'['.$do->$rPk.']', $label, $do->$rPk);
									}
								}
							}
						}
						if (isset($this->reverseLinkNewValue[$elName]) && $this->reverseLinkNewValue[$elName] !== false) {
							if (is_int($this->reverseLinkNewValue[$elName])) {
								$totalSubforms = $this->reverseLinkNewValue[$elName];
							} else {
								$totalSubforms = 1;
							}
							for ($i = 0; $i < $totalSubforms; $i++) {
								unset($subFB, $subForm, $subFormEl);
								// Add a subform to add a new reverseLink record.
								$do = DB_DataObject::factory($this->reverseLinks[$key]['table']);
								$do->{$lField} = $this->_do->{$this->_getPrimaryKey($this->_do)};
								$subFB =& DB_DataObject_FormBuilder::create($do,
								false,
                                                                                         'QuickForm',
								get_class($this));
								$this->reverseLinks[$key]['FBs'][] =& $subFB;
								$subFB->elementNamePrefix = $elName;
								$subFB->elementNamePostfix = '_'.count($this->reverseLinks[$key]['FBs']);
								$subFB->createSubmit = false;
								$subFB->formHeaderText = 'New '.(isset($do->fb_formHeaderText)
								? $do->fb_formHeaderText
								: $this->prettyName($do->__table));
								$do->fb_linkNewValue = false;
								$subForm =& $subFB->getForm();
								$this->reverseLinks[$key]['SFs'][] =& $subForm;
								$subFormEl =& $this->_form->_createSubForm($elName.count($this->reverseLinks[$key]['FBs']), null, $subForm);
								$element[] =& $subFormEl;
							}
						}
						if ($this->reverseLinks[$key]['collapse']) {
							$this->_form->_addElementGrid($elName, array(), $rowNames, $table);
							$this->_form->_collapseRecordList($elName);
						} else {
							$this->_form->_addElementGroup($element, $elName, $this->crossLinkSeparator);
						}
						unset($element);
						break;
					case ($type & DB_DATAOBJECT_FORMBUILDER_GROUP):
						unset($element);
						$element =& $this->_form->_createHiddenField($key.'__placeholder');
						break;
					default:
						if ($this->useAccessors
						&& method_exists($this->_do, 'get' . $key)) {
							$formValues[$key] = $this->_do->{'get'.$key}();
						} else {
							$formValues[$key] = $this->_do->$key;
						}
						if (!isset($element)) {
							$element =& $this->_form->_createTextField($key);
						}
				} // End switch
				//} // End else
				if ($elValidator !== false) {
					if (!isset($rules[$key])) {
						$rules[$key] = array();
					}
					$rules[$key][] = array('validator' => $elValidator,
                                                            'rule' => $elValidRule,
                                                            'message' => $this->ruleViolationMessage);
				} // End if

			} // End else

			//GROUP OR ELEMENT ADDITION
			if (isset($this->preDefGroups[$key]) && !($type & DB_DATAOBJECT_FORMBUILDER_GROUP)) {
				$group = $this->preDefGroups[$key];
				$groups[$group][] = $element;
			} elseif (isset($element)) {
				if (is_array($element)) {
					$this->_form->_addElementGroup($element, $key);
				} else {
					$this->_form->_addElement($element);
				}
			} // End if

			//SET AUTO-RULES IF NOT DEACTIVATED FOR THIS OR ALL ELEMENTS
			if (!$this->_excludeAllFromAutoRules
			&& !in_array($key, $this->excludeFromAutoRules)) {
				//ADD REQURED RULE FOR NOT_NULL FIELDS
				if ((!in_array($key, $keys)
				|| $this->hidePrimaryKey == false)
				&& ($type & DB_DATAOBJECT_NOTNULL)
				&& !in_array($key, $elements_to_freeze)
				&& !($type & DB_DATAOBJECT_BOOL)) {
					$this->_form->_setFormElementRequired($key);
					$this->debug('Adding required rule for '.$key);
				}

				// VALIDATION RULES
				if (isset($rules[$key])) {
					$this->_form->_addFieldRules($rules[$key], $key);
					$this->debug("Adding rule '$rules[$key]' to $key");
				}
			} else {
				$this->debug($key.' excluded from auto-rules');
			}
		} // End foreach

		if ($this->linkNewValue) {
			$this->_form->_addRuleForLinkNewValues();
		}

		// Freeze fields that are not to be edited by the user
		$this->_form->_freezeFormElements($elements_to_freeze);

		//GROUP SUBMIT
		$flag = true;
		if (isset($this->preDefGroups['__submit__'])) {
			$group = $this->preDefGroups['__submit__'];
			if (count($groups[$group]) > 1) {
				$groups[$group][] =& $this->_form->_createSubmitButton('__submit__', $this->submitText);
				$flag = false;
			} else {
				$flag = true;
			}
		}

		//GROUPING
		if (isset($groups) && is_array($groups)) { //apply grouping
			reset($groups);
			while (list($grp, $elements) = each($groups)) {
				if (count($elements) == 1) {
					$this->_form->_addElement($elements[0]);
					$this->_form->_moveElementBefore($this->_form->_getElementName($elements[0]), $grp.'__placeholder');
				} elseif (count($elements) > 1) {
					$this->_form->_addElementGroup($elements, $grp, '&nbsp;');
					$this->_form->_moveElementBefore($grp, $grp.'__placeholder');
				}
			}
		}

		//ELEMENT SUBMIT
		if ($flag == true && $this->createSubmit == true) {
			$this->_form->_addSubmitButton('__submit__', $this->submitText);
		}


		$this->_form->_finishForm();

		// Assign default values to the form
		$fixedFormValues = array();
		foreach ($formValues as $key => $value) {
			if(in_array($key,$elements_to_freeze) && isset($links[$key])) {
			} else {
				$fixedFormValues[$this->getFieldName($key)] = $value;

			}

		}
		$this->_form->_setFormDefaults($fixedFormValues);
		return $this->_form->getForm();
	}

	function populateOptions() {
		$badVars = array('linkDisplayFields', 'linkOrderFields');
		foreach (get_object_vars($this) as $var => $value) {
			if ($var[0] != '_' && !in_array($var, $badVars) && isset($this->_do->{'fb_'.$var})) {
				$this->$var = $this->_do->{'fb_'.$var};
			}
		}

		foreach ($this->crossLinks as $key => $crossLink) {
			if (!isset($crossLink['type'])) {
				$crossLink['type'] = 'radio';
			}
			if (!isset($crossLink['collapse'])) {
				$crossLink['collapse'] = false;
			}
			unset($do);
			$do = DB_DataObject::factory($crossLink['table']);
			if (PEAR::isError($do)) {
				throw new Exception('Cannot load dataobject for table '.$crossLink['table'].' - '.$do->getMessage());
			}

			if (!is_array($links = $do->links())) {
				$links = array();
			}

			if (isset($crossLink['fromField'])) {
				$fromField = $crossLink['fromField'];
			} else {
				unset($fromField);
			}
			if (isset($crossLink['toField'])) {
				$toField = $crossLink['toField'];
			} else {
				unset($toField);
			}
			if (!isset($toField) || !isset($fromField)) {
				foreach ($links as $field => $link) {
					list($linkTable, $linkField) = explode(':', $link);
					if (!isset($fromField) && $linkTable == $this->_do->__table) {
						$fromField = $field;
					} elseif (!isset($toField) && (!isset($fromField) || $linkField != $fromField)) {
						$toField = $field;
					}
				}
			}
			unset($this->crossLinks[$key]);
			if(is_numeric($key)) {
				$groupName  = $this->_sanitizeFieldName('__crossLink_'.$crossLink['table'].
                                                                       '_'.$fromField.
                                                                       '_'.$toField);
			} else {
				$groupName = $key;
			}
			$this->crossLinks[$groupName] = array_merge($crossLink,
			array('fromField' => $fromField,
                                                                               'toField' => $toField));
			foreach (array('preDefOrder', 'fieldsToRender', 'userEditableFields') as $arrName) {

				foreach ($this->{$arrName} as $key => $value) {
					if ($this->_sanitizeFieldName($value)
					== $this->_sanitizeFieldName($groupName)) {
						$this->{$arrName}[$key] = $groupName;
					}
				}
			}

			foreach (array('preDefElements', 'fieldLabels', 'fieldAttributes') as $arrName) {
				if (isset($this->{$arrName}[$this->_sanitizeFieldName('__crossLink_'.$crossLink['table'])])) {
					if (!isset($this->{$arrName}[$groupName])) {
						$this->{$arrName}[$groupName] =& $this->{$arrName}['__crossLink_'.$crossLink['table']];
					}
					unset($this->{$arrName}[$this->_sanitizeFieldName('__crossLink_'.$crossLink['table'])]);
				}
			}
		}

		foreach ($this->tripleLinks as $key => $tripleLink) {
			//$freeze = array_search($elName, $elements_to_freeze);
			unset($do);
			$do = DB_DataObject::factory($tripleLink['table']);
			if (PEAR::isError($do)) {
				throw new Exception($do->getMessage());
			}
			if (!is_array($links = $do->links())) {
				$links = array();
			}

			if (isset($tripleLink['fromField'])) {
				$fromField = $tripleLink['fromField'];
			} else {
				unset($fromField);
			}
			if (isset($tripleLink['toField1'])) {
				$toField1 = $tripleLink['toField1'];
			} else {
				unset($toField1);
			}
			if (isset($tripleLink['toField2'])) {
				$toField2 = $tripleLink['toField2'];
			} else {
				unset($toField2);
			}
			if (!isset($toField2) || !isset($toField1) || !isset($fromField)) {
				foreach ($links as $field => $link) {
					list($linkTable, $linkField) = explode(':', $link);
					if (!isset($fromField) && $linkTable == $this->_do->__table) {
						$fromField = $field;
					} elseif (!isset($toField1) && (!isset($fromField) || $linkField != $fromField)) {
						$toField1 = $field;
					} elseif (!isset($toField2) && (!isset($fromField) || $linkField != $fromField) && $linkField != $toField1) {
						$toField2 = $field;
					}
				}
			}
			unset($this->tripleLinks[$key]);
			$elName  = $this->_sanitizeFieldName('__tripleLink_' . $tripleLink['table'].
                                                                  '_'.$fromField.
                                                                  '_'.$toField1.
                                                                  '_'.$toField2);
			$this->tripleLinks[$elName] = array_merge($tripleLink,
			array('fromField' => $fromField,
                                                                             'toField1' => $toField1,
                                                                             'toField2' => $toField2));
			foreach (array('preDefOrder', 'fieldsToRender', 'userEditableFields') as $arrName) {
				foreach ($this->{$arrName} as $key => $value) {
					if ($this->_sanitizeFieldName($value)
					== $this->_sanitizeFieldName('__tripleLink_'.$tripleLink['table'])) {
						$this->{$arrName}[$key] = $elName;
					}
				}
			}
			foreach (array('preDefElements', 'fieldLabels', 'fieldAttributes') as $arrName) {
				if (isset($this->{$arrName}[$this->_sanitizeFieldName('__tripleLink_'.$tripleLink['table'])])) {
					if (!isset($this->{$arrName}[$elName])) {
						$this->{$arrName}[$elName] =& $this->{$arrName}[$this->_sanitizeFieldName('__tripleLink_'.$tripleLink['table'])];
					}
					unset($this->{$arrName}[$this->_sanitizeFieldName('__tripleLink_'.$tripleLink['table'])]);
				}
			}
		}
		foreach ($this->reverseLinks as $key => $reverseLink) {
			if (!isset($reverseLink['field'])) {
				unset($do);
				$do = DB_DataObject::factory($reverseLink['table']);
				if (!is_array($links = $do->links())) {
					$links = array();
				}
				foreach ($links as $field => $link) {
					list($linkTable, $linkField) = explode(':', $link);
					if ($linkTable == $this->_do->__table) {
						$reverseLink['field'] = $field;
						break;
					}
				}
			}
			$elName  = $this->_sanitizeFieldName('__reverseLink_'.$reverseLink['table'].
                                                                  '_'.$reverseLink['field']);
			if (!isset($reverseLink['linkText'])) {
				$reverseLink['linkText'] = ' - currently linked to - ';
			}
			if (!isset($reverseLink['collapse'])) {
				$reverseLink['collapse'] = false;
			}
			unset($this->reverseLinks[$key]);
			$this->reverseLinks[$elName] = $reverseLink;
			foreach (array('preDefOrder', 'fieldsToRender', 'userEditableFields') as $arrName) {
				foreach ($this->{$arrName} as $key => $value) {
					if ($this->_sanitizeFieldName($value)
					== $this->_sanitizeFieldName('__reverseLink_'.$reverseLink['table'])) {
						$this->{$arrName}[$key] = $elName;
					}
				}
			}
			foreach (array('preDefElements', 'fieldLabels', 'fieldAttributes', 'reverseLinkNewValue') as $arrName) {
				if (isset($this->{$arrName}[$this->_sanitizeFieldName('__reverseLink_'.$reverseLink['table'])])) {
					if (!isset($this->{$arrName}[$elName])) {
						$this->{$arrName}[$elName] =& $this->{$arrName}[$this->_sanitizeFieldName('__reverseLink_'.$reverseLink['table'])];
					}
					unset($this->{$arrName}[$this->_sanitizeFieldName('__reverseLink_'.$reverseLink['table'])]);
				}
			}
		}
		if (is_array($this->linkNewValue)) {
			$newArr = array();
			foreach ($this->linkNewValue as $field) {
				$newArr[$field] = $field;
			}
			$this->linkNewValue = $newArr;
		} else {
			if ($this->linkNewValue) {
				$this->linkNewValue = array();
				if (is_array($links = $this->_do->links())) {
					foreach ($links as $link => $to) {
						$this->linkNewValue[$link] = $link;
					}
				}
			} else {
				$this->linkNewValue = array();
			}
		}
		if (!is_array($this->reverseLinkNewValue)) {
			if ($new = $this->reverseLinkNewValue) {
				$this->reverseLinkNewValue = array();
				if (is_array($this->reverseLinks)) {
					foreach ($this->reverseLinks as $key => $reverseLink) {
						$this->reverseLinkNewValue['__reverseLink_'.$reverseLink['table'].'_'.$reverseLink['field']] = $new;
					}
				}
			} else {
				$this->reverseLinkNewValue = array();
			}
		}
		if (is_array($this->excludeFromAutoRules)
		&& in_array('__ALL__', $this->excludeFromAutoRules)
		|| '__ALL__' == $this->excludeFromAutoRules) {
			$this->excludeFromAutoRules = array();
			$this->_excludeAllFromAutoRules = true;
		}

		$this->_form->populateOptions();
	}
	function _getSpecialElementNames() {
		$ret = array();
		foreach ($this->tripleLinks as $tripleLink) {
			$ret[$this->_sanitizeFieldname('__tripleLink_'.$tripleLink['table'].
                                                             '_'.$tripleLink['fromField'].
                                                             '_'.$tripleLink['toField1'].
                                                             '_'.$tripleLink['toField2'])]
			= DB_DATAOBJECT_FORMBUILDER_TRIPLELINK;
		}
		foreach ($this->crossLinks as $key=>$crossLink) {
			$ret[$key]
			= DB_DATAOBJECT_FORMBUILDER_CROSSLINK;
		}
		foreach ($this->reverseLinks as $reverseLink) {
			$ret[$this->_sanitizeFieldName('__reverseLink_'.$reverseLink['table'].
                                                             '_'.$reverseLink['field'])]
			= DB_DATAOBJECT_FORMBUILDER_REVERSELINK;
		}
		foreach ($this->preDefGroups as $group) {
			$ret[$group] = DB_DATAOBJECT_FORMBUILDER_GROUP;
		}
		return $ret;
	}
	function processForm($values)
	{
		$origDo = clone($this->_do);
		if ($this->elementNamePrefix !== '' || $this->elementNamePostfix !== '') {
			$origValues = $values;
			$values = $this->_getMyValues($values);
		}
		$this->debug('<br>...processing form data...<br>');
		if ($this->isCallableAndExists($this->preProcessFormCallback)) {
			call_user_func_array($this->preProcessFormCallback, array(&$values, &$this));
		}
		$editableFields = array_intersect($this->_getUserEditableFields(),
		array_keys($this->_getFieldsToRender()));

		$tableFields = $this->_do->table();
		if (!is_array($links = $this->_do->links())) {
			$links = array();
		}
		foreach ($values as $field => $value) {
			$this->debug('Field '.$field.' ');
			// Double-check if the field may be edited by the user... if not, don't
			// set the submitted value, it could have been faked!
			if (in_array($field, $editableFields)) {
				if (isset($tableFields[$field])) {
					if (($tableFields[$field] & DB_DATAOBJECT_DATE) || in_array($field, $this->dateFields)) {
						$this->debug('DATE CONVERSION for using callback from '.$value.' ...');
						if ($this->isCallableAndExists($this->dateToDatabaseCallback)) {
							$value = call_user_func($this->dateToDatabaseCallback, $value);
						} else {
							$this->debug('WARNING: dateToDatabaseCallback not callable', 'FormBuilder');
						}
					} elseif (($tableFields[$field] & DB_DATAOBJECT_TIME) || in_array($field, $this->timeFields)) {
						$this->debug('TIME CONVERSION for using callback from '.$value.' ...');
						if ($this->isCallableAndExists($this->dateToDatabaseCallback)) {
							$value = call_user_func($this->dateToDatabaseCallback, $value);
						} else {
							$this->debug('WARNING: dateToDatabaseCallback not callable', 'FormBuilder');
						}
					} elseif (is_array($value)) {
						if (isset($value['tmp_name'])) {
							$this->debug(' (converting file array) ');
							$value = $value['name'];
							//JUSTIN
							//This is not really a valid assumption IMHO. This should only be done if the type is
							// date or the field is in dateFields
							/*} else {
							$this->debug("DATE CONVERSION using callback from $value ...");
							$value = call_user_func($this->dateToDatabaseCallback, $value);*/
						}
					}
					if (isset($links[$field])) {
						if ($value == $this->linkNewValueText && $tableFields[$field] & DB_DATAOBJECT_INT) {
							$value = 0;
						} elseif ($value === '') {
							$this->debug('Casting to NULL');
							require_once('DB/DataObject/Cast.php');
							$value = DB_DataObject_Cast::sql('NULL');
						}
					}
					$this->debug('is substituted with "'.print_r($value, true).'".<br/>');

					// See if a setter method exists in the DataObject - if so, use that one
					if ($this->useMutators
					&& method_exists($this->_do, 'set' . $field)) {
						$this->_do->{'set'.$field}($value);
					} else {
						// Otherwise, just set the property 'normally'...
						$this->_do->$field = $value;
					}
				} else {
					$this->debug('is not a valid field.<br/>');
				}
			} else {
				$this->debug('is defined not to be editable by the user!<br/>');
			}
		}
		foreach ($this->booleanFields as $boolField) {
			if (in_array($boolField, $editableFields)
			&& !isset($values[$boolField])) {
				if ($this->useMutators
				&& method_exists($this->_do, 'set' . $boolField)) {
					$this->_do->{'set'.$boolField}(0);
				} else {
					$this->_do->$boolField = 0;
				}
			}
		}
		foreach ($tableFields as $field => $type) {
			if (($type & DB_DATAOBJECT_BOOL)
			&& in_array($field, $editableFields)
			&& !isset($values[$field])) {
				if ($this->useMutators
				&& method_exists($this->_do, 'set' . $field)) {
					$this->_do->{'set'.$field}(0);
				} else {
					$this->_do->$field = 0;
				}
			}
		}

		$dbOperations = true;
		if ($this->validateOnProcess === true) {
			$this->debug('Validating data... ');
			if (is_array($errors = $this->validateData())) {
				$dbOperations = false;
			}
		}

		$pk = $this->_getPrimaryKey($this->_do);

		// Data is valid, let's store it!
		if ($dbOperations) {

			//take care of linkNewValues
			/*if (isset($values['__DB_DataObject_FormBuilder_linkNewValue_'])) {
			foreach ($values['__DB_DataObject_FormBuilder_linkNewValue_'] as $elName => $subTable) {*/
			if (isset($this->_form->_linkNewValueForms)) {
				foreach (array_keys($this->_form->_linkNewValueForms) as $elName) {
					$subTable = $this->_form->_linkNewValueDOs[$elName]->tableName();
					if (isset($values['__DB_DataObject_FormBuilder_linkNewValue__'.$elName])) {
						if ($values[$elName] == $this->linkNewValueText) {
							//$this->_form->_prepareForLinkNewValue($elName, $subTable);
							$ret = $this->_form->_linkNewValueForms[$elName]->process(array(&$this->_form->_linkNewValueFBs[$elName], 'processForm'), false);
							if (PEAR::isError($ret)) {
								$this->debug('Error processing linkNewValue for '.serialize($this->_form->_linkNewValueDOs[$elName]));
								throw new Exception('Error processing linkNewValue - Error from processForm: '.$ret->getMessage(),
								null,
								null,
								null,
								$this->_form->_linkNewValueDOs[$elName]);
							}
							$subPk = $this->_form->_linkNewValueFBs[$elName]->_getPrimaryKey($this->_form->_linkNewValueDOs[$elName]);
							$this->_do->$elName = $values[$elName] = $this->_form->_linkNewValueDOs[$elName]->$subPk;
						}
					}
				}
			}

			$action = $this->_queryType;
			if ($this->_queryType == DB_DATAOBJECT_FORMBUILDER_QUERY_AUTODETECT) {
				// Could the primary key be detected?
				if ($pk === false) {
					// Nope, so let's exit and return false. Sorry, you can't store data using
					// processForm with this DataObject unless you do some tweaking :-(
					$this->debug('Primary key not detected - storing data not possible.');
					return false;
				}

				$action = DB_DATAOBJECT_FORMBUILDER_QUERY_FORCEUPDATE;
				if (!isset($this->_do->$pk) || !strlen($this->_do->$pk)) {
					$action = DB_DATAOBJECT_FORMBUILDER_QUERY_FORCEINSERT;
				}
			}

			switch ($action) {
				case DB_DATAOBJECT_FORMBUILDER_QUERY_FORCEINSERT:
					if (false === ($id = $this->_do->insert())) {
						$this->debug('Insert of main record failed');
						return $this->_raiseDoError('Insert of main record failed', $this->_do);
					}
					$this->debug('ID ('.$pk.') of the new object: '.$id.'<br/>');
					break;
				case DB_DATAOBJECT_FORMBUILDER_QUERY_FORCEUPDATE:

					if (false === $this->_do->update($origDo)) {
						$this->debug('Update of main record failed');
						return $this->_raiseDoError('Update of main record failed', $this->_do);

					}
					$this->debug('Object updated.<br/>');
					break;
			}

			// process tripleLinks
			foreach ($this->tripleLinks as $tripleLink) {
				$tripleLinkName = $this->_sanitizeFieldName('__tripleLink_'.$tripleLink['table'].
                                                                               '_'.$tripleLink['fromField'].
                                                                               '_'.$tripleLink['toField1'].
                                                                               '_'.$tripleLink['toField2']);
				if (in_array($tripleLinkName, $editableFields)) {
					unset($do);
					$do = DB_DataObject::factory($tripleLink['table']);

					$fromField = $tripleLink['fromField'];
					$toField1 = $tripleLink['toField1'];
					$toField2 = $tripleLink['toField2'];

					if (isset($values[$tripleLinkName])) {
						$rows = $values[$tripleLinkName];
					} else {
						$rows = array();
					}
					$links = $do->links();
					list ($linkTable, $linkField) = explode(':', $links[$fromField]);
					$do->$fromField = $this->_do->$linkField;
					$do->selectAdd();
					$do->selectAdd($toField1);
					$do->selectAdd($toField2);
					if ($doKey = $this->_getPrimaryKey($do)) {
						$do->selectAdd($doKey);
					}
					if ($this->isCallableAndExists($this->prepareLinkedDataObjectCallback)) {
						call_user_func_array($this->prepareLinkedDataObjectCallback, array(&$do, $tripleLinkName));
					}
					$oldFieldValues = array();
					if ($do->find()) {
						while ($do->fetch()) {
							if (isset($rows[$do->$toField1]) && isset($rows[$do->$toField1][$do->$toField2])) {
								$oldFieldValues[$do->$toField1][$do->$toField2] = true;
							} else {
								if (false === $do->delete()) {
									$this->debug('Failed to delete tripleLink '.serialize($do));
									return $this->_raiseDoError('Failed to delete tripleLink', $do);
								}
							}
						}
					}

					if (count($rows) > 0) {
						foreach ($rows as $rowid => $row) {
							if (count($row) > 0) {
								foreach ($row as $fieldvalue => $on) {
									if (!isset($oldFieldValues[$rowid]) || !isset($oldFieldValues[$rowid][$fieldvalue])) {
										unset($do);
										$do = DB_DataObject::factory($tripleLink['table']);
										$do->$fromField = $this->_do->$linkField;
										$do->$toField1 = $rowid;
										$do->$toField2 = $fieldvalue;
										if (false === $do->insert()) {
											$this->debug('Failed to insert tripleLink '.serialize($do));
											return $this->_raiseDoError('Failed to insert tripleLink', $do);
										}
									}
								}
							}
						}
					}
				}
			}

			//process crossLinks
			foreach ($this->crossLinks as $key=>$crossLink) {

				$crossLinkName = $key;

				if (in_array($crossLinkName, $editableFields)) {

					unset($do);
					$do = DB_DataObject::factory($crossLink['table']);

					$fromField = $crossLink['fromField'];
					$toField = $crossLink['toField'];

					if (isset($values[$crossLinkName])) {
						if ($crossLink['type'] == 'select') {
							$fieldvalues = array();
							foreach ($values[$crossLinkName] as $value) {
								$fieldvalues[$value] = $value;
							}
						} else {
							$fieldvalues = $values[$crossLinkName];
						}
					} else {
						$fieldvalues = array();
					}

					/*if (isset($values['__crossLink_'.$crossLink['table'].'__extraFields'])) {
					 $extraFieldValues = $values['__crossLink_'.$crossLink['table'].'__extraFields'];
					 } else {
					 $extraFieldValues = array();
					 }*/

					$links = $do->links();
					list ($linkTable, $linkField) = explode(':', $links[$fromField]);
					$do->$fromField = $this->_do->$linkField;
					$do->selectAdd();
					$do->selectAdd($toField);
					$do->selectAdd($fromField);
					if ($doKey = $this->_getPrimaryKey($do)) {
						$do->selectAdd($doKey);
					}
					if ($this->isCallableAndExists($this->prepareLinkedDataObjectCallback)) {
						call_user_func_array($this->prepareLinkedDataObjectCallback, array(&$do, $crossLinkName));
					}
					$oldFieldValues = array();
					if ($do->find()) {
						while ($do->fetch()) {
							if (isset($fieldvalues[$do->$toField])) {
								$oldFieldValues[$do->$toField] = clone($do);
							} else {
								if (false === $do->delete()) {
									$this->debug('Failed to delete crossLink '.serialize($do));
									return $this->_raiseDoError('Failed to delete crossLink', $do);
								}
							}
						}
					}

					if (count($fieldvalues) > 0) {

						foreach ($fieldvalues as $fieldvalue => $on) {
							$crossLinkPrefix = $this->elementNamePrefix.$crossLinkName.'__'.$fieldvalue.'_';
							$crossLinkPostfix = '_'.$this->elementNamePostfix;
							if (isset($oldFieldValues[$fieldvalue])) {
								if (isset($do->fb_crossLinkExtraFields)
								&& (!isset($crossLink['type']) || ($crossLink['type'] !== 'select'))) {
									$ret = $this->_extraFieldsFb[$crossLinkPrefix.$crossLinkPostfix]->processForm(isset($origValues)
									? $origValues
									: $values);
									if (PEAR::isError($ret)) {
										$this->debug('Failed to process extraFields for crossLink '.serialize($do));
										throw new Exception('Failed to process extraFields crossLink - Error from processForm: '
										.$ret->getMessage()
										, null, null, null, $do);
									}
								}
							} else {
								if (isset($do->fb_crossLinkExtraFields)
								&& (!isset($crossLink['type']) || ($crossLink['type'] !== 'select'))) {
									$insertValues = isset($origValues) ? $origValues : $values;
									$insertValues[$crossLinkPrefix.$fromField.$crossLinkPostfix] = $this->_do->$linkField;
									$insertValues[$crossLinkPrefix.$toField.$crossLinkPostfix] = $fieldvalue;
									$this->_extraFieldsFb[$crossLinkPrefix.$crossLinkPostfix]->fieldsToRender[] = $fromField;
									$this->_extraFieldsFb[$crossLinkPrefix.$crossLinkPostfix]->fieldsToRender[] = $toField;
									$ret = $this->_extraFieldsFb[$crossLinkPrefix.$crossLinkPostfix]->processForm($insertValues);
									if (PEAR::isError($ret)) {
										$this->debug('Failed to process extraFields for crossLink '.serialize($do));
										throw new Exception('Failed to process extraFields crossLink - Error from processForm: '
										.$ret->getMessage()
										, null, null, null, $do);
									}
								} else {
									unset($do);
									$do = DB_DataObject::factory($crossLink['table']);
									$do->$fromField = $this->_do->$linkField;
									$do->$toField = $fieldvalue;
									if (false === $do->insert()) {
										$this->debug('Failed to insert crossLink '.serialize($do));
										return $this->_raiseDoError('Failed to insert crossLink', $do);
									}
								}
							}
						}
					}
				}
			}

			foreach ($this->reverseLinks as $reverseLink) {
				$elName = $this->_sanitizeFieldName('__reverseLink_'.$reverseLink['table'].'_'.$reverseLink['field']);

				if (in_array($elName, $editableFields)) {
					// Check for subforms
					if (isset($this->linkElementTypes[$elName])
					&& $this->linkElementTypes[$elName] == 'subForm') {
						foreach($reverseLink['SFs'] as $sfkey => $subform) {
							// Process each subform that was rendered.
							if ($subform->validate()) {
								$ret = $subform->process(array(&$reverseLink['FBs'][$sfkey], 'processForm'), false);
								if (PEAR::isError($ret)) {
									$this->debug('Failed to process subForm for reverseLink '.serialize($reverseLink['FBs'][$sfkey]->_do));
									throw new Exception('Failed to process extraFields crossLink - Error from processForm: '
									.$ret->getMessage()
									, null, null, null, $reverseLink['FBs'][$sfkey]->_do);
								}
							}
						}
					} else {
						unset($do);
						$do = DB_DataObject::factory($reverseLink['table']);
						if ($this->isCallableAndExists($this->prepareLinkedDataObjectCallback)) {
							call_user_func_array($this->prepareLinkedDataObjectCallback, array(&$do, $key));
						}
						if (!is_array($rLinks = $do->links())) {
							$rLinks = array();
						}
						$rPk = $this->_getPrimaryKey($do);
						$rFields = $do->table();
						list($lTable, $lField) = explode(':', $rLinks[$reverseLink['field']]);
						if ($do->find()) {
							while ($do->fetch()) {
								unset($newVal);
								if (isset($values[$elName][$do->$rPk])) {
									if ($do->{$reverseLink['field']} != $this->_do->$lField) {
										$do->{$reverseLink['field']} = $this->_do->$lField;
										if (false === $do->update()) {
											$this->debug('Failed to update reverseLink '.serialize($do));
											return $this->_raiseDoError('Failed to update reverseLink', $do);
										}
									}
								} elseif ($do->{$reverseLink['field']} == $this->_do->$lField) {
									if (isset($reverseLink['defaultLinkValue'])) {
										$do->{$reverseLink['field']} = $reverseLink['defaultLinkValue'];
										if (false === $do->update()) {
											$this->debug('Failed to update reverseLink '.serialize($do));
											return $this->_raiseDoError('Failed to update reverseLink', $do);
										}
									} else {
										if ($rFields[$reverseLink['field']] & DB_DATAOBJECT_NOTNULL) {
											//ERROR!!
											$this->debug('Checkbox in reverseLinks unset when link field may not be null');
										} else {
											require_once('DB/DataObject/Cast.php');
											$do->{$reverseLink['field']} = DB_DataObject_Cast::sql('NULL');
											if (false === $do->update()) {
												$this->debug('Failed to update reverseLink '.serialize($do));
												return $this->_raiseDoError('Failed to update reverseLink', $do);
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}

		if ($this->isCallableAndExists($this->postProcessFormCallback)) {
			call_user_func_array($this->postProcessFormCallback, array(&$values, &$this));
		}

		return $dbOperations;
	}
}

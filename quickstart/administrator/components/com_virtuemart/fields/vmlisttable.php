<?php
defined('_JEXEC') or die();

/**
 * Creates a dropdown select list by a given model and function
 *
 * @package    VirtueMart
 * @subpackage Fields
 * @author Max Milbers
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2016 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: $
 */

JFormHelper::loadFieldClass('list');
jimport('joomla.form.formfield');

class JFormFieldVmListTable extends JFormFieldList {

	/**
	 * Element name
	 * @access    protected
	 * @var        string
	 */
	var $type = 'vmListTable';

	protected function getInput() {

		return parent::getInput();
	}
	protected function getOptions() {
		$options = array();
		VmConfig::loadConfig();
		vmLanguage::loadJLang('com_virtuemart');

		$this->model = $this->getAttribute('model',false);
		$this->func = $this->getAttribute('func',false);
		if(!$this->model or !$this->func){
			return parent::getOptions();
		}
		$m = VmModel::getModel($this->model);
		if(!$m){
			return parent::getOptions();
		}
		$values = call_user_func(array($m,$this->func));


		if(!$this->multiple) $options[] .=  JHtml::_('select.option', 0, vmText::_('COM_VIRTUEMART_FORM_TOP_LEVEL'));

		$lvalue = $this->getAttribute('lvalue','value');
		$ltext = $this->getAttribute('ltext','text');

		foreach ($values as $v) {
			$options[] = JHtml::_('select.option', $v->$lvalue, $v->$ltext);
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		if(!is_array($this->value))$this->value = array($this->value);

		if($this->multiple){
			$this->multiple = ' multiple="multiple" ';
		}

		return $options;
	}
}
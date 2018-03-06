<?php
/**
 *
 * @author Jeremy Magne
 * @version $Id$
 * @package VirtueMart
 * @subpackage payment
 * Copyright (C) 2004-2015 Virtuemart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */


defined('_JEXEC') or die();

jimport('joomla.form.formfield');

defined('DS') or define('DS', DIRECTORY_SEPARATOR);
if (!class_exists( 'VmConfig' )) require(JPATH_ROOT .'/administrator/components/com_virtuemart/helpers/config.php');

class JFormFieldVmtitle extends JFormField {

	public $type = 'Vmtitle';

	protected function getLabel()
	{

		$description = $this->element['description'];

		VmConfig::loadConfig();

		$html = '';
		$class = !empty($this->class)? 'class="' .  $this->class . '"' : '';
		if (empty($class)) {
			$class.="style=\"font-weight: bold; padding: 5px; background-color: #cacaca; float:none; clear:both;\"";
		}
		if ($this->value) {

			$html .= '<div ' . $class . '>';
			$html .= vmText::_($this->value);
			$html .= '</div>';

		}

		return $html;
	}

	protected function getInput()
	{
		if (empty($this->element['description'])) {
			 return '';
		}

		$description = (string)$this->element['description'];
		$class = $this->element['class'] ? ' class="' . trim((string)$this->element['class']) . '"' : '';

		$html = !empty($description) ? vmText::_($description) : '';

		return '<span ' . $class . '>' . $html . '</span>';
	}

}
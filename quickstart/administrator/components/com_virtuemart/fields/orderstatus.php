<?php
defined ('_JEXEC') or die();
/**
 *
 * @package    VirtueMart
 * @subpackage Plugins  - Elements
 * @author Valérie Isaksen
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2011 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id:$
 */

class JFormFieldOrderstatus extends JFormField {
	var $type = 'orderstatus';
	function getInput () {
		
		if (!class_exists( 'VmConfig' )) require(JPATH_ROOT .'/administrator/components/com_virtuemart/helpers/config.php');

		VmConfig::loadConfig ();
		vmLanguage::loadJLang('com_virtuemart');
		$key = ($this->element['key_field'] ? $this->element['key_field'] : 'value');
		$val = ($this->element['value_field'] ? $this->element['value_field'] : $this->name);
		$model = VmModel::getModel ('Orderstatus');
		$orderStatus = $model->getOrderStatusList (true);
		foreach ($orderStatus as $orderState) {
			$orderState->order_status_name = vmText::_ ($orderState->order_status_name);
		}
		return JHtml::_ ('select.genericlist', $orderStatus, $this->name, 'class="inputbox" multiple="true" size="1"', 'order_status_code', 'order_status_name', $this->value, $this->id);
	}

}
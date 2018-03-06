<?php
defined("_JEXEC") or die("Direct Access to " . basename(__FILE__) . "is not allowed.");

/**
 *
 * @package    VirtueMart
 * @subpackage Plugins  _ Elements
 * @author Valérie Isaksen
 * @package VirtueMart
 * @copyright Copyright (c) 2004 - 2012 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: $
 */
JFormHelper::loadFieldClass('list');
jimport('joomla.form.formfield');

class JFormFieldSubscribe extends JFormFieldList
{

	var $type = "Subscribe";

	function getOptions()
	{

		$query = "
          SELECT virtuemart_custom_id, custom_title
          FROM #__virtuemart_customs
		";

		$db = JFactory::getDBO();
		$db->setQuery($query);
		$customFieldList = $db->loadObjectList();

		foreach ($customFieldList as $v) {
			$options[] = JHtml::_('select.option', $v->value, $v->text);
		}

		return $options;
	}

}
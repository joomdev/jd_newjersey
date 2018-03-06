<?php
defined("_JEXEC") or die("Direct Access to " . basename(__FILE__) . "is not allowed.");

/**
 *
 * @package    VirtueMart
 * @subpackage Plugins  _ Elements
 * @author Valérie Isaksen
 * @link http://www.alatak.net
 * @copyright Copyright (c) 2004 - November 21 2017 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: $
 */
JFormHelper::loadFieldClass('list');
jimport('joomla.form.formfield');

class JFormFieldpayboxsubscribe extends JFormFieldList {

	/**
	 * Element name
	 *
	 * @access    protected
	 * @var        string
	 */
	var $type = "payboxsubscribe";

	function getOptions() {

		$query = "
          SELECT virtuemart_custom_id, custom_title
          FROM #__virtuemart_customs
          WHERE (field_type = 'P')
          AND (custom_title LIKE '%" . $this->_name . "%')
";

		$db = JFactory::getDBO();
		$db->setQuery($query);

		$values = $db->loadObjectList();

		foreach ($values as $v) {
			$options[] = JHtml::_('select.option', $v->value, $v->text);
		}


		return $options;
	}

}
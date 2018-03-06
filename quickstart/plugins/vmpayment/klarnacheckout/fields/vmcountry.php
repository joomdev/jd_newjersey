<?php
defined('_JEXEC') or die();

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
 * @version $Id: vmcountry.php 9413 2017-01-04 17:20:58Z Milbo $
 */

JFormHelper::loadFieldClass('list');
jimport('joomla.form.formfield');
class JFormFieldVmCountry extends JFormFieldList {

	var $type = 'vmcountry';

	function getOptions() {

		$db = JFactory::getDBO();

		$query = 'SELECT `virtuemart_country_id` AS value, `country_name` AS text FROM `#__virtuemart_countries`
               		WHERE `published` = 1 ORDER BY `country_name` ASC ';

		$db->setQuery($query);
		$values = $db->loadObjectList();

		foreach ($values as $v) {
			$options[] = JHtml::_('select.option', $v->value, $v->text);
		}


		return $options;
	}

}
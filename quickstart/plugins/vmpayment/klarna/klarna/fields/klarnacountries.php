<?php  defined('_JEXEC') or die();

/**
 * @version $Id: klarnacountries.php 6369 2012-08-22 14:33:46Z alatak $
 *
 * @author Valérie Isaksen
 * @package VirtueMart
 * @copyright Copyright (c) 2004 - 2012 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
JFormHelper::loadFieldClass('list');
jimport('joomla.form.formfield');
class JFormFieldKlarnaCountries extends JFormFieldList {

	/**
	 * Element name
	 * @access    protected
	 * @var        string
	 */
	var $type = 'klarnacountries';

	protected function getInput() {
		$this->multiple = true;
		return parent::getInput();
	}

	protected function getOptions() {
		$db = JFactory::getDBO();
		$klarna_countries = '"se", "de", "dk", "nl", "fi", "no"';
		$query = 'SELECT `country_3_code` AS value, `country_name` AS text FROM `#__virtuemart_countries`
               		WHERE `published` = 1 AND `country_2_code` IN (' . $klarna_countries . ') ORDER BY `country_name` ASC ';

		$db->setQuery($query);
		$values = $db->loadObjectList();

		foreach ($values as $v) {
			$options[] = JHtml::_('select.option', $v->value, $v->text);
		}


		return $options;
	}

}
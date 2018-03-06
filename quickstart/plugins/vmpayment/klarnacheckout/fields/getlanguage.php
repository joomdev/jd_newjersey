<?php
defined('_JEXEC') or die();

/**
 *
 * @package	VirtueMart
 * @subpackage Plugins  - Elements
 * @author Valérie Isaksen
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2011 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: getlanguage.php 7301 2013-10-29 17:45:07Z alatak $
 */

JFormHelper::loadFieldClass('list');
jimport('joomla.form.formfield');
class JFormFieldGetlanguage extends JFormFieldList {

	var $type = 'getlanguage';

	function getOptions() {


	    $languages = JLanguage::getKnownLanguages();
	    $fields = array();

	    foreach ($languages as $language) {
		    $options[] = JHtml::_('select.option', strtolower($language['tag']),$language['name'].' ('.strtolower($language['tag'] .')'));
	    }
		return $options;
    }

}
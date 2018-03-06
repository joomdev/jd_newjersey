<?php
defined('_JEXEC') or die('Restricted access');

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
 * @version $Id$
 */
jimport('joomla.form.formfield');

class JFormFieldVmCurl extends JFormField {

	var $type = 'vmcurl';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput() {

		VmConfig::loadConfig();
		vmLanguage::loadJLang('com_virtuemart');

		$option = vRequest::getCmd('option');
		if (!function_exists('curl_init') or !function_exists('curl_exec')) {
			return vmText::_('COM_VIRTUEMART_PS_CURL_LIBRARY_NOT_INSTALLED');
		} else {

				$js = "
 jQuery(document).ready(function( $ ) {
   $( '#vmcurl' ) .closest('.control-group').hide();
 });
 ";

				$doc = JFactory::getDocument();
				$doc->addScriptDeclaration($js);
				return '<span id="vmcurl"></span>';

		}
	}

}
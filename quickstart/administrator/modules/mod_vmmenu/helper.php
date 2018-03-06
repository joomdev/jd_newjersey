<?php
/**
 *
 * @version $Id$
 * @package VirtueMart
 * @author Valérie Isaksen
 * @subpackage mod_vmmenu
 * @copyright Copyright (C) 2014 VirtueMart Team - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */

// no direct access
defined('_JEXEC') or die;
defined('DS') or define('DS', DIRECTORY_SEPARATOR);

abstract class ModVMMenuHelper {


	public static function getVMComponent($authCheck = true) {

		$lang	= JFactory::getLanguage();
		$user		= JFactory::getUser();

		$db = JFactory::getDBO();

		$q = 'SELECT m.id, m.title, m.alias, m.link, m.parent_id, m.img, e.element FROM `#__menu` as m
				LEFT JOIN #__extensions AS e ON m.component_id = e.extension_id
		         WHERE m.client_id = 1 AND e.enabled = 1 AND m.id > 1 AND e.element = \'com_virtuemart\' AND m.menutype="main"
		         AND (m.parent_id=1 OR m.parent_id =
			                        (SELECT m.id FROM `#__menu` as m
									LEFT JOIN #__extensions AS e ON m.component_id = e.extension_id
			                        WHERE m.parent_id=1 AND m.client_id = 1 AND e.enabled = 1 AND m.id > 1 AND e.element = \'com_virtuemart\' AND m.menutype="main"))
		         ORDER BY m.lft';
		$db->setQuery($q);

		$vmComponentItems = $db->loadObjectList();
		$result = new stdClass();
		if (!class_exists( 'VmConfig' )) require(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'config.php');
		VmConfig::loadConfig();

		if ($vmComponentItems) {

			if (!class_exists( 'VmConfig' )) require(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'config.php');
			vmLanguage::loadJLang('com_virtuemart.sys');
			// Parse the list of extensions.
			foreach ($vmComponentItems as &$vmComponentItem) {
				$vmComponentItem->link = vRequest::vmSpecialChars(trim($vmComponentItem->link));
				if ($vmComponentItem->parent_id == 1) {
					if ($authCheck == false || ($authCheck && $user->authorise('core.manage', $vmComponentItem->element))) {
						$result = $vmComponentItem;
						if (!isset($result->submenu)) {
							$result->submenu = array();
						}

						if (empty($vmComponentItem->link)) {
							$vmComponentItem->link = 'index.php?option=' . $vmComponentItem->element;
						}

						$vmComponentItem->text = $lang->hasKey($vmComponentItem->title) ? JText::_($vmComponentItem->title) : $vmComponentItem->alias;
					}
				} else {
					// Sub-menu level.
					if (isset($result)) {
						// Add the submenu link if it is defined.
						if (isset($result->submenu) && !empty($vmComponentItem->link)) {
							$vmComponentItem->text = $lang->hasKey($vmComponentItem->title) ? JText::_($vmComponentItem->title) : $vmComponentItem->alias;

							$class = preg_replace('#\.[^.]*$#', '', basename($vmComponentItem->img));
							$class = preg_replace('#\.\.[^A-Za-z0-9\.\_\- ]#', '', $class);
							if(JVM_VERSION<3){
								$vmComponentItem->class="icon-16-".$class;
							} else {
								$vmComponentItem->class='';
							}
							$result->submenu[] = & $vmComponentItem;
						}
					}
				}
			}

			$props = get_object_vars($result);
			if(!empty($props)){
				return $result;
			}
		}

		return false;

	}

}

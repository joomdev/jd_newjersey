<?php
defined('_JEXEC') or die('');
/**
 * Helper to handle the templates
 *
 * @package	VirtueMart
 * @subpackage Helpers
 * @author Max Milbers
 * @copyright Copyright (c) 2014 VirtueMart Team and author. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */
 

class VmTemplate {

	static $_templates = array();
	static $_home = array(false,false);


	public static function loadVmTemplateStyle() {

		static $res = null;
		if($res !== null) return $res;

		$vmtemplate = VmConfig::get( 'vmtemplate', 0 );
		if(empty($vmtemplate) or $vmtemplate == 'default') {
			$res = self::getDefaultTemplate();
			if(!$res) {
				$err = 'Not able to load default template';
				vmError( 'renderMail get Template failed: '.$err );
			}
		} else if(!empty($vmtemplate) and is_numeric( $vmtemplate )) {
			$res = self::getTemplateById( $vmtemplate );
		}

		if(!$res) {
			vmError( 'loadVmTemplateStyle failed: Could not load default template style');
		} else {
			return $res;
		}
	}

	/**
	 * @param int $client_id FE is 0, BE is 1
	 * @return bool
	 */
	public static function getDefaultTemplate($client_id = 0){

		if(self::$_home[$client_id]) return self::$_home[$client_id];
		$app = JFactory::getApplication();

		if(!$client_id and $app->isSite()){
			$template = $app->getTemplate(true);
			$template = (array) $template;
		} else {
			$q = 'SELECT id, home, template, s.params
			FROM #__template_styles as s
			LEFT JOIN #__extensions as e
			ON e.element=s.template
			AND e.type="template"
			AND e.client_id=s.client_id
			WHERE s.client_id = '.$client_id.'
			AND e.enabled = 1 AND s.home = 1';
			$db = JFactory::getDbo();
			$db->setQuery( $q );
			$template = $db->loadAssoc();
		}

		if(!$template){
			vmError( 'getDefaultTemplate failed ' );
			return false;
		} else {
			self::$_home[$client_id] = $template;
			self::$_templates[$template['id']] = $template;
			return self::$_templates[$template['id']];
		}
	}
	
	public static function getTemplateById($id){

		if(!isset(self::$_templates[$id])){
			$q = 'SELECT id, home,`template`,`params` FROM `#__template_styles` WHERE `id`="'.$id.'" ';
			$db = JFactory::getDbo();
			$db->setQuery($q);
			self::$_templates[$id] = $db->loadAssoc();
			if(!self::$_templates[$id]){
				vmError( 'getTemplateById get Template failed for id: '.$id );
			}
		}
		return self::$_templates[$id];
	}

	/**
	 * This function sets the right template on the view
	 * @author Max Milbers
	 */
	static function setVmTemplate ($view, $catTpl = 0, $prodTpl = 0, $catLayout = 0, $prodLayout = 0) {

		//Lets get here the template set in the shopconfig, if there is nothing set, get the joomla standard
		$template = VmConfig::get( 'vmtemplate', 0 );
		$db = JFactory::getDBO();
		//Set specific category template
		if(!empty($catTpl) && empty($prodTpl)) {
			if(is_Int( $catTpl )) {
				$q = 'SELECT `category_template` FROM `#__virtuemart_categories` WHERE `virtuemart_category_id` = "'.(int)$catTpl.'" ';
				$db->setQuery( $q );
				$temp = $db->loadResult();
				if(!empty($temp)) $template = $temp;
			} else {
				$template = $catTpl;
			}
		}

		//Set specific product template
		if(!empty($prodTpl)) {
			if(is_Int( $prodTpl )) {
				$q = 'SELECT `product_template` FROM `#__virtuemart_products` WHERE `virtuemart_product_id` = "'.(int)$prodTpl.'" ';
				$db->setQuery( $q );
				$temp = $db->loadResult();
				if(!empty($temp)) $template = $temp;
			} else {
				$template = $prodTpl;
			}
		}

		if( (!empty($template) and $template!='default') or JFactory::getApplication()->isAdmin()){
			self::setTemplate( $template );
		}

		//Lets get here the layout set in the shopconfig, if there is nothing set, get the joomla standard
		if(vRequest::getCmd( 'view' ) == 'virtuemart') {
			$layout = VmConfig::get( 'vmlayout', 'default' );
			$view->setLayout( strtolower( $layout ) );
		} else {

			if(empty($catLayout) and empty($prodLayout)) {
				$catLayout = VmConfig::get( 'productlayout', 'default' );
			}

			//Set specific category layout
			if(!empty($catLayout) && empty($prodLayout)) {
				if(is_Int( $catLayout )) {
					$q = 'SELECT `layout` FROM `#__virtuemart_categories` WHERE `virtuemart_category_id` = "'.(int)$catLayout.'" ';
					$db->setQuery( $q );
					$temp = $db->loadResult();
					if(!empty($temp)) $layout = $temp;
				} else {
					$layout = $catLayout;
				}
			}

			//Set specific product layout
			if(!empty($prodLayout)) {
				if(is_Int( $prodLayout )) {
					$q = 'SELECT `layout` FROM `#__virtuemart_products` WHERE `virtuemart_product_id` = "'.(int)$prodLayout.'" ';
					$db->setQuery( $q );
					$temp = $db->loadResult();
					if(!empty($temp)) $layout = $temp;
				} else {
					$layout = $prodLayout;
				}
			}

		}

		if(!empty($layout)) {
			$view->setLayout( strtolower( $layout ) );
		}

	}

	/**
	 * Final setting of template
	 * Accepts a string, an id or an array with at least the keys template and params
	 * @author Max Milbers
	 */
	static function setTemplate ($template = 0) {

		$res = false;

		if(is_array($template)){
			$res = $template;
		} else {
			if(empty($template) or $template == 'default'){
				$res = self::loadVmTemplateStyle();
			} else {
				if(is_numeric($template)){
					$res = self::getTemplateById($template);
				} else {
					vmAdminInfo('Your template settings are old, please check your template settings in the vm config and in your categories');
					vmdebug('Your template settings are old, please check your template settings in the vm config and in your categories');
				}
			}
		}

		$registry = null;
		if($res){
			$registry = new JRegistry;
			$registry->loadString($res['params']);
			$template = $res['template'];
		}

		if(is_dir( VMPATH_ROOT.DS.'templates'.DS.$template )) {
			$app = JFactory::getApplication();
			if($app->isSite()) $app->setTemplate($template,$registry);

		} else {
			vmError( 'The chosen template couldnt be found on the filesystem: '.VMPATH_ROOT.DS.'templates'.DS.$template );
		}

		return $template;
	}

}
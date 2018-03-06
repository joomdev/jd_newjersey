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
 * @version $Id: $
 */
JFormHelper::loadFieldClass('filelist');
class JFormFieldVMFiles extends JFormFieldFileList {

	/**
	 * Element name
	 *
	 * @access    protected
	 * @var        string
	 */
	var $type  = 'Files';

	protected function getInput() {

		if(JVM_VERSION < 3) {
			$this->element['directory'] = 'images/stories';
		} else {
			//Fallback for old directories
			$dir = $this->getAttribute('directory');
			if(strpos($dir,'images/stories')!==false){

				$dirNew = str_replace('images/stories','images',$dir);
				if(!class_exists('JFolder')){
					require(VMPATH_LIBS.DS.'joomla'.DS.'filesystem'.DS.'folder.php');
				}
				if(JFolder::exists(VMPATH_ROOT .$dirNew)){
					$this->directory = $dirNew;
				}
			}

			return parent::getInput();
		}
	}

	protected function getOptions(){
		return parent::getOptions();
	}

}
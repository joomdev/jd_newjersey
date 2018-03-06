<?php 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
/**
*
* Base controller
*
* @package	VirtueMart
* @subpackage Core
* @author Max Milbers
* @link https://virtuemart.net
* @copyright Copyright (c) 2011 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id$
*/

if (!class_exists( 'VmController' )) require(VMPATH_ADMIN.DS.'helpers'.DS.'vmcontroller.php');

/**
 * VirtueMart default administrator controller
 *
 * @package		VirtueMart
 */

class VirtuemartControllerVirtuemart extends VmController {


	public function __construct() {
		parent::__construct();
	}

	/**
	 *
	 * Task for disabling dangerous database tools, used after install
	 * @author Max Milbers
	 */
	public function disableDangerousTools(){

		$config = VmModel::getInstance('config', 'VirtueMartModel');
		$config->setDangerousToolsOff();
		$this->display();
	}


	public function keepalive(){
		//echo 'alive';
		jExit();
	}



	public function getMemberStatus() {

		vRequest::vmCheckToken();

		$data = new stdClass();
		if(!vmAccess::manager()){
			$data->msg = 'No rights';
			echo vmJsApi::safe_json_encode($data);
			jExit();
		}

		$request = 0;
		$ackey = VmConfig::get('member_access_number','');
		$host = JUri::getInstance()->getHost();


		if(!empty($host) AND !empty($ackey)) {

			$link = '//extensions.virtuemart.net/index.php?option=com_virtuemart&view=plugin&name=istraxx_download_byhost&ackey='.base64_encode( $ackey ).'&host='.$host.'&vmlang='.VmConfig::$vmlangTag.'&sku=VMMS&vmver='.vmVersion::$RELEASE;

			$opts = array(
				'https'=>array(
				'method'=>"GET"
				/*'header'=>"Accept-language: en\r\n" .
				"Cookie: foo=bar\r\n"*/
				)
			);
			$context = stream_context_create($opts);

			$request = file_get_contents('https:'.$link, false, $context);

			if(!empty($request)) {
				if(preg_match('@(error|access denied)@i', $request)) {
					//return false;
				} else {
					$data = json_decode($request);

					if(empty($data->res) or empty($data->html)){
						vmdebug('Data is empty',$data);
						$data = new stdClass();
						$data->msg = 'Error getting validation file';
					} else {
						$data = $this->nag($data);
					}
				}
			}
		}
		echo vmJsApi::safe_json_encode($data);
		jExit();
	}

	private function nag($data){

		if(!empty($data->res)){

			if(!empty($data->html)){

				if(!class_exists('vmCrypt'))
					require(VMPATH_ADMIN.DS.'helpers'.DS.'vmcrypt.php');
				$safePath = vmCrypt::getEncryptSafepath();
				$safePath .= DS.'vmm.ini';
				$date = JFactory::getDate();
				$today = $date->toUnix();

				$content = ';<?php die(); */
					[keys]
					key = "'.VmConfig::get('member_access_number').'"
					unixtime = "'.$today.'"
					res = "'.vRequest::filter($data->res,FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_LOW).'"
					html = "'.htmlspecialchars($data->html).'"
					; */ ?>';
				$result = JFile::write($safePath, $content);
			}
		}
		return $data;
	}
}

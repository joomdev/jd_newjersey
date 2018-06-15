<?php
/**
 *
 * Data module for shop users
 *
 * @package	VirtueMart
 * @subpackage User
 * @author Oscar van Eijk
 * @author Max Milbers
 * @author	RickG
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: user.php 9802 2018-03-20 15:22:11Z Milbo $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Hardcoded groupID of the Super Admin
define ('__SUPER_ADMIN_GID', 25);

if(!class_exists('VmModel'))require(VMPATH_ADMIN.DS.'helpers'.DS.'vmmodel.php');


/**
 * Model class for shop users
 *
 * @package	VirtueMart
 * @subpackage	User
 * @author	RickG
 * @author Max Milbers
 */
class VirtueMartModelUser extends VmModel {


	/**
	 * Constructor for the user model.
	 *
	 * The user ID is read and determined if it is an array of ids or just one single id.
	 */
	function __construct(){

		parent::__construct('virtuemart_user_id');

		$this->setToggleName('user_is_vendor');
		$this->addvalidOrderingFieldName(array('ju.username','ju.name','ju.email','sg.virtuemart_shoppergroup_id','shopper_group_name','shopper_group_desc','vmu.virtuemart_user_id') );
		$this->setMainTable('vmusers');
		$this->removevalidOrderingFieldName('virtuemart_user_id');
		array_unshift($this->_validOrderingFieldName,'ju.id');
	}

	/**
	 * public function Resets the user id and data
	 *
	 *
	 * @author Max Milbers
	 */
	public function setId($cid){

		$user = JFactory::getUser();
		//anonymous sets to 0 for a new entry
		if(empty($user->id)){
			$userId = 0;
			//vmdebug('Recognized anonymous case');
		} else {
			//not anonymous, but no cid means already registered user edit own data
			if(empty($cid)){
				$userId = $user->id;
				//vmdebug('setId setCurrent $user',$user->get('id'));
			} else {
				if($cid != $user->id){
					$user = JFactory::getUser();
					if(vmAccess::manager(array('user','user.edit'))){
						$userId = $cid;
						//vmdebug('setId is Manager',$userId);
					} else {
						vmError('Blocked attempt setId '.$cid.' '.$user->id);
						$userId = $user->id;
					}
				}else {
					$userId = $user->id;
					//vmdebug('setId setCurrent $user',$user->get('id'));
				}
			}
		}

		$this->setUserId($userId);
		return $userId;

	}

	/**
	 * Internal function
	 *
	 * @param unknown_type $id
	 */
	private function setUserId($id){

		if($this->_id!=$id){
			$this->_id = (int)$id;
			$this->_data = null;
			$this->customer_number = 0;
		}
	}

	public function getCurrentUser(){
		$user = JFactory::getUser();
		$this->setUserId($user->id);
		return $this->getUser();
	}

	private $_defaultShopperGroup = 0;

	/**
	 * Sets the internal user id with given vendor Id
	 *
	 * @author Max Milbers
	 * @param int $vendorId
	 */
	function getVendor($vendorId=1,$return=TRUE){
		$vendorModel = VmModel::getModel('vendor');
		$userId = VirtueMartModelVendor::getUserIdByVendorId($vendorId);
		if($userId){
			$this->setUserId($userId);
			if($return){
				return $this->getUser();
			}
		} else {
			return false;
		}
	}


	/**
	 * Retrieve the detail record for the current $id if the data has not already been loaded.
	 * @author Max Milbers
	 */
	function getUser(){

		if(!empty($this->_data)) return $this->_data;

		$db = JFactory::getDBO();

		$this->_data = $this->getTable('vmusers');
		$this->_data->load((int)$this->_id);
		$this->_data->JUser = JUser::getInstance($this->_id);

		// Add the virtuemart_shoppergroup_ids
		if(!empty($this->_id)){
			$xrefTable = $this->getTable('vmuser_shoppergroups');
			$this->_data->shopper_groups = $xrefTable->load($this->_id);
		}
		if(empty($this->_data->shopper_groups)) $this->_data->shopper_groups = array();

		$site = JFactory::getApplication ()->isSite ();
		if ($site) {
			$shoppergroupmodel = VmModel::getModel('ShopperGroup');
			$shoppergroupmodel->appendShopperGroups($this->_data->shopper_groups,$this->_data->JUser,$site);
		}

		if(!empty($this->_id)) {
			$q = 'SELECT `virtuemart_userinfo_id` FROM `#__virtuemart_userinfos` WHERE `virtuemart_user_id` = "' . (int)$this->_id.'" ORDER BY `address_type` ASC';
			$db->setQuery($q);
			$userInfo_ids = $db->loadColumn(0);
		} else {
			$userInfo_ids  = array();
		}

		$this->_data->userInfo = array ();
		$BTuid = 0;

		foreach($userInfo_ids as $uid){

			$this->_data->userInfo[$uid] = $this->getTable('userinfos');
			$this->_data->userInfo[$uid]->load($uid);

			if ($this->_data->userInfo[$uid]->address_type == 'BT') {
				$BTuid = $uid;

				$this->_data->userInfo[$BTuid]->name = $this->_data->JUser->name;
				$this->_data->userInfo[$BTuid]->email = $this->_data->JUser->email;
				$this->_data->userInfo[$BTuid]->username = $this->_data->JUser->username;
				$this->_data->userInfo[$BTuid]->address_type = 'BT';
				// 				vmdebug('$this->_data->vmusers',$this->_data);
			}
		}

		// 		vmdebug('user_is_vendor ?',$this->_data->user_is_vendor);
		if($this->_data->user_is_vendor){

			$vendorModel = VmModel::getModel('vendor');
			if(Vmconfig::get('multix','none')=='none'){
				$this->_data->virtuemart_vendor_id = 1;
				//vmdebug('user model, single vendor',$this->_data->virtuemart_vendor_id);
			}

			$vendorModel->setId($this->_data->virtuemart_vendor_id);
			$this->_data->vendor = $vendorModel->getVendor();
		}

		return $this->_data;
	}


	/**
	 * Retrieve contact info for a user if any
	 *
	 * @return array of null
	 */
	function getContactDetails()
	{
		if ($this->_id) {
			$db = JFactory::getDBO();
			$db->setQuery('SELECT * FROM #__contact_details WHERE user_id = ' . $this->_id);
			$_contacts = $db->loadObjectList();
			if (count($_contacts) > 0) {
				return $_contacts[0];
			}
		}
		return null;
	}


	/**
	 * Bind the post data to the JUser object and the VM tables, then saves it
	 * It is used to register new users
	 * This function can also change already registered users, this is important when a registered user changes his email within the checkout.
	 *
	 * @author Max Milbers
	 * @author Oscar van Eijk
	 * @return boolean True is the save was successful, false otherwise.
	 */
	public function store(&$data){

		$message = '';
		vRequest::vmCheckToken('Invalid Token, while trying to save user');

		if(empty($data)){
			vmError('Developer notice, no data to store for user');
			return false;
		}

		//To find out, if we have to register a new user, we take a look on the id of the usermodel object.
		//The constructor sets automatically the right id.
		$new = false;
		if(empty($this->_id) or $this->_id < 1){
			$new = true;
			$user = new JUser();	//thealmega http://forum.virtuemart.net/index.php?topic=99755.msg393758#msg393758
		} else {
			$cUser = JFactory::getUser();
			if(!vmAccess::manager('user.edit') and $cUser->id!=$this->_id){
				vmWarn('Insufficient permission');
				return false;
			}
			$user = JFactory::getUser($this->_id);
		}

		$gid = $user->get('gid'); // Save original gid

		// Preformat and control user datas by plugin
		JPluginHelper::importPlugin('vmextended');
		JPluginHelper::importPlugin('vmuserfield');
		$dispatcher = JDispatcher::getInstance();

		$valid = true ;
		$dispatcher->trigger('plgVmOnBeforeUserfieldDataSave',array(&$valid,$this->_id,&$data,$user ));
		// $valid must be false if plugin detect an error
		if( !$valid ) {
			return false;
		}

		// Before I used this "if($cart && !$new)"
		// This construction is necessary, because this function is used to register a new JUser, so we need all the JUser data in $data.
		// On the other hand this function is also used just for updating JUser data, like the email for the BT address. In this case the
		// name, username, password and so on is already stored in the JUser and dont need to be entered again.

		if(empty ($data['email'])){
			$email = $user->get('email');
			if(!empty($email)){
				$data['email'] = $email;
			}
		} else {
			$data['email'] =  vRequest::filter($data['email'],FILTER_VALIDATE_EMAIL,FILTER_FLAG_STRIP_LOW|FILTER_FLAG_STRIP_HIGH);
		}
		//$data['email'] = str_replace(array('\'','"',',','%','*','/','\\','?','^','`','{','}','|','~'),array(''),$data['email']);

		//This is important, when a user changes his email address from the cart,
		//that means using view user layout edit_address (which is called from the cart)
		$user->set('email',$data['email']);

		if(empty ($data['name'])){
			$name = $user->get('name');
			if(!empty($name)){
				$data['name'] = $name;
			}

		} else {
			$data['name'] = vRequest::filter($data['name'],FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_LOW);

		}
		$data['name'] = str_replace(array('\'','"',',','%','*','/','\\','?','^','`','{','}','|','~'),array(''),$data['name']);

		if(empty ($data['username'])){
			$username = $user->get('username');
			if(!empty($username)){
				$data['username'] = $username;
			} else {
				$data['username'] = vRequest::filter($data['username'],FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_LOW);
			}
		}

		if(empty ($data['password'])){
			$data['password'] = vRequest::getCmd('password', '');
			if($data['password']!=vRequest::get('password')){
				vmError('Password contained invalid character combination.');
				return false;
			}
		}

		if(empty ($data['password2'])){
			$data['password2'] = vRequest::getCmd('password2');
			if($data['password2']!=vRequest::get('password2')){
				vmError('Password2 contained invalid character combination.');
				return false;
			}
		}

		if(!$new and empty($data['password2'])){
			unset($data['password']);
			unset($data['password2']);
		}

		if(!vmAccess::manager('core')){
			$whiteDataToBind = array();
			if(isset($data['name'])) $whiteDataToBind['name'] = $data['name'];
			if(isset($data['username'])) $whiteDataToBind['username'] = $data['username'];
			if(isset($data['email'])) $whiteDataToBind['email'] = $data['email'];
			if(isset($data['language'])) $whiteDataToBind['language'] = $data['language'];
			if(isset($data['editor'])) $whiteDataToBind['editor'] = $data['editor'];
			if(isset($data['password'])) $whiteDataToBind['password'] = $data['password'];
			if(isset($data['password2'])) $whiteDataToBind['password2'] = $data['password2'];
			unset($data['isRoot']);
		} else {
			$whiteDataToBind = $data;
		}

		// Bind Joomla userdata
		if (!$user->bind($whiteDataToBind)) {
			vmdebug('Couldnt bind data to joomla user');
			//array('user'=>$user,'password'=>$data['password'],'message'=>$message,'newId'=>$newId,'success'=>false);
		}

		if($new){
			// If user registration is not allowed, show 403 not authorized.
			// But it is possible for admins and storeadmins to save
			$usersConfig = JComponentHelper::getParams( 'com_users' );

			$cUser = JFactory::getUser();
			if($usersConfig->get('allowUserRegistration') == '0' and !(vmAccess::manager('user')) ) {
				vmLanguage::loadJLang('com_virtuemart');
				$msg = vmText::_ ('COM_VIRTUEMART_ACCESS_FORBIDDEN'). ' allowUserRegistration in joomla disabled';
				vmError($msg, $msg);
				return;
			}
			// Initialize new usertype setting
			$newUsertype = $usersConfig->get( 'new_usertype' );
			if (!$newUsertype) {
				$newUsertype=2;
			}

			// Set some initial user values
			$user->set('usertype', $newUsertype);

			$user->groups[] = $newUsertype;

			$date = JFactory::getDate();
			$user->set('registerDate', $date->toSQL());

			// If user activation is turned on, we need to set the activation information
			$useractivation = $usersConfig->get( 'useractivation' );
			$doUserActivation=false;
			if ($useractivation == '1' or $useractivation == '2') {
				$doUserActivation=true;
			}

			if ($doUserActivation ) {
				jimport('joomla.user.helper');
				$user->set('activation', vRequest::getHash( JUserHelper::genRandomPassword()) );
				$user->set('block', '1');
				if ($useractivation == '2') {
					$user->set('guest', '1');
				}
				//$user->set('lastvisitDate', '0000-00-00 00:00:00');
			}
		}

		$option = vRequest::getCmd( 'option');
		// If an exising superadmin gets a new group, make sure enough admins are left...
		if (!$new && $user->get('gid') != $gid && $gid == __SUPER_ADMIN_GID) {
			if ($this->getSuperAdminCount() <= 1) {
				vmError(vmText::_('COM_VIRTUEMART_USER_ERR_ONLYSUPERADMIN'));
				return false;
			}
		}

		if(isset($data['language'])){
			$user->setParam('language',$data['language']);
		}

		// Load the users plugin group.
		JPluginHelper::importPlugin('user');

		// Save the JUser object
		if (!$user->save()) {
			$msg = vmText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED',$user->getError());
			vmError($msg,$msg);
			return false;
		} else {
			$data['name'] = $user->get('name');
			$data['username'] = $user->get('username');
			$data['email'] = $user->get('email');
			$data['language'] = $user->get('language');
			$data['editor'] = $user->get('editor');
		}

		$newId = $user->get('id');
		$data['virtuemart_user_id'] = $newId;	//We need this in that case, because data is bound to table later
		$this->setUserId($newId);

		//Save the VM user stuff
		if(!$this->saveUserData($data) || !self::storeAddress($data)){
			vmError('COM_VIRTUEMART_NOT_ABLE_TO_SAVE_USER_DATA');
			// 			vmError(vmText::_('COM_VIRTUEMART_NOT_ABLE_TO_SAVE_USERINFO_DATA'));
		} else {
			

			if ($new) {
				$user->userInfo = $data;
				$password='';
				if ($usersConfig->get('sendpassword', 1)) {
					$password=$user->password_clear;
				}

				//$doVendor = (boolean) $usersConfig->get('mail_to_admin', true);

				$this->sendRegistrationEmail($user,$password, $doUserActivation);
				if ($doUserActivation ) {
					vmInfo('COM_VIRTUEMART_REG_COMPLETE_ACTIVATE');
				} else {
					vmInfo('COM_VIRTUEMART_REG_COMPLETE');
					$user->set('activation', '' );
					$user->set('block', '0');
					$user->set('guest', '0');
				}
			} else {
				vmInfo('COM_VIRTUEMART_USER_DATA_STORED');
			}
		}

		//The extra check for isset vendor_currency prevents storing of the vendor if there is no form (edit address cart)
		if((int)$data['user_is_vendor']==1 and isset($data['vendor_currency'])){
			vmdebug('vendor recognised '.$data['virtuemart_vendor_id']);
			if($this ->storeVendorData($data)){
				if ($new) {
					if ($doUserActivation ) {
						vmInfo('COM_VIRTUEMART_REG_VENDOR_COMPLETE_ACTIVATE');
					} else {
						vmInfo('COM_VIRTUEMART_REG_VENDOR_COMPLETE');
					}
				} else {
					vmInfo('COM_VIRTUEMART_VENDOR_DATA_STORED');
				}
			}
		}

		if(!isset($data['password'])) $data['password'] = '';
		return array('user'=>$user,'password'=>$data['password'],'message'=>$message,'newId'=>$newId,'success'=>true);

	}

	/**
	 * This function is NOT for anonymous. Anonymous just get the information directly sent by email.
	 * This function saves the vm Userdata for registered JUsers.
	 * TODO, setting of shoppergroup isnt done
	 *
	 * TODO No reason not to use this function for new users, but it requires a Joomla <user> plugin
	 * that gets fired by the onAfterStoreUser. I'll built that (OvE)
	 *
	 * Notice:
	 * As long we do not have the silent registration, an anonymous does not get registered. It is enough to send the virtuemart_order_id
	 * with the email. The order is saved with all information in an extra table, so there is
	 * no need for a silent registration. We may think about if we actually need/want the feature silent registration
	 * The information of anonymous is stored in the order table and has nothing todo with the usermodel!
	 *
	 * @author Max Milbers
	 * @author Oscar van Eijk
	 * return boolean
	 */
	public function saveUserData(&$data,$trigger=true){

		if(empty($this->_id)){
			echo 'This is a notice for developers, you used this function for an anonymous user, but it is only designed for already registered ones';
			vmError( 'This is a notice for developers, you used this function for an anonymous user, but it is only designed for already registered ones');
			return false;
		}

		$noError = true;

		$usertable = $this->getTable('vmusers');
		$alreadyStoredUserData = $usertable->load($this->_id);

		if(!vmAccess::manager('core')){
			unset($data['virtuemart_vendor_id']);
			unset($data['user_is_vendor']);
		} else {
			if(!isset($data['user_is_vendor']) and !empty($alreadyStoredUserData->user_is_vendor)){
				$data['user_is_vendor'] = $alreadyStoredUserData->user_is_vendor;
			}
			if(!isset($data['virtuemart_vendor_id']) and !empty($alreadyStoredUserData->virtuemart_vendor_id)){
				$data['virtuemart_vendor_id'] = $alreadyStoredUserData->virtuemart_vendor_id;
			}
		}

		if(!vmAccess::manager('user.edit')){
			unset($data['customer_number']);
		}
		if(empty($alreadyStoredUserData->customer_number)){
			if(empty($data['customer_number'])){
				$data['customer_number'] = strtoupper(substr($data['username'],0,2)).substr(md5($data['username']),0,7);
				//We set this data so that vmshopper plugin know if they should set the customer number
				$data['customer_number_bycore'] = 1;
			}
		} else {
			if(!vmAccess::manager('user.edit')){
				$data['customer_number'] = $alreadyStoredUserData->customer_number;
			}
		}

		if($trigger){
			JPluginHelper::importPlugin('vmextended');
			JPluginHelper::importPlugin('vmshopper');
			$dispatcher = JDispatcher::getInstance();

			$plg_datas = $dispatcher->trigger('plgVmOnUserStore',array(&$data));
			foreach($plg_datas as $plg_data){
				// 			$data = array_merge($plg_data,$data);
			}
		}

		$res = $usertable -> bindChecknStore($data);
		if(!$res){
			vmError('storing user adress data');
			$noError = false;
		}

		$data['virtuemart_vendor_id'] = $usertable->virtuemart_vendor_id;
		$data['user_is_vendor'] = $usertable->user_is_vendor;

		if(vmAccess::manager('user.edit') and !empty($data['virtuemart_shoppergroup_set'])){

			$shoppergroupmodel = VmModel::getModel('ShopperGroup');
			if(empty($this->_defaultShopperGroup)){
				$this->_defaultShopperGroup = $shoppergroupmodel->getDefault(0);
			}

			$user_shoppergroups_table = $this->getTable('vmuser_shoppergroups');
			if(empty($data['virtuemart_shoppergroup_id']) or $data['virtuemart_shoppergroup_id']==$this->_defaultShopperGroup->virtuemart_shoppergroup_id){
				$data['virtuemart_shoppergroup_id'] = array();
			}

			//We can't do that here, because we could else not set "no shoppergroup"
			/*if(!isset($data['virtuemart_shoppergroup_id'])){
				$data['virtuemart_shoppergroup_id'] = array();
			}*/


			$shoppergroupData = array('virtuemart_user_id'=>$this->_id,'virtuemart_shoppergroup_id'=>$data['virtuemart_shoppergroup_id']);

			$res = $user_shoppergroups_table -> bindChecknStore($shoppergroupData);
			if(!$res){
				vmError('Set shoppergroup error');
				$noError = false;
			}

		}

		if($trigger){
			$plg_datas = $dispatcher->trigger('plgVmAfterUserStore',array($data));
			foreach($plg_datas as $plg_data){
				$data = array_merge($plg_data);
			}
		}

		if(!empty($data['vendorId']) and $data['vendorId']>1){
			//$vUserD = array('virtuemart_user_id' => $data['virtuemart_user_id'],'virtuemart_vendor_id' => $data['vendorId']);
			$vUser = $this->getTable('vendor_users');
			$vUser->load((int)$data['vendorId']);
			if(!$vUser->virtuemart_user_id){
				$vUser->bind(array('virtuemart_vendor_id'=>(int)$data['vendorId'],'virtuemart_user_id'=>$data['virtuemart_user_id']));
			} else if(!in_array((int)$data['virtuemart_user_id'],$vUser->virtuemart_user_id)){
				$arr = array_merge($vUser->virtuemart_user_id,(array)$data['virtuemart_user_id']);
				$vUser->bind(array('virtuemart_vendor_id'=>(int)$data['vendorId'],'virtuemart_user_id'=>$arr));
			}
			$vUser->store();

		}

		return $noError;
	}

	public function storeVendorData($data){

		if($data['user_is_vendor'] and vmAccess::manager('user.editshop')){

			$vendorModel = VmModel::getModel('vendor');

			//TODO Attention this is set now to virtuemart_vendor_id=1 in single vendor mode, because using a vendor with different id then 1 is not completly supported and can lead to bugs
			//So we disable the possibility to store vendors not with virtuemart_vendor_id = 1
			if(Vmconfig::get('multix','none')=='none' ){
				$data['virtuemart_vendor_id'] = 1;
				vmdebug('no multivendor, set virtuemart_vendor_id = 1');
			}
			$vendorModel->setId($data['virtuemart_vendor_id']);

			if (!$vendorModel->store($data)) {
				vmdebug('Error storing vendor',$vendorModel);
				return false;
			}
			//$this->setFraudProtection();

		}

		return true;
	}

	/**
	 * FraudProtection to comply to the French financial Law 2018
	 *
	 * @author Valérie Isaksen
	 */
	function setFraudProtection(){

		if(!class_exists('VirtueMartModelConfig')) require(VMPATH_ADMIN .'/models/config.php');
		$res  = VirtueMartModelConfig::checkConfigTableExists();
		if(!empty($res)){
			$model = $this->getModel('config');
			$model->setFraudProtection();
		}
	}


	/**
	 * Take a data array and save any address info found in the array.
	 *
	 * @author unknown, oscar, max milbers
	 * @param array $data (Posted) user data
	 * @param sting $_table Table name to write to, null (default) not to write to the database
	 * @param boolean $_cart Attention, this was deleted, the address to cart is now done in the controller (True to write to the session (cart))
	 * @return boolean True if the save was successful, false otherwise.
	 */
	function storeAddress(&$data){

		$user =JFactory::getUser();

		$userinfo = $this->getTable('userinfos');

		$manager = vmAccess::manager();
		if($data['address_type'] == 'BT'){

			if(isset($data['virtuemart_userinfo_id']) and $data['virtuemart_userinfo_id']!=0){

				if(!$manager ){

					$userinfo->load($data['virtuemart_userinfo_id']);

					if($userinfo->virtuemart_user_id!=$user->id){
						vmError('Hacking attempt as admin?','Hacking attempt storeAddress');
						return false;
					}
				}
			} else {

				if(!$manager){
					$userId = $user->id;
				} else {
					$userId = (int)$data['virtuemart_user_id'];
				}
				$q = 'SELECT `virtuemart_userinfo_id` FROM #__virtuemart_userinfos
				WHERE `virtuemart_user_id` = '.$userId.'
				AND `address_type` = "BT"';

				$db = JFactory::getDbo();
				$db->setQuery($q);
				$total = $db->loadColumn();

				if (count($total) > 0) {
					$data['virtuemart_userinfo_id'] = (int)$total[0];
				} else {
					$data['virtuemart_userinfo_id'] = 0;//md5(uniqid($this->virtuemart_user_id));
				}
				$userinfo->load($data['virtuemart_userinfo_id']);
				//unset($data['virtuemart_userinfo_id']);
			}
			$data = (array)$data;
			if(!$this->validateUserData($data,'BT')){
				return false;
			}

			$userInfoData = self::_prepareUserFields($data, 'BT',$userinfo);
			//vmdebug('model user storeAddress',$data);
			$userinfo->bindChecknStore($userInfoData);
		}

		// Check for fields with the the 'shipto_' prefix; that means a (new) shipto address.
		if($data['address_type'] == 'ST' or isset($data['shipto_address_type_name'])){
			$dataST = array();
			//$_pattern = '/^shipto_/';

			foreach ($data as $_k => $_v) {
				//if (preg_match($_pattern, $_k)) {
				if (strpos($_k,'shipto_')===0) {
					//$_new = preg_replace($_pattern, '', $_k);
					$_new = substr($_k,7);
					$dataST[$_new] = $_v;
				}
			}

			$userinfo   = $this->getTable('userinfos');
			if(isset($dataST['virtuemart_userinfo_id']) and $dataST['virtuemart_userinfo_id']!=0){
				$dataST['virtuemart_userinfo_id'] = (int)$dataST['virtuemart_userinfo_id'];

				if(!$manager){

					$userinfo->load($dataST['virtuemart_userinfo_id']);

					$user = JFactory::getUser();
					if($userinfo->virtuemart_user_id!=$user->id){
						vmError('Hacking attempt as admin?','Hacking attempt store address');
						return false;
					}
				}
			}

			if(empty($userinfo->virtuemart_user_id)){
				if(!$manager){
					$dataST['virtuemart_user_id'] = $user->id;
				} else {
					if(isset($data['virtuemart_user_id'])){
						$dataST['virtuemart_user_id'] = (int)$data['virtuemart_user_id'];
					} else {
						//Disadvantage is that admins should not change the ST address in the FE (what should never happen anyway.)
						$dataST['virtuemart_user_id'] = $user->id;
					}
				}
			}

			if(!is_array($dataST)) $dataST = (array)$dataST;
			if(!$this->validateUserData($dataST,'ST')){
				return false;
			}
			$dataST['address_type'] = 'ST';
			$userfielddata = self::_prepareUserFields($dataST, 'ST',$userinfo,'shipto_');

			$userinfo->bindChecknStore($userfielddata);

			$app = JFactory::getApplication();
			if($app->isSite()){
				if (!class_exists('VirtueMartCart')) require(VMPATH_SITE . DS . 'helpers' . DS . 'cart.php');
				$cart = VirtuemartCart::getCart();
				if($cart){
					$cart->selected_shipto = $userinfo->virtuemart_userinfo_id;
				}
			}
		}


		return $userinfo->virtuemart_userinfo_id;
	}

	/**
	* Test userdata if valid
	*
	* @author Max Milbers
	* @param String if BT or ST
	* @param Object If given, an object with data address data that must be formatted to an array
	* @return redirectMsg, if there is a redirectMsg, the redirect should be executed after
	*/
	public function validateUserData(&$data,$type='BT',$showInfo = false) {

		if (!class_exists('VirtueMartModelUserfields'))
		require(VMPATH_ADMIN . DS . 'models' . DS . 'userfields.php');
		$userFieldsModel = VmModel::getModel('userfields');

		if ($type == 'BT') {
			$fieldtype = 'account';
		} else if($type == 'cartfields'){
			$fieldtype = 'cart';
		} else {
			$fieldtype = 'shipment';
		}

		$neededFields = $userFieldsModel->getUserFields(
		$fieldtype
		, array('required' => true, 'delimiters' => true, 'captcha' => true, 'system' => false)
		, array('delimiter_userinfo', 'name','username', 'password', 'password2', 'address_type_name', 'address_type', 'user_is_vendor', 'agreed'));

		$i = 0;

		$return = true;
		$untested = true;
		$required  = 0;
		$missingFields = array();
		$lang = JFactory::getLanguage();
		foreach ($neededFields as $field) {

			//This is a special test for the virtuemart_state_id. There is the speciality that the virtuemart_state_id could be 0 but is valid.
			if ($field->name == 'virtuemart_state_id' or $field->name == 'virtuemart_country_id' and $untested) {
				$untested = false;
				if (!class_exists('VirtueMartModelState')) require(VMPATH_ADMIN . DS . 'models' . DS . 'state.php');
				if(!empty($data['virtuemart_country_id'])){
					if(!isset($data['virtuemart_state_id'])) $data['virtuemart_state_id'] = 0;

					if (!$msg = VirtueMartModelState::testStateCountry($data['virtuemart_country_id'], $data['virtuemart_state_id'])) {
						//The state is invalid, so we set the state 0 here.
						//$data['virtuemart_state_id'] = 0;
						//vmdebug('State was not fitting to country, set virtuemart_state_id to 0');
					} else if(empty($data['virtuemart_state_id'])){
						//vmdebug('virtuemart_state_id is empty, but valid (country has not states, set to unrequired');
						$field->required = false;
					} else {
						//vmdebug('validateUserData my country '.$data['virtuemart_country_id'].' my state '.$data['virtuemart_state_id']);
					}
				}
			}

			if($field->required ){
				$required++;
				if(empty($data[$field->name])){
					if($lang->hasKey('COM_VIRTUEMART_MISSING_'.$field->name)){
						$missingFields[] = vmText::_('COM_VIRTUEMART_MISSING_'.$field->name);
					} else {
						$missingFields[] = vmText::sprintf('COM_VIRTUEMART_MISSING_VALUE_FOR_FIELD',$field->title );
					}

					$i++;
					$return = false;
				}
				else if($data[$field->name] == $field->default){
					$i++;
				} else {

				}
			}
		}

		if(empty($required)){
			vmdebug('Nothing to require');
			$return = true;
		} else if($i==$required){
			$return = -1;
		}
		//vmdebug('my i '.$i.' my data size $showInfo: '.(int)$showInfo.' required: '.(int)$required,$return);

		//if( ($required>2 and ($i+1)<$required) or ($required<=2 and !$return) or $showInfo){
		if($showInfo or ($required>2 and $i<($required-1)) or ($required<3 and !$return) ){
			foreach($missingFields as $fieldname){
				vmInfo($fieldname);
			}
		}
		return $return;
	}


	function _prepareUserFields(&$data, $type, $userinfo = 0, $prefix = '')
	{
		if(!class_exists('VirtueMartModelUserfields')) require(VMPATH_ADMIN.DS.'models'.DS.'userfields.php' );
		$userFieldsModel = VmModel::getModel('userfields');

		if ($type == 'ST') {
			$prepareUserFields = $userFieldsModel->getUserFields(
									 'shipment'
			, array() // Default toggles
			);
		} else { // BT
			// The user is not logged in (anonymous), so we need tome extra fields
			$prepareUserFields = $userFieldsModel->getUserFields(
										 'account'
			, array() // Default toggles
			, array('delimiter_userinfo', 'name', 'username', 'password', 'password2', 'user_is_vendor') // Skips
			);

		}

		$user = JFactory::getUser();
		$manager = vmAccess::manager();

		// Format the data
		foreach ($prepareUserFields as $fld) {
			if(empty($data[$fld->name])) $data[$fld->name] = '';

			if(!$manager and $fld->readonly){
				$fldName = $fld->name;
				unset($data[$fldName]);
				if($userinfo!==0){
					if(property_exists($userinfo,$fldName)){
						$data[$fldName] = $userinfo->$fldName;
					} else {
						vmError('Your tables seem to be broken, you have fields in your form which have no corresponding field in the db');
					}
				}
			} else {
				$data[$fld->name] = $userFieldsModel->prepareFieldDataSave($fld, $data, $prefix);
			}
		}

		return $data;
	}

	function getBTuserinfo_id($id = 0){
		if(empty($db)) $db = JFactory::getDBO();

		if($id == 0){
			$id = $this->_id;
			vmdebug('getBTuserinfo_id is '.$this->_id);
		}

		static $c = array();

		if(isset($c[$id])){
			return $c[$id];
		} else {
			$q = 'SELECT `virtuemart_userinfo_id` FROM `#__virtuemart_userinfos` WHERE `virtuemart_user_id` = "' .(int)$id .'" AND `address_type`="BT" ';
			$db->setQuery($q);
			$c[$id] = $db->loadResult();
			return $c[$id];
		}
	}

	/**
	 *
	 * @author Max Milbers
	 */
	function getUserInfoInUserFields($layoutName, $type,$uid,$cart=true,$isVendor=false ){

		// 		if(!class_exists('VirtueMartModelUserfields')) require(VMPATH_ADMIN.DS.'models'.DS.'userfields.php' );
		// 		$userFieldsModel = new VirtuemartModelUserfields();
		$userFieldsModel = VmModel::getModel('userfields');
		$prepareUserFields = $userFieldsModel->getUserFieldsFor( $layoutName, $type );

		if($type=='ST'){
			$preFix = 'shipto_';
		} else {
			$preFix = '';
		}
		/*
		 * JUser  or $this->_id is the logged user
		 */
		if(!empty($this->_data->JUser) and $this->_data->JUser->id==$this->_id){
			$JUser = $this->_data->JUser;
		} else {
			if(empty($this->_data)){
				$JUser = JUser::getInstance($this->_id);
			} else {
				$JUser = $this->_data->JUser = JUser::getInstance($this->_id);
			}
		}

		$data = null;
		$userFields = array();
		if(!empty($uid)){

			$dataT = $this->getTable('userinfos');
			$data = $dataT->load($uid);

			if($data->virtuemart_user_id!==0 and !$isVendor){

				$user = JFactory::getUser();
				if(!vmAccess::manager()){
					if($data->virtuemart_user_id!=$this->_id){
						vmError('Blocked attempt loading userinfo, you got logged');
						echo 'Hacking attempt loading userinfo, you got logged';
						return false;
					}
				}
			}

			if ($data->address_type != 'ST' ) {
				$BTuid = $uid;

				$data->name = $JUser->name;
				$data->email = $JUser->email;
				$data->username = $JUser->username;
				$data->address_type = 'BT';

			}
		} else {
			vmdebug('getUserInfoInUserFields case empty $uid');
			//New Address is filled here with the data of the cart (we are in the userview)
			if($cart){

				if (!class_exists('VirtueMartCart'))
				require(VMPATH_SITE . DS . 'helpers' . DS . 'cart.php');
				$cart = VirtueMartCart::getCart();
				$adType = $type.'address';

				if(empty($cart->$adType)){
					$data = $cart->$type;
					if(empty($data)) $data = array();

					if($JUser){
						if(empty($data['name'])){
							$data['name'] = $JUser->name;
						}
						if(empty($data['email'])){
							$data['email'] = $JUser->email;
						}
						if(empty($data['username'])){
							$data['username'] = $JUser->username;
						}
						if(empty($data['virtuemart_user_id'])){
							$data['virtuemart_user_id'] = $JUser->id;
						}
					}
					$data = (object)$data;
				}

			} else {

				if($JUser){
						if(empty($data['name'])){
							$data['name'] = $JUser->name;
						}
						if(empty($data['email'])){
							$data['email'] = $JUser->email;
						}
						if(empty($data['username'])){
							$data['username'] = $JUser->username;
						}
						if(empty($data['virtuemart_user_id'])){
							$data['virtuemart_user_id'] = $JUser->id;
						}
					$data = (object)$data;
				}
			}
		}

		if(empty($data) ) {
			vmdebug('getUserInfoInUserFields $data empty',$uid,$data);
			$cart = VirtueMartCart::getCart();
			$data = $cart->BT;
		}

		$userFields[$uid] = $userFieldsModel->getUserFieldsFilled(
		$prepareUserFields
		,$data
		,$preFix
		);

		return $userFields;
	}


	/**
	 * This stores the userdata given in userfields
	 * @deprecated seems unused
	 * @author Max Milbers
	 */
	function storeUserDataByFields($data,$type, $toggles, $skips){

		if(!class_exists('VirtueMartModelUserfields')) require(VMPATH_ADMIN.DS.'models'.DS.'userfields.php' );
		$userFieldsModel = VmModel::getModel('userfields');

		$prepareUserFields = $userFieldsModel->getUserFields(
		$type,
		$toggles,
		$skips
		);

		// Format the data
		foreach ($prepareUserFields as $_fld) {
			if(empty($data[$_fld->name])) $data[$_fld->name] = '';
			$data[$_fld->name] = $userFieldsModel->prepareFieldDataSave($_fld,$data);
		}

		$this->store($data);

		return true;

	}

	/**
	 * This uses the shopFunctionsF::renderAndSendVmMail function, which uses a controller and task to render the content
	 * and sents it then.
	 *
	 *
	 * @author Oscar van Eijk
	 * @author Max Milbers
	 * @author Christopher Roussel
	 * @author Valérie Isaksen
	 */
	private function sendRegistrationEmail($user, $password, $doUserActivation){
		if(!class_exists('shopFunctionsF')) require(VMPATH_SITE.DS.'helpers'.DS.'shopfunctionsf.php');
		$vars = array('user' => $user);

		// Send registration confirmation mail
		$password = preg_replace('/[\x00-\x1F\x7F]/', '', $password); //Disallow control chars in the email
		$vars['password'] = $password;

		if ($doUserActivation) {
			jimport('joomla.user.helper');
			$activationLink = 'index.php?option=com_users&task=registration.activate&token='.$user->get('activation');

			$vars['activationLink'] = $activationLink;
		}

		$usersConfig = JComponentHelper::getParams( 'com_users' );
		$adminMail = $usersConfig->get('mail_to_admin',false);
		if(empty($adminMail)){
			unset($vars['doVendor']);	//The construction is due the nasty construction in renderMail
		} else {
			$vars['doVendor'] = 1;
		}

		shopFunctionsF::renderMail('user', $user->get('email'), $vars);

	}

	/**
	 * Delete all record ids selected
	 *
	 * @return boolean True is the remove was successful, false otherwise.
	 */
	function remove($userIds) {

		if(vmAccess::manager('user')){

			$userInfo = $this->getTable('userinfos');
			$vm_shoppergroup_xref = $this->getTable('vmuser_shoppergroups');
			$vmusers = $this->getTable('vmusers');
			$_status = true;
			foreach($userIds as $userId) {

				$_JUser = JUser::getInstance($userId);

				if ($this->getSuperAdminCount() <= 1) {
					// Prevent deletion of the only Super Admin
					//$_u = JUser::getInstance($userId);
					if ($_JUser->get('gid') == __SUPER_ADMIN_GID) {
						vmError(vmText::_('COM_VIRTUEMART_USER_ERR_LASTSUPERADMIN'));
						$_status = false;
						continue;
					}
				}

				if (!$userInfo->delete($userId)) {
					return false;
				}

				if (!$vm_shoppergroup_xref->delete($userId)) {
					$_status = false;
					continue;
				}

				if (!$vmusers->delete($userId)) {
					$_status = false;
					continue;
				}

				if (!$_JUser->delete()) {
					vmError($_JUser->getError());
					$_status = false;
					continue;
				}
			}
		}

		return $_status;
	}

	function removeAddress($virtuemart_userinfo_id){

		$db = JFactory::getDBO();

		if ( isset($virtuemart_userinfo_id) and $this->_id != 0 ) {
			//$userModel -> deleteAddressST();
			$q = 'DELETE FROM #__virtuemart_userinfos  WHERE virtuemart_user_id="'. $this->_id .'" AND virtuemart_userinfo_id="'. (int)$virtuemart_userinfo_id .'"';
			$db->setQuery($q);
			if($db->execute()){
				vmInfo('COM_VIRTUEMART_ADDRESS_DELETED');
				return true;
			}
		}
		return false;
	}

	var $searchTable = 'juser';
	/**
	 * Retrieve a list of users from the database.
	 *
	 * @author Max Milbers
	 * @return object List of user objects
	 */
	function getUserList() {

		//$select = ' * ';
		//$joinedTables = ' FROM #__users AS ju LEFT JOIN #__virtuemart_vmusers AS vmu ON ju.id = vmu.virtuemart_user_id';
		$search = vRequest::getString('search', false);
		$app = JFactory::getApplication ();
		$this->searchTable = $app->getUserStateFromRequest ('com_virtuemart.user.searchTable', 'searchTable', 'juser', 'string');
		//$tableToUse = vRequest::getString('searchTable','juser');

		$where = array();
		if ($search) {
			$where = ' WHERE ';
			$db = JFactory::getDbo();
			$searchArray = array('ju.name','ju.username','ju.email','shopper_group_name');	// removed ,'usertype' should be handled by extra dropdown
			$userFieldsValid = array();
			if($this->searchTable!='juser'){

				if(!class_exists('TableUserinfos'))require(VMPATH_ADMIN.DS.'tables'.DS.'userinfos.php');

				$userfieldTable = new TableUserinfos($db);
				$userfieldFields = $userfieldTable->getProperties();
				$userFieldSearchArray = array('company','first_name','last_name','address_1','zip','city','phone_1');
				//We must validate if the userfields actually exists, they could be removed

				foreach($userFieldSearchArray as $ufield){
					if(array_key_exists($ufield,$userfieldFields)){
						$userFieldsValid[] = $ufield;
					}
				}
				$searchArray = array_merge($userFieldsValid,$searchArray);
			}

			$search = str_replace(' ','%',$db->escape( $search, true ));
			foreach($searchArray as $field){

					$whereOr[] = ' '.$field.' LIKE "%'.$search.'%" ';
			}
			//$where = substr($where,0,-3);
		}

		$select = ' ju.id AS id
			, ju.name AS name
			, ju.username AS username
			, ju.email AS email
			, IFNULL(vmu.user_is_vendor,"0") AS is_vendor
			, IFNULL(sg.shopper_group_name, "") AS shopper_group_name ';

		if ($search) {
			if($this->searchTable!='juser'){
				$select .= ' , ui.name as uiname ';
			}

			foreach($userFieldsValid as $ufield){
				$select .= ' , '.$ufield;
			}
		}

		$joinedTables = ' FROM #__users AS ju
			LEFT JOIN #__virtuemart_vmusers AS vmu ON ju.id = vmu.virtuemart_user_id
			LEFT JOIN #__virtuemart_vmuser_shoppergroups AS vx ON ju.id = vx.virtuemart_user_id
			LEFT JOIN #__virtuemart_shoppergroups AS sg ON vx.virtuemart_shoppergroup_id = sg.virtuemart_shoppergroup_id ';
		if ($search and $this->searchTable!='juser') {
			$joinedTables .= ' LEFT JOIN #__virtuemart_userinfos AS ui ON ui.virtuemart_user_id = vmu.virtuemart_user_id';
		}

		$whereAnd = array();
		if(VmConfig::get('multixcart',0)=='byvendor'){
			$superVendor = vmAccess::isSuperVendor();
			if($superVendor>1){
				$joinedTables .= ' LEFT JOIN #__virtuemart_vendor_users AS vu ON ju.id = vmu.virtuemart_user_id';
				$whereAnd[] = ' vu.virtuemart_vendor_id = '.$superVendor.' ';
			}
		}

		$where = '';
		$whereStr =  ' WHERE ';
		if(!empty($whereOr)){
			$where = $whereStr.implode(' OR ',$whereOr);
			$whereStr = 'AND';
		}
		if(!empty($whereAnd)){
			$where .= $whereStr.' ('.implode(' OR ',$whereAnd).')';
		}
		return $this->_data = $this->exeSortSearchListQuery(0,$select,$joinedTables,$where,' GROUP BY ju.id',$this->_getOrdering());

	}

	public function getSwitchUserList($superVendor=null,$adminID=false) {

		if(!isset($superVendor)) $superVendor = vmAccess::isSuperVendor();

		$result = false;
		if($superVendor){
			$db = JFactory::getDbo();
			$search = vRequest::getUword('usersearch','');
			if(!empty($search)){
				$search = ' WHERE (`name` LIKE "%'.$search.'%" OR `username` LIKE "%'.$search.'%" OR `customer_number` LIKE "%'.$search.'%")';
			} else if($superVendor!=1) {
				$search = ' WHERE vu.virtuemart_vendor_id = '.$superVendor.' ';
			}

			$q = 'SELECT ju.`id`,`name`,`username` FROM `#__users` as ju';

			if($superVendor!=1 or !empty($search)) {
				$q .= ' LEFT JOIN #__virtuemart_vmusers AS vmu ON vmu.virtuemart_user_id = ju.id';
				if($superVendor!=1){
					$q .= ' LEFT JOIN #__virtuemart_vendor_users AS vu ON vu.virtuemart_user_id = ju.id';
					$search .=  ' AND ( vmu.user_is_vendor = 0 OR (vmu.virtuemart_vendor_id) IS NULL)';
				}
			}
			$current = JFactory::getUser();
			$hiddenUserID = $adminID ? $adminID : $current->id;
			if(!empty($search)){
				$search .= ' AND ju.id!= "'.$hiddenUserID.'" ';
			} else {
				$q .= ' WHERE ju.id!= "'.$hiddenUserID.'" ';
			}


			$q .= $search.' ORDER BY `name` LIMIT 0,10000';
			$db->setQuery($q);
			$result = $db->loadObjectList();

			if($result){
				foreach($result as $k => $user) {
					$result[$k]->displayedName = $user->name .'&nbsp;&nbsp;( '. $user->username .' )';
				}
			} else {
				$result = array();
			}

			if($adminID){

				$user = JFactory::getUser($adminID);
				if($current->id!=$user->id){
					$toAdd = new stdClass();
					$toAdd->id = $user->id;
					$toAdd->name = $user->name;
					$toAdd->username = $user->username;
					$toAdd->displayedName = vmText::sprintf('COM_VIRTUEMART_RETURN_TO',$user->name,$user->username);
					array_unshift($result,$toAdd);
				}
			}

			$toAdd = new stdClass();
			$toAdd->id = 0;
			$toAdd->name = '';
			$toAdd->username = '';
			$toAdd->displayedName = '-'.vmText::_('COM_VIRTUEMART_REGISTER').'-';
			array_unshift($result,$toAdd);
		}

		return $result;
	}

	/**
	 * If a filter was set, get the SQL WHERE clase
	 *
	 * @return string text to add to the SQL statement
	 */
	function _getFilter()
	{
		if ($search = vRequest::getString('search', false)) {
			$db = JFactory::getDBO();
			$search = '"%' . $db->escape( $search, true ) . '%"' ;
			//$search = $db->Quote($search, false);
			$searchArray = array('name','username','email','usertype','shopper_group_name');

			$where = ' WHERE ';
			foreach($searchArray as $field){
				$where.= ' `'.$field.'` LIKE '.$search.' OR ';
			}
			$where = substr($where,0,-3);
			return ($where);
		}
		return ('');
	}

	/**
	 * Retrieve a single address for a user
	 *
	 *  @param $_uid int User ID
	 *  @param $_virtuemart_userinfo_id string Optional User Info ID
	 *  @param $_type string, addess- type, ST (ShipTo, default) or BT (BillTo). Empty string to ignore
	 */
	function getUserAddressList($_uid = 0, $_type = 'ST',$_virtuemart_userinfo_id = -1){

		//Todo, add perms, allow admin to see 0 entries.
		if($_uid==0 and $this->_id==0){
			return array();
		}
		$_q = 'SELECT * FROM #__virtuemart_userinfos  WHERE virtuemart_user_id="' . (($_uid==0)?$this->_id:(int)$_uid) .'"';
		if ($_virtuemart_userinfo_id !== -1) {
			$_q .= ' AND virtuemart_userinfo_id="'.(int)$_virtuemart_userinfo_id.'"';
		} else {
			if ($_type !== '') {
				$_q .= ' AND address_type="'.$_type.'"';
			}
		}
 		//vmdebug('getUserAddressList execute '.$_q);
		return ($this->_getList($_q));
	}

	/**
	 * Retrieves the Customer Number of the user specified by ID
	 *
	 * @param int $_id User ID
	 * @return string Customer Number
	 */
	private $customer_number = 0;
	public function getCustomerNumberById()
	{
		if($this->customer_number===0){
			$_q = "SELECT `customer_number` FROM `#__virtuemart_vmusers` "
				."WHERE `virtuemart_user_id`='" . $this->_id . "' ";
			$_r = $this->_getList($_q);

			if(!empty($_r[0])){
				$this->customer_number = $_r[0]->customer_number;
			}else {
				$this->customer_number = false;
			}
		}

		return $this->customer_number;
	}

	/**
	 * Get the number of active Super Admins
	 *
	 * @return integer
	 */
	function getSuperAdminCount(){

		$db = JFactory::getDBO();
		if(JVM_VERSION>1){
			$q = ' SELECT COUNT(us.id)  FROM #__users as us '.
				' INNER JOIN #__user_usergroup_map as um ON us.id = um.user_id ' .
				' INNER JOIN #__usergroups as ug ON um.group_id = ug.id ' .
				' WHERE ug.id = "8" AND block = "0" ';
		} else {
			$q = 'SELECT COUNT(id) FROM #__users'
				. ' WHERE gid = ' . __SUPER_ADMIN_GID . ' AND block = 0';
		}

		$db->setQuery($q);
		return ($db->loadResult());
	}




	/**
	 * Return a list of Joomla ACL groups.
	 *
	 * The returned object list includes a group anme and a group name with spaces
	 * prepended to the name for displaying an indented tree.
	 *
	 * @author RickG
	 * @return ObjectList List of acl group objects.
	 */
	function getAclGroupIndentedTree(){

		//TODO check this out

		$name = 'title';
		$as = '`';
		$table = '#__usergroups';
		$and = '';

		//Ugly thing, produces Select_full_join
		$query = 'SELECT `node`.`' . $name . $as . ', CONCAT(REPEAT("&nbsp;&nbsp;&nbsp;", (COUNT(`parent`.`' . $name . '`) - 1)), `node`.`' . $name . '`) AS `text` ';
		$query .= 'FROM `' . $table . '` AS node, `' . $table . '` AS parent ';
		$query .= 'WHERE `node`.`lft` BETWEEN `parent`.`lft` AND `parent`.`rgt` ';
		$query .= $and;
		$query .= 'GROUP BY `node`.`' . $name . '` ';
		$query .= ' ORDER BY `node`.`lft`';

		$db = JFactory::getDBO();
		$db->setQuery($query);
		//$app = JFactory::getApplication();
		//$app -> enqueueMessage($db->getQuery());
		$objlist = $db->loadObjectList();
		// 		vmdebug('getAclGroupIndentedTree',$objlist);
		return $objlist;
	}
}


//No Closing tag

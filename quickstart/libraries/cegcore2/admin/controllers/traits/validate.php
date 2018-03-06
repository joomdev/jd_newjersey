<?php
/**
* COMPONENT FILE HEADER
**/
namespace G2\A\C\T;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
trait Validate {
	
	function _vupdate($v, $update_fld = 'validated'){
		$Extension = new \G2\A\M\Extension();
		
		$ext = $Extension->where('name', $this->extension)->select('first', ['json' => ['settings']]);
		if(empty($ext)){
			$ext = [];
			$ext['Extension']['name'] = $this->extension;
			$ext['Extension']['enabled'] = 1;
		}
		$ext['Extension']['settings'][$update_fld] = $v;
		$ext['Extension']['settings']['vdomain'] = \G2\L\Url::domain(false);
		$result = $Extension->save($ext['Extension'], ['json' => ['settings']]);
		return $result;
	}
	
	function validate(){
		$domain = \G2\L\Url::domain(false);//str_replace(array('http://', 'https://'), '', \G2\L\Url::domain());
		$this->set('domain', $domain);
		if(isset($this->data['trial'])){
			$status = (string)\GApp::extension($this->extension)->valid('', true);
			if(is_numeric($status) AND strlen($status) > 1){
				\GApp::session()->flash('error', 'Trial mode has been activated before.');
				$this->redirect(r2('index.php?ext='.$this->extension));
			}else{
				$this->_vupdate(time() + (10 * 24 * 60 * 60), 'validated');
				$this->redirect(r2('index.php?ext='.$this->extension));
			}
		}
		if(!empty($this->data['license_key'])){
			
			$fields = '';
			//$postfields = array();
			unset($this->data['option']);
			unset($this->data['act']);
			foreach($this->data as $key => $value){
				$fields .= "$key=".urlencode($value)."&";
			}
			
			$update_fld = 'validated';
			if(strpos($this->data['license_key'], '@') !== false){
				$update_fld = explode('@', $this->data['license_key'])[0];
			}
			
			$target_url = 'http://www.chronoengine.com/index.php?option=com_chronocontact&task=extra&chronoformname=validateLicense&ver=6&api=3';
			
			if(ini_get('allow_url_fopen')){
				$output = file_get_contents($target_url.'&'.rtrim($fields, "& "));
			}else if(function_exists('curl_init')){
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $target_url);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_TIMEOUT, 10);
				curl_setopt($ch, CURLOPT_POSTFIELDS, rtrim($fields, "& "));
				$output = curl_exec($ch);
				curl_close($ch);
			}else{
				\GApp::session()->flash('error', 'Please enable the curl library on your server in order to be able to connect to the chronoengine.com web server');
				$this->redirect(r2('index.php?ext='.$this->extension));
			}
			
			$validstatus = $output;
			
			if(strpos($validstatus, 'valid') === 0){
				$valresults = explode(':', $validstatus, 2);
				$valprods = json_decode($valresults[1], true);
				$result = false;
				
				$prod = str_replace('chrono', '', $this->extension);
				foreach($valprods as $valprod){
					if(!empty($valprod['ext']) AND $valprod['ext'] == $prod){
						$result = $this->_vupdate(1, 'validated');
					}else if(!empty($valprod['plg'])){
						$result = $this->_vupdate(1, 'validated_'.$valprod['plg']);
					}
				}
				
				if($result){
					\GApp::session()->flash('success', 'Validated successfully.');
					$this->redirect(r2('index.php?ext='.$this->extension));
				}else{
					\GApp::session()->flash('error', 'Validation error.');
				}
			}else if($validstatus == 'invalid'){
				\GApp::session()->flash('error', 'Validation error, you have provided incorrect data.');
			}else{
				if(!empty($this->data['serial_number'])){
					$blocks = explode("-", trim($this->data['serial_number']));
					$hash = md5($this->data('license_key').str_replace('www.', '', $domain).$blocks[3]);
					if(substr($hash, 0, 7) == $blocks[4]){
						$result = $this->_vupdate(1, 'validated');
						
						if($result){
							\GApp::session()->flash('success', 'Validated successfully.');
							$this->redirect(r2('index.php?ext='.$this->extension));
						}else{
							\GApp::session()->flash('error', 'Validation error.');
						}
					}else{
						\GApp::session()->flash('error', 'Serial number invalid!');
					}
				}
				
				\GApp::session()->flash('error', 'We could not connect to the chronoengine.com web server.');
				$this->redirect(r2('index.php?ext='.$this->extension));
			}
		}
		
		$this->set('ext_name', $this->extension);
		$this->view = 'views.common.validateinstall';
	}
}
?>
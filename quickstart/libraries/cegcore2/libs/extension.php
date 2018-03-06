<?php
/**
* ChronoCMS version 1.0
* Copyright (c) 2012 ChronoCMS.com, All rights reserved.
* Author: (ChronoCMS.com Team)
* license: Please read LICENSE.txt
* Visit http://www.ChronoCMS.com for regular updates and information.
**/
namespace G2\L;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
class Extension {
	var $name = '';
	var $settings = null;
	
	function __construct($ext){
		$this->name = $ext;
	}
	
	public static function getInstance($ext){
		static $instances;
		if(!isset($instances)){
			$instances = array();
		}
		if(empty($instances[$ext])){
			$instances[$ext] = new self($ext);
			return $instances[$ext];
		}else{
			return $instances[$ext];
		}
	}

	public function settings(){
		if(!empty($this->settings)){
			return $this->settings;
		}else{
			$Extension = new \G2\A\M\Extension();
			$settings = $Extension->where('name', $this->name)->select('first', ['json' => ['settings']]);
			if(!empty($settings['Extension']['settings'])){
				return $this->settings = new Obj($settings['Extension']['settings']);
			}else{
				return $this->settings = new Obj([]);
			}
		}
	}
	
	public function save_settings(){
		$settings = $this->settings();
		$Extension = new \G2\A\M\Extension();
		
		$exists = $Extension->where('name', $this->name)->select('first');
		if(!empty($exists)){
			return $Extension->where('name', $this->name)->update(['settings' => $settings->data], ['json' => ['settings']]);
		}else{
			return $Extension->insert(['name' => $this->name, 'settings' => $settings->data], ['json' => ['settings']]);
		}
	}
	
	public function valid($group = '', $full = false){
		$settings = $this->settings();
		$act = 'validated';
		if(!empty($group)){
			$act .= '_'.$group;
		}
		
		$vdomain = $settings->get('vdomain', false);
		if($vdomain !== false){
			if($vdomain != \G2\L\Url::domain(false)){
				return false;
			}
		}
		
		if($full){
			return $settings->get($act);
		}
		
		$status = $settings->get($act, 0);
		if(strlen($status) > 1){
			if(time() > $status){
				return false;
			}else{
				return (int)ceil(($status - time())/(24 * 60 * 60));
			}
		}else{
			return !empty($status);
		}
		
		return ((bool)$settings->get($act, 0) === true);
	}
}
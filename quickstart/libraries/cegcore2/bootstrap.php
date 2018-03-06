<?php
/**
* ChronoCMS version 1.0
* Copyright (c) 2012 ChronoCMS.com, All rights reserved.
* Author: (ChronoCMS.com Team)
* license: Please read LICENSE.txt
* Visit http://www.ChronoCMS.com for regular updates and information.
**/
namespace G2;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;

class Bootstrap {
	const VERSION = 1;
	const UPDATE = 2;
	public static function initialize($plathform = '', $params = array()){
		switch ($plathform){
			default:
				//CONSTANTS
				\G2\Globals::set('FRONT_PATH', dirname(__FILE__).DS);
				\G2\Globals::set('ADMIN_PATH', dirname(__FILE__).DS.'admin'.DS);
				//initialize language
				\G2\L\Lang::initialize();
				//SET ERROR CONFIG
				if((int)L\Config::get('error.reporting') != 1){
					error_reporting((int)L\Config::get('error.reporting'));
				}
				if((bool)L\Config::get('error.debug') === true){
					\G2\L\Error::initialize();
				}
				//timezone
				date_default_timezone_set(L\Config::get('site.timezone', 'UTC'));
			break;
		}
		
		if($plathform == 'joomla'){
			$mainframe = \JFactory::getApplication();
			//database
			\G2\L\Config::set('db.host', $mainframe->getCfg('host'));
			$dbtype = ($mainframe->getCfg('dbtype') == 'mysqli' ? 'mysql' : $mainframe->getCfg('dbtype'));
			\G2\L\Config::set('db.type', $dbtype);
			\G2\L\Config::set('db.name', $mainframe->getCfg('db'));
			\G2\L\Config::set('db.user', $mainframe->getCfg('user'));
			\G2\L\Config::set('db.pass', $mainframe->getCfg('password'));
			\G2\L\Config::set('db.prefix', $mainframe->getCfg('dbprefix'));
			//mails
			\G2\L\Config::set('mail.from_name', $mainframe->getCfg('fromname'));
			\G2\L\Config::set('mail.from_email', $mainframe->getCfg('mailfrom'));
			
			if((int)$mainframe->getCfg('smtpauth') != 0){
				\G2\L\Config::set('mail.smtp.username', $mainframe->getCfg('smtpuser'));
				\G2\L\Config::set('mail.smtp.password', $mainframe->getCfg('smtppass'));
			}
			\G2\L\Config::set('mail.smtp.host', $mainframe->getCfg('smtphost'));
			\G2\L\Config::set('mail.smtp.security', $mainframe->getCfg('smtpsecure'));
			\G2\L\Config::set('mail.smtp.port', $mainframe->getCfg('smtpport'));
			//set timezone
			//date_default_timezone_set($mainframe->getCfg('offset'));
			\G2\L\Config::set('site.timezone', $mainframe->getCfg('offset'));
			//site title
			\G2\L\Config::set('site.title', $mainframe->getCfg('sitename'));
			//\G2\Globals::set('app', 'joomla');
			
			\G2\Globals::set('FRONT_URL', \JFactory::getURI()->root().'libraries/cegcore2/');
			\G2\Globals::set('ADMIN_URL', \JFactory::getURI()->root().'libraries/cegcore2/admin/');
			\G2\Globals::set('ROOT_URL', \JFactory::getURI()->root());
			
			\G2\Globals::set('ROOT_PATH', dirname(dirname(dirname(__FILE__))).DS);
			
			$lang = \JFactory::getLanguage();
			\G2\L\Config::set('site.language', str_replace('-', '_', $lang->getTag()));
		}else if($plathform == 'wordpress'){
			global $wpdb;
			\G2\L\Config::set('db.host', DB_HOST);
			$dbtype = 'mysql';
			\G2\L\Config::set('db.type', $dbtype);
			\G2\L\Config::set('db.name', DB_NAME);
			\G2\L\Config::set('db.user', DB_USER);
			\G2\L\Config::set('db.pass', DB_PASSWORD);
			\G2\L\Config::set('db.prefix', $wpdb->prefix);
			
			//set timezone
			\G2\L\Config::set('site.timezone', !empty(get_option('timezone_string')) ? get_option('timezone_string') : 'UTC');
			//site title
			\G2\L\Config::set('site.title', get_bloginfo('name'));
			
			//\G2\Globals::set('app', 'wordpress');
			
			\G2\Globals::set('FRONT_URL', plugins_url().'/'.$params['component'].'/cegcore2/');
			\G2\Globals::set('ADMIN_URL', plugins_url().'/'.$params['component'].'/cegcore2/admin/');
			\G2\Globals::set('ROOT_URL', site_url().'/');
			
			\G2\Globals::set('ROOT_PATH', dirname(dirname(dirname(__FILE__))).DS);
			
			\G2\L\Config::set('site.language', get_bloginfo('language'));
			//change the default page parameter string because WP uses the param "page"
			//\G2\L\Config::set('page_url_param_name', 'page_num');
			
			if(function_exists('wp_magic_quotes')){
				$stripslashes_wp = function (&$value){
					$value = stripslashes($value);
				};
				array_walk_recursive($_GET, $stripslashes_wp);
				array_walk_recursive($_POST, $stripslashes_wp);
				array_walk_recursive($_COOKIE, $stripslashes_wp);
				array_walk_recursive($_REQUEST, $stripslashes_wp);
			}
		}else{
			//\G2\Globals::set('app', '');
			
			\G2\Globals::set('FRONT_URL', \G2\L\Url::root());
			\G2\Globals::set('ADMIN_URL', \G2\L\Url::root().'admin/');
			\G2\Globals::set('ROOT_URL', \G2\Globals::get('FRONT_URL'));
			
			\G2\Globals::set('ROOT_PATH', dirname(__FILE__).DS);
		}
		\G2\Globals::set('CURRENT_PATH', \G2\Globals::get(''.strtoupper(GCORE_SITE).'_PATH'));
		\G2\Globals::set('CURRENT_URL', \G2\Globals::get(''.strtoupper(GCORE_SITE).'_URL'));
	}
}
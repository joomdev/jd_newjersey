<?php
	/*
	# mod_sp_tweet - Twitter Module by JoomShaper.com
	# -----------------------------------------------
	# Author    JoomShaper http://www.joomshaper.com
	# Copyright (C) 2010 - 2014 JoomShaper.com. All Rights Reserved.
	# license - GNU/GPL V2 or Later
	# Websites: http://www.joomshaper.com
	*/

	// no direct access
	defined('_JEXEC') or die('Restricted access');

	include_once('library/TwitterAPIExchange.php');

	class modSPTwitter{
		private $moduleID;
		private $params;
		private $cacheParams=array();
		private $api;
		
		//Initiate configurations
		public function __construct($params, $id) {
			jimport('joomla.filesystem.file');
			$this->moduleID = $id;
			$this->params = $params;

			return $this;
		}


		/**
		* Simple catching functionn
		* 
		* @param string $file
		* @param string $url
		* @param array $args    
		* @param int $time   default is 900/60 = 15 min
		* @param mixed $onError   string function or array(object, method )
		* @return string
		*/
		private function Cache( $file,$time=900, $onerror='') {
			// check joomla cache dir writable
			$dir = basename(dirname(__FILE__));
			if (is_writable(JPATH_CACHE)) {
				// check cache dir or create cache dir
				if (!file_exists(JPATH_CACHE.'/'.$dir)) mkdir(JPATH_CACHE.'/'.$dir.'/', 0755);
				$cache_file = JPATH_CACHE.'/'.$dir.'/'.$this->moduleID.'-'.$file;
				// check cache file, if not then write cache file
				if ( !file_exists($cache_file) )
				{
					$data = $this->getData();
					JFile::write($cache_file, $data);
				} //if cache file expires, then write cache
				elseif ( filesize($cache_file) == 0 || ((filemtime($cache_file) + (int) $time ) < time()) ) {
					$data = $this->getData();
					JFile::write($cache_file, $data);
				}
				$data =  JFile::read($cache_file);
				$params['file'] = $cache_file;
				$params['data'] = $data;
				if( !empty($onerror) ) call_user_func($onerror, $params);
				return $data;
			} else {
				return $this->getData();
			}
		}

		/*
		* get twitter datas
		*/		
		private function getData()
		{

			$settings = array(
				'consumer_key' => $this->params->get('consumer_key'),
				'consumer_secret' => $this->params->get('consumer_key_secret'),
				'oauth_access_token' => $this->params->get('access_token'),
				'oauth_access_token_secret' => $this->params->get('access_token_secret')
				);

			if( empty($settings['consumer_key']) ){
				JError::raiseNotice( 100, 'Twitter Consumer Key not defined.' );
				return NULL;
			} 			
			elseif( empty($settings['consumer_secret']) ){
				JError::raiseNotice( 100, 'Twitter Consumer secret not defined.' );
				return NULL;
			} 
			elseif( empty($settings['oauth_access_token']) ){
				JError::raiseNotice( 100, 'Twitter access token not defined.' );
				return NULL;
			} 
			elseif( empty($settings['oauth_access_token_secret']) ){
				JError::raiseNotice( 100, 'Twitter access token secret not defined.' );
				return NULL;
			} 



			$url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
			$getfield = '?include_entities=true&include_rts=true&screen_name='.$this->params->get('username').'&count='. $this->params->get('tweets');
			$requestMethod = 'GET';
			$this->api = new SPTwitterAPIExchange($settings);
			return $this->api->setGetfield($getfield)->buildOauth($url, $requestMethod)->performRequest();

		}

		/*
		* function onError
		*/	
		public function onError($params)
		{
			$data = json_decode($params['data'],true);
			if( isset( $data['errors'] ) or  isset( $data['error'] ) ) JFile::Delete($params['file']);
		}

		/*
		* Function to get tweets
		*/			
		public function tweets() {
			if( $this->params->get('module_cache')==='1' ) 
				$data = $this->Cache( 'twitter.json', $this->params->get('cache_time'), array($this,'onError') );
			else 
				$data = $this->getData();

			return json_decode($data, true);
		}

		/*
		* Prepare feeds
		*/			
		public function prepareTweet($string) {
			//Url
			$pattern = '/((ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?)/i';
			$replacement = '<a target="' . $this->params->get('target') . '" class="tweet_url" href="$1">$1</a>';
			$string = preg_replace($pattern, $replacement, $string);

			//Search
			if ($this->params->get('linked_search')==1) {
				$pattern = '/[\#]+([A-Za-z0-9-_]+)/i';
				$replacement = ' <a target="' . $this->params->get('target') . '" class="tweet_search" href="http://search.twitter.com/search?q=$1">#$1</a>';
				$string = preg_replace($pattern, $replacement, $string);
			}

			//Mention
			if ($this->params->get('linked_mention')==1) {
				$pattern = '/\s[\@]+([A-Za-z0-9-_]+)/i';
				$replacement = ' <a target="' . $this->params->get('target') . '" class="tweet_mention" href="http://twitter.com/$1">@$1</a>';
				$string = preg_replace($pattern, $replacement, $string);	
			}

			//Mention
			if ($this->params->get('email_linked')==1) {
				$pattern = '/\s([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4})/i';
				$replacement = ' <a target="' . $this->params->get('target') . '" class="tweet_email" href="mailto:$1">$1</a>';
				$string = preg_replace($pattern, $replacement, $string);
			}
			return $string;
		}

		//Function for converting time
		public function timeago($timestamp) {
			$time_arr 		= explode(" ",$timestamp);
			$year 			= $time_arr[5];
			$day 			= $time_arr[2];
			$time 			= $time_arr[3];
			$time_array 	= explode(":",$time);
			$month_name 	= $time_arr[1];
			$month = array (
				'Jan' => 1,
				'Feb' => 2,
				'Mar' => 3,
				'Apr' => 4,
				'May' => 5,
				'Jun' => 6,
				'Jul' => 7,
				'Aug' => 8,
				'Sep' => 9,
				'Oct' => 10,
				'Nov' => 11,
				'Dec' => 12
			);

			$delta = gmmktime(0, 0, 0, 0, 0) - mktime(0, 0, 0, 0, 0);
			$timestamp = mktime($time_array[0], $time_array[1], $time_array[2], $month[$month_name], $day, $year);
			$etime = time() - ($timestamp + $delta);
			if ($etime < 1) {
				return '0 seconds';
			}

			$a = array( 12 * 30 * 24 * 60 * 60  =>  'YEAR',
				30 * 24 * 60 * 60       =>  'MONTH',
				24 * 60 * 60            =>  'DAY',
				60 * 60                 =>  'HOUR',
				60                      =>  'MINUTE',
				1                       =>  'SECOND'
			);

			foreach ($a as $secs => $str) {
				$d = $etime / $secs;
				if ($d >= 1) {
					$r = round($d);
					return $r . ' ' . JText::_($str . ($r > 1 ? 'S' : ''));
				}
			}
		}
	}
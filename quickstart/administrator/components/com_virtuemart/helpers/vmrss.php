<?php
/**
 * RSS helper class
 *
 * @package	VirtueMart
 * @subpackage Helpers
 * @author Max Milberes
 * @author Valerie Isaksen
 * @copyright Copyright (c) 2014 VirtueMart Team and author. All rights reserved.
 */
defined('_JEXEC') or die('Restricted access');
defined('DS') or define('DS', DIRECTORY_SEPARATOR);

class vmRSS{

	/**
	 * Get cached feed
	 * @author valerie isaksen, Max Milbers
	 * @param $rssUrl
	 * @param $max
	 * @param $cache_time in minutes
	 * @return mixed
	 */
	static public function getCPsRssFeed($rssUrl,$max, $cache_time=2880) {  // 2880 = 2days

		$cache = VmConfig::getCache ('com_virtuemart_rss');

		$cache->setLifeTime($cache_time);
		$cache->setCaching (1);
		$feeds = $cache->call (array('vmRSS', 'getRssFeed'), $rssUrl, $max, $cache_time);

		return $feeds;
	}

	/**
	 * Returns the RSS feed from Extensions.virtuemart.net
	 * @return mixed
	 */
	public static $extFeeds = false;
	static public function getExtensionsRssFeed($items =15, $cache_time = 2880) {
		if (empty(self::$extFeeds)) {
			try {
				self::$extFeeds = self::getCPsRssFeed( "https://extensions.virtuemart.net/?format=feed&type=rss", $items,$cache_time );
				//self::$extFeeds =  self::getRssFeed("http://extensions.virtuemart.net/?format=feed&type=rss", 15);
			} catch (Exception $e) {
				echo 'Were not able to parse extension feed';
			}
		}
		return self::$extFeeds;
	}

	/**
	 * Returns the RSS feed from virtuemart.net
	 * @return mixed
	 */
	public static $vmFeeds = false;
	static public function getVirtueMartRssFeed() {
 		if (empty(self::$vmFeeds)) {
			try {
				self::$vmFeeds =  self::getCPsRssFeed("https://virtuemart.net/news/list-all-news?format=feed&type=rss", 5, 240);
			} catch (Exception $e) {
				echo 'Where not able to parse news feed';
			}
		}
		return self::$vmFeeds;
	}

	/**
	 * @param $rssURL
	 * @param $max
	 * @return array|bool
	 */
	static public function getRssFeed($rssURL, $max, $cache_time) {

		$rssFeedFact = new JFeedFactory();
		$rssFeed = $rssFeedFact->getFeed($rssURL);
		$i = 0;
		$feeds = array();
		while($rssFeed->offsetExists($i) and $item = $rssFeed->offsetGet($i) and $i<$max){
			$feed = new StdClass();
			$feed->link = $item->uri;
			$feed->title = $item->title;
			$feed->description = $item->content;
			$feeds[] = $feed;
			$i++;
		}

		return $feeds;

	}


}


// pure php no closing tag

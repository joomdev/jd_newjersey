<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage
* @author RolandD, Max Milbers
* @link https://virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: ratings.php 9500 2017-04-11 19:50:26Z Milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

if (!class_exists ('VmModel')){
	require(VMPATH_ADMIN . DS . 'helpers' . DS . 'vmmodel.php');
}

/**
 * Model for VirtueMart Products
 *
 * @package VirtueMart
 * @author RolandD
 */
class VirtueMartModelRatings extends VmModel {

	var $_productBought = array();

	private static $_select = TRUE; //' `u`.*,`pr`.*,`l`.`product_name`,`rv`.`vote`, IFNULL(`u`.`name`, `pr`.`customer`) AS customer ';
	private static $_jTables = ' LEFT JOIN `#__virtuemart_rating_votes` AS `rv` on
			(`pr`.`virtuemart_rating_vote_id` IS NOT NULL AND `rv`.`virtuemart_rating_vote_id`=`pr`.`virtuemart_rating_vote_id` ) XOR
			(`pr`.`virtuemart_rating_vote_id` IS NULL AND (`rv`.`virtuemart_product_id`=`pr`.`virtuemart_product_id` and `rv`.`created_by`=`pr`.`created_by`) )
			LEFT JOIN `#__users` AS `u`	ON `pr`.`created_by` = `u`.`id` ';

	/**
	 * constructs a VmModel
	 * setMainTable defines the maintable of the model
	 * @author Max Milbers
	 */
	function __construct() {
		parent::__construct();
		$this->setMainTable('ratings');


		$layout = vRequest::getString('layout','default');
		$task = vRequest::getCmd('task','default');

		if($layout == 'list_reviews' or $task == 'listreviews'){
			vmdebug('in review list');
			if($task == 'add'){
				$myarray = array('r.created_on','virtuemart_rating_review_id','vote');
				$this->removevalidOrderingFieldName('created_on');
				$this->removevalidOrderingFieldName('product_name');
				$this->removevalidOrderingFieldName('virtuemart_rating_id');
				$this->removevalidOrderingFieldName('rating');
				$this->_selectedOrdering = 'r.created_on';
			} else {
				$myarray = array('pr.created_on','virtuemart_rating_review_id','vote');
				$this->removevalidOrderingFieldName('created_on');
				$this->removevalidOrderingFieldName('product_name');
				$this->removevalidOrderingFieldName('virtuemart_rating_id');
				$this->removevalidOrderingFieldName('rating');
				$this->_selectedOrdering = 'pr.created_on';
			}

		} else {
			$myarray = array('created_on','product_name','virtuemart_rating_id');
			$this->removevalidOrderingFieldName('pr.created_on');
			$this->removevalidOrderingFieldName('virtuemart_rating_review_id');
			$this->removevalidOrderingFieldName('vote');
			$this->_selectedOrdering = 'created_on';
		}
		$this->addvalidOrderingFieldName($myarray);


	}

	public static function getSelect(){

		if(self::$_select === TRUE){
			$collate= '';
			$collateMb4= '';
			if(JVM_VERSION>=3){
				$c = JFactory::getConfig();
				$db = JFactory::getDbo();

				$q = 'SELECT COLLATION_NAME from information_schema.columns where TABLE_SCHEMA = "'.$c->get('db').'"
				and TABLE_NAME = "'.str_replace('#__',$db->getPrefix(),'#__users').'"
				and COLUMN_NAME = "name";';
				$db->setQuery($q);
				$ru = $db->loadResult();
				if($ru){

					$collateMb4= 'COLLATE '.$ru;
					$q = 'SELECT COLLATION_NAME from information_schema.columns where TABLE_SCHEMA = "'.$c->get('db').'"
				and TABLE_NAME = "'.str_replace('#__',$db->getPrefix(),'#__virtuemart_rating_reviews').'"
				and COLUMN_NAME = "customer";';
					$db->setQuery($q);
					$r = $db->loadResult();

					if(strpos($r,'mb4')>0){
						$collate = $collateMb4;
					} else {
						$collate= 'COLLATE '.str_replace('mb4','',$ru);
					}
				}
			}
			self::$_select = ' `u`.*,`pr`.*,`l`.`product_name`,`rv`.`vote`, IFNULL(`u`.`name` '.$collateMb4.', `pr`.`customer` '.$collate.') AS customer ';
		}
		return self::$_select;
	}

    /**
     * Select the products to list on the product list page
     */
    public function getRatings() {

     	$tables = ' FROM `#__virtuemart_ratings` AS `r` JOIN `#__virtuemart_products_'.VmConfig::$vmlang.'` AS `pr`
     			ON r.`virtuemart_product_id` = pr.`virtuemart_product_id` ';

		$whereString = '';
		if(VmConfig::get('multix','none')!='none'){
			$tables .= ' LEFT JOIN  `#__virtuemart_products` as p ON r.`virtuemart_product_id` = p.`virtuemart_product_id`';
			$virtuemart_vendor_id = vmAccess::getVendorId();
			if(!empty($virtuemart_vendor_id)){
				$whereString = ' WHERE virtuemart_vendor_id="'.$virtuemart_vendor_id.'"';
			}
		}

     	$this->_data = $this->exeSortSearchListQuery(0,' r.*,pr.`product_name` ',$tables,$whereString,'',$this->_getOrdering());

     	return $this->_data;
    }


    /**
    * Load a single rating
    * @author RolandD
    */
    public function getRating($cids) {

	    if (empty($cids)) {
		    return false;
	    }

		/* First copy the product in the product table */
		$ratings_data = $this->getTable('ratings');

		/* Load the rating */
		$joinValue = array('product_name' =>'#__virtuemart_products');

	    if ($cids) {
		    $ratings_data->load ($cids[0], $joinValue, 'virtuemart_product_id');
	    }

		/* Add some variables for a new rating */
		if (vRequest::getCmd('task') == 'add') {
			$virtuemart_product_id = vRequest::getInt('virtuemart_product_id');
			if(is_array($virtuemart_product_id) && count($virtuemart_product_id) > 0){
				$virtuemart_product_id = (int)$virtuemart_product_id[0];
			} else {
				$virtuemart_product_id = (int)$virtuemart_product_id;
			}
			$ratings_data->virtuemart_product_id = $virtuemart_product_id;

			/* User ID */
			$user = JFactory::getUser();
			$ratings_data->virtuemart_user_id = $user->id;
		}

		return $ratings_data;
    }

	/**
	 * @author Max Milbers
	 * @param $virtuemart_product_id
	 * @return null
	 */
	function getReviews($virtuemart_product_id, $virtuemart_vendor_id = 0, $num_reviews = false){

	    if (empty($virtuemart_product_id)) {
		    return NULL;
	    }
		static $reviews = array();
		$hash = VmConfig::$vmlang.$virtuemart_product_id.$this->_selectedOrderingDir.$this->_selectedOrdering;
		if(!isset($reviews[$hash])){

			$jKind = 'INNER';
			if($virtuemart_vendor_id){
				$jKind = 'LEFT';
			}

			//$select = '`u`.*,`pr`.*,`l`.`product_name`,`rv`.`vote`, IFNULL(`u`.`name`, `pr`.`customer`) AS customer, `pr`.`published`';
			$tables = ' FROM `#__virtuemart_rating_reviews` AS `pr`
			'.$jKind.' JOIN `#__virtuemart_products_'.VmConfig::$vmlang.'` AS `l` ON `l`.`virtuemart_product_id` = `pr`.`virtuemart_product_id` ';
			if(!empty($virtuemart_vendor_id)){
				$tables .= 'LEFT JOIN `#__virtuemart_products` AS `p` ON `p`.`virtuemart_product_id` = `pr`.`virtuemart_product_id` ';
			}
			$tables .= self::$_jTables;
			/*$tables .= 'LEFT JOIN `#__virtuemart_rating_votes` AS `rv` on
			(`pr`.`virtuemart_rating_vote_id` IS NOT NULL AND `rv`.`virtuemart_rating_vote_id`=`pr`.`virtuemart_rating_vote_id` ) XOR
			(`pr`.`virtuemart_rating_vote_id` IS NULL AND (`rv`.`virtuemart_product_id`=`pr`.`virtuemart_product_id` and `rv`.`created_by`=`pr`.`created_by`) )';
		$tables .= 'LEFT JOIN `#__users` AS `u`	ON `pr`.`created_by` = `u`.`id`';
*/
			$whereString = ' WHERE  `pr`.`virtuemart_product_id` = "'.$virtuemart_product_id.'" ';
			if(!empty($virtuemart_vendor_id)){
				$whereString .= ' AND `p`.virtuemart_vendor_id="'.$virtuemart_vendor_id.'"';
			}
			self::$_select = self::getSelect();
			$reviews[$hash] = $this->exeSortSearchListQuery(0,self::$_select,$tables,$whereString,'',$this->_getOrdering(), '', $num_reviews);
		}


     	return $reviews[$hash];
    }


	/**
	 * @author Max Milbers
	 * @param $cids
	 * @return mixed@
	 */
	function getReview($cids, $new = false){

		if($new){
			$t = $this->getTable('products');
			$t->load($cids);
			$t->customer = '';
			$t->vote = '';
			$t->comment = '';
			$t->virtuemart_rating_review_id = null;
			$t->virtuemart_rating_vote_id = null;
			$t->created_by_alias = '';
			return $t;
		} else {
			self::$_select = self::getSelect();
			$q = 'SELECT '.self::$_select.' FROM `#__virtuemart_rating_reviews` AS `pr`
		LEFT JOIN `#__virtuemart_products_'.VmConfig::$vmlang.'` AS `l` ON `l`.`virtuemart_product_id` = `pr`.`virtuemart_product_id`';
		$q .= self::$_jTables;
 /*    	ON `p`.`virtuemart_product_id` = `pr`.`virtuemart_product_id`
		LEFT JOIN `#__virtuemart_rating_votes` as `rv` on `rv`.`virtuemart_product_id`=`pr`.`virtuemart_product_id` and `rv`.`created_by`=`pr`.`created_by`
		LEFT JOIN `#__users` AS `u`
     	ON `pr`.`created_by` = `u`.`id`*/
		$q .= 'WHERE virtuemart_rating_review_id="'.(int)$cids[0].'" ' ;


			$db = JFactory::getDBO();
			$db->setQuery($q);
			$r = $db->loadObject();
			if(!$r){
				vmdebug('getReview',$db->getQuery());
			}
			return $r;
		}

    }


    /**
     * gets a rating by a product id
     *
     * @author Max Milbers
     * @param int $product_id
     */

    function getRatingByProduct($product_id,$onlyPublished=true){
    	$q = 'SELECT * FROM `#__virtuemart_ratings` WHERE `virtuemart_product_id` = "'.(int)$product_id.'" ';
		if($onlyPublished){
			$q .= 'AND `published`="1" ';
		}
		$db = JFactory::getDBO();
		$db->setQuery($q);
		return $db->loadObject();

    }



    /**
     * gets a review by a product id
     *
     * @author Max Milbers
     * @param int $product_id
     */

    function getProductReviewForUser($product_id,$userId=0){
   		if(empty($userId)){
			$user = JFactory::getUser();
			$userId = $user->id;
    	}
		if(!empty($userId)){
			$q = 'SELECT * FROM `#__virtuemart_rating_reviews` WHERE `virtuemart_product_id` = "'.(int)$product_id.'" AND `created_by` = "'.(int)$userId.'" ';
			$db = JFactory::getDBO();
			$db->setQuery($q);
			return $db->loadObject();
		} else {
			return false;
		}

    }

	/**
	 * @deprecated
	 */
	function getReviewByProduct($product_id,$userId=0){
		return $this->getProductReviewForUser($product_id,$userId);
	}

    /**
     * gets a reviews by a product id
     *
     * @author Max Milbers
     * @param int $product_id
     */

	function getReviewsByProduct($product_id){
   		if(empty($userId)){
			$user = JFactory::getUser();
			$userId = $user->id;
    	}
		$q = 'SELECT * FROM `#__virtuemart_rating_reviews` WHERE `virtuemart_product_id` = "'.(int)$product_id.'" ';
		$db = JFactory::getDBO();
		$db->setQuery($q);
		return $db->loadObjectList();
    }

    /**
     * gets a vote by a product id and userId
     *
     * @author Max Milbers
     * @param int $product_id
     */

    function getVoteByProduct($product_id,$userId=0){

    	if(empty($userId)){
			$user = JFactory::getUser();
			$userId = $user->id;
    	}
		$q = 'SELECT * FROM `#__virtuemart_rating_votes` WHERE `virtuemart_product_id` = "'.(int)$product_id.'" AND `created_by` = "'.(int)$userId.'" ';
		$db = JFactory::getDBO();
		$db->setQuery($q);
		return $db->loadObject();

    }

	function getAverageVotesByProductId($prId){
		$q = 'SELECT AVG(vote) FROM `#__virtuemart_rating_votes` WHERE `virtuemart_product_id` = "'.(int)$prId.'" ';
		$db = JFactory::getDBO();
		$db->setQuery($q);
		return $db->loadResult();
	}

	function getVoteById($id){
		$q = 'SELECT * FROM `#__virtuemart_rating_votes` WHERE `virtuemart_rating_vote_id` = "'.(int)$id.'"  ';
		$db = JFactory::getDBO();
		$db->setQuery($q);
		return $db->loadObject();
	}

    /**
    * Save a rating
    * @author  Max Milbers
    */
    public function saveRating($data=0) {

		//Check user_rating
		$maxrating = VmConfig::get('vm_maximum_rating_scale',5);
		$virtuemart_product_id = vRequest::getInt('virtuemart_product_id',0);
		if(empty($virtuemart_product_id)) {
			vmError( 'Cant save rating/review/vote without vote/product_id' );
			return FALSE;
		}

		if(empty($data)) $data = vRequest::getPost();

		$app = JFactory::getApplication();
		if( $app->isSite() ){
			$user = JFactory::getUser();
			$data['created_by'] = $user->id;
			$allowReview = $this->allowReview($virtuemart_product_id);
			$allowRating = $this->allowRating($virtuemart_product_id);

			if (VmConfig::get ('reviews_autopublish', 1)) {
				$data['published'] = 1;
			} else {
				$model = new VmModel();
				$product = $model->getTable('products');
				$product->load($data['virtuemart_product_id']);
				$vendorId = vmAccess::isSuperVendor();
				if(!vmAccess::manager() or $vendorId!=$product->virtuemart_vendor_id){
					$data['published'] = 0;
				}
			}


		} else {
			if(empty($data['created_by']) and !empty($data['customer']) and vmAccess::manager('ratings')){
				//$userId = -1;
				$data['created_by'] = -1;
			} else {
				//$userId = $data['created_by'];
			}

			$allowReview = true;
			$allowRating = true;
		}
		vmdebug('bindChecknStore rating_votes',$data);

		if($allowRating){
			//normalize the rating
			if ($data['vote'] < 0) {
				$data['vote'] = 0;
			}
			if ($data['vote'] > ($maxrating + 1)) {
				$data['vote'] = $maxrating;
			}

			if (!class_exists ('ShopFunctions')){
				require(VMPATH_ADMIN . DS . 'helpers' . DS . 'shopfunctions.php');
			}
			$data['lastip'] = ShopFunctions::getClientIP();

			$maskIP = VmConfig::get('maskIP','last');
			if($maskIP=='last'){
				$rpos = strrpos($data['lastip'],'.');
				$data['lastip'] = substr($data['lastip'],0,($rpos+1)).'xx';
			}

			$data['vote'] = (int) $data['vote'];

			$rating = $this->getRatingByProduct($data['virtuemart_product_id']);
			vmdebug('$rating',$rating);


			if($data['created_by']>0 ){
				$vote = $this->getVoteByProduct($data['virtuemart_product_id'],$data['created_by']);
				vmdebug('getVoteByProduct $vote',$vote);

			} else if(!empty($data['virtuemart_rating_vote_id'])){
				$vote = $this->getVoteById($data['virtuemart_rating_vote_id']);
				vmdebug('getVoteById $vote',$vote);
			} else {
				$vote = false;
			}

			$data['virtuemart_rating_vote_id'] = empty($vote->virtuemart_rating_vote_id)? 0: $vote->virtuemart_rating_vote_id;

			if(isset($data['vote'])){
				$votesTable = $this->getTable('rating_votes');
				vmdebug('bindChecknStore rating_votes',$data);
				$res = $votesTable->bindChecknStore($data,TRUE);
				if(!$res){
					vmError(get_class( $this ).'::Error store votes ');
				}
			}

			if(!empty($rating->rates) && empty($vote) ){
				$data['rates'] = $rating->rates + $data['vote'];
				$data['ratingcount'] = $rating->ratingcount+1;
				//$data['rating'] = $data['rates']/$data['ratingcount'];
			}
			else {
				if (!empty($rating->rates) && !empty($vote->vote)) {
					$data['rates'] = $rating->rates - $vote->vote + $data['vote'];
					$data['ratingcount'] = $rating->ratingcount;
					//Lets recalculate it
					//$data['rating'] = $this->getAverageVotesByProductId($data['virtuemart_product_id']);

				}
				else {
					//$data['rating'] = $data['rates'] = $data['vote'];
					$data['ratingcount'] = 1;
				}
			}

			$data['rating'] = $this->getAverageVotesByProductId($data['virtuemart_product_id']);
			/*if(empty($data['rates']) || empty($data['ratingcount']) ){
				$data['rating'] = 0;
			} else {
				$data['rating'] = $data['rates']/$data['ratingcount'];
			}*/

			$data['virtuemart_rating_id'] = empty($rating->virtuemart_rating_id)? 0: $rating->virtuemart_rating_id;
			vmdebug('saveRating $data to table ratings',$data);
			$rating = $this->getTable('ratings');
			$res = $rating->bindChecknStore($data,TRUE);
			if(!$res){
				vmError(get_class( $this ).'::Error store rating ');
			}
		}

		if($allowReview and !empty($data['comment'])){
			//if(!empty($data['comment'])){
			$data['comment'] = substr($data['comment'], 0, VmConfig::get('vm_reviews_maximum_comment_length', 2000)) ;

			// no HTML TAGS but permit all alphabet
			$value =	preg_replace('@<[\/\!]*?[^<>]*?>@si','',$data['comment']);//remove all html tags
			$value =	(string)preg_replace('#on[a-z](.+?)\)#si','',$value);//replace start of script onclick() onload()...
			$value = trim(str_replace('"', ' ', $value),"'") ;
			$data['comment'] =	(string)preg_replace('#^\'#si','',$value);//replace ' at start
			$data['comment'] = nl2br($data['comment']);  // keep returns
			//set to defaut value not used (prevent hack)
			$data['review_ok'] = 0;
			$data['review_rating'] = 0;
			$data['review_editable'] = 0;
			// Check if ratings are auto-published (set to 0 prevent injected by user)
			//


			if($data['created_by']>0 ){
				$review = $this->getProductReviewForUser($data['virtuemart_product_id'],$data['created_by']);
			} else if(!empty($data['virtuemart_rating_review_id'])){
				$review = $this->getReview(array($data['virtuemart_rating_review_id']));
			}


			if(!empty($review->review_rates)){
				$data['review_rates'] = $review->review_rates + $data['vote'];
			} else {
				$data['review_rates'] = $data['vote'];
			}

			if(!empty($review->review_ratingcount)){
				$data['review_ratingcount'] = $review->review_ratingcount+1;
			} else {
				$data['review_ratingcount'] = 1;
			}

			$data['review_rating'] = $data['vote'];// $data['review_rates']/$data['review_ratingcount'];

			$data['virtuemart_rating_review_id'] = empty($review->virtuemart_rating_review_id)? 0: $review->virtuemart_rating_review_id;

			$reviewTable = $this->getTable('rating_reviews');
			$res = $reviewTable->bindChecknStore($data,TRUE);
			if(!$res){
				vmError(get_class( $this ).'::Error store review ');
			}
		}
		return $data['virtuemart_rating_review_id'];


	}
    /**
    * removes a product and related table entries
    *
    * @author Max Milberes
    */
    public function remove($ids) {

		if(!vmAccess::manager('ratings.delete')){
			vmWarn('Insufficient permissions to delete category');
			return false;
		}
    	$rating = $this->getTable($this->_maintablename);
    	$review = $this->getTable('rating_reviews');
    	$votes = $this->getTable('rating_votes');

    	$ok = TRUE;
    	foreach($ids as $id) {

    		$rating->load($id);
    		$prod_id = $rating->virtuemart_product_id;

    		if (!$rating->delete($id)) {
    			vmError(get_class( $this ).'::Error deleting ratings ');
    			$ok = FALSE;
    		}

    		if (!$review->delete($prod_id,'virtuemart_product_id')) {
    			vmError(get_class( $this ).'::Error deleting review ');
    			$ok = FALSE;
    		}

    		if (!$votes->delete($prod_id,'virtuemart_product_id')) {
    			vmError(get_class( $this ).'::Error deleting votes ');
    			$ok = FALSE;
    		}
    	}

    	return $ok;

    }



    /**
	* Returns the number of reviews assigned to a product
	*
	* @author RolandD
	* @param int $pid Product ID
	* @return int
	*/
	public function countReviewsForProduct($pid) {
		$db = JFactory::getDBO();
		$q = "SELECT COUNT(*) AS total
			FROM #__virtuemart_rating_reviews
			WHERE virtuemart_product_id=".(int)$pid;
		$db->setQuery($q);
		$reviews = $db->loadResult();
		return $reviews;
	}

	public function showReview($product_id){

		return $this->show($product_id, VmConfig::get('showReviewFor','all'));
	}

	public function showRating($product_id = 0){
		return $this->show($product_id, VmConfig::get('showRatingFor','all'));
	}

	public function allowReview($product_id){
		return $this->show($product_id, VmConfig::get('reviewMode','bought'));
	}

	public function allowRating($product_id){
		return $this->show($product_id, VmConfig::get('ratingMode','bought'));
	}

	/**
	 * Decides if the rating/review should be shown on the FE
	 * @author Max Milbers
	 */
	private function show($product_id, $show){

		//dont show
		if($show == 'none'){
			return false;
		}
		//show all
		else {
			if ($show == 'all') {
				return true;
			}
			//show only registered
			else {
				if ($show == 'registered') {
					$user = JFactory::getUser ();
					return !empty($user->id);
				}
				//show only registered && who bought the product
				else {
					if ($show == 'bought') {

						if (empty($product_id)) {
							return false;
						}

						if (isset($this->_productBought[$product_id])) {
							return $this->_productBought[$product_id];
						}

						if(!class_exists('vmCrypt')){
							require(VMPATH_ADMIN.DS.'helpers'.DS.'vmcrypt.php');
						}
						$key = vmCrypt::encrypt('productBought'.$product_id);
						$count = JFactory::getApplication()->input->cookie->getString($key, false);
						if($count){
							//check, somehow broken, atm
							$v = vmCrypt::encrypt($key);
							if($v!=$count){
								$count = false;
							}
						}

						if(!$count){
							$user = JFactory::getUser ();
							if(empty($user->id)) return false;

							$rr_os=VmConfig::get('rr_os',array('C'));
							if(!is_array($rr_os)) $rr_os = array($rr_os);

							$db = JFactory::getDBO ();
							$q = 'SELECT COUNT(*) as total FROM `#__virtuemart_orders` AS o LEFT JOIN `#__virtuemart_order_items` AS oi ';
							$q .= 'ON `o`.`virtuemart_order_id` = `oi`.`virtuemart_order_id` ';
							$q .= 'WHERE o.virtuemart_user_id = "' . $user->id . '" AND oi.virtuemart_product_id = "' . $product_id . '" ';
							$q .= 'AND o.order_status IN (\'' . implode("','",$rr_os). '\') ';

							$db->setQuery ($q);
							$count = $db->loadResult ();
						}

						if ($count) {
							$this->_productBought[$product_id] = true;
							return true;
						}
						else {
							$this->_productBought[$product_id] = false;
							return false;
						}
					}
				}
			}
		}
	}
}
// pure php no closing tag

<?php
/**
 *
 * loads the js for ask a question and recommened to a friend.
 *
 * @package    VirtueMart
 * @subpackage
 * @author Max Milbers, Valerie Isaksen
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2014 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: default_showprices.php 8024 2014-06-12 15:08:59Z Milbo $
 */
// Check to ensure this file is included in Joomla!
defined ('_JEXEC') or die('Restricted access');

static $ask_recommened_loaded = false;
if($ask_recommened_loaded) return '';

$product = $viewData['product'];

if(VmConfig::get('usefancy',1)){
	vmJsApi::addJScript( 'fancybox/jquery.fancybox-1.3.4.pack',false, false);
	vmJsApi::css('jquery.fancybox-1.3.4');
	$Modal ="
		$('a.ask-a-question, a.printModal, a.recommened-to-friend, a.manuModal').click(function(event){
		  event.preventDefault();
		  $.fancybox({
			href: $(this).attr('href'),
			type: 'iframe',
			height: 550
			});
		  });
		";
} else {

	vmJsApi::addJScript( 'facebox', false, false );
	vmJsApi::css( 'facebox' );
    $Modal ="
    		$('a.ask-a-question, a.printModal, a.recommened-to-friend, a.manuModal').click(function(event){
		      event.preventDefault();
		      $.facebox({
		        ajax: $(this).attr('href'),
		        rev: 'iframe|550|550'
		        });
		      });
    		";
}

vmJsApi::addJScript('popups',"
	jQuery(document).ready(function($) {
		".$Modal."
	});
");
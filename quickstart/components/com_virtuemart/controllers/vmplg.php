<?php

/**
 *
 * Controller for the Plugins Response
 *
 * @package	VirtueMart
 * @subpackage pluginResponse
 * @author Valérie Isaksen
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2014 VirtueMart Team and authors. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: cart.php 3388 2011-05-27 13:50:18Z alatak $
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the controller framework
jimport('joomla.application.component.controller');

/**
 * Controller for the plugin response view
 *
 * @package VirtueMart
 * @subpackage pluginresponse
 * @author Valérie Isaksen
 *
 */
class VirtueMartControllerVmplg extends JControllerLegacy {

    /**
     * Construct the cart
     *
     * @access public
     */
    public function __construct() {
		parent::__construct();
    }

    /**
     * ResponseReceived()
     * From the plugin page, the user returns to the shop. The order email is sent, and the cart emptied.
     *
     * @author Valerie Isaksen
     *
     */
    function pluginResponseReceived() {

	$this->PaymentResponseReceived();
	$this->ShipmentResponseReceived();
    }

    /**
     * ResponseReceived()
     * From the payment page, the user returns to the shop. The order email is sent, and the cart emptied.
     *
     */
    function PaymentResponseReceived() {

	if (!class_exists('vmPSPlugin'))
	    require(VMPATH_PLUGINLIBS . DS . 'vmpsplugin.php'); JPluginHelper::importPlugin('vmpayment');

	$return_context = "";
	$dispatcher = JDispatcher::getInstance();
	$html = "";
	$paymentResponse = vmText::_('COM_VIRTUEMART_CART_THANKYOU');
	$returnValues = $dispatcher->trigger('plgVmOnPaymentResponseReceived', array( 'html' => &$html,&$paymentResponse));

	$view = $this->getView('vmplg', 'html');
	$layoutName = vRequest::getVar('layout', 'default');
	$view->setLayout($layoutName);

	$view->assignRef('paymentResponse', $paymentResponse);
	$view->assignRef('paymentResponseHtml', $html);

	// Display it all
	$view->display();
    }

	/**
	 *
	 */
    function ShipmentResponseReceived() {
		// TODO: not ready yet

	    if (!class_exists('vmPSPlugin'))
		    require(VMPATH_PLUGINLIBS . DS . 'vmpsplugin.php');
	    JPluginHelper::importPlugin('vmshipment');

	    $return_context = "";
	    $dispatcher = JDispatcher::getInstance();

	    $html = "";
	    $shipmentResponse = vmText::_('COM_VIRTUEMART_CART_THANKYOU');
	    $dispatcher->trigger('plgVmOnShipmentResponseReceived', array( 'html' => &$html,&$shipmentResponse));

    }

    /**
     * PaymentUserCancel()
     * From the payment page, the user has cancelled the order. The order previousy created is deleted.
     * The cart is not emptied, so the user can reorder if necessary.
     * then delete the order
     *
     */
    function pluginUserPaymentCancel() {

	if (!class_exists('vmPSPlugin'))
	    require(VMPATH_PLUGINLIBS . DS . 'vmpsplugin.php');

	if (!class_exists('VirtueMartCart'))
	    require(VMPATH_SITE . DS . 'helpers' . DS . 'cart.php');

    $cart = VirtueMartCart::getCart ();
		$cart->prepareCartData();
    if (!empty($cart->couponCode)) {
	    if (!class_exists('CouponHelper'))
		    require(VMPATH_SITE . DS . 'helpers' . DS . 'coupon.php');
	    CouponHelper::setInUseCoupon($cart->couponCode, false);
    }

	JPluginHelper::importPlugin('vmpayment');
	$dispatcher = JDispatcher::getInstance();
	$dispatcher->trigger('plgVmOnUserPaymentCancel', array());

	// return to cart view
	$view = $this->getView('cart', 'html');
	$layoutName = vRequest::getCmd('layout', 'default');
	$view->setLayout($layoutName);

	// Display it all
	$view->display();
    }

    /**
     * Attention this is the function which processs the response of the payment plugin
     *
     * @return success of update
     */
    function pluginNotification() {

	if (!class_exists('vmPSPlugin'))
	    require(VMPATH_PLUGINLIBS . DS . 'vmpsplugin.php');

	if (!class_exists('VirtueMartCart'))
	    require(VMPATH_SITE . DS . 'helpers' . DS . 'cart.php');

	if (!class_exists('VirtueMartModelOrders'))
	    require( VMPATH_ADMIN . DS . 'models' . DS . 'orders.php' );

	JPluginHelper::importPlugin('vmpayment');

	$dispatcher = JDispatcher::getInstance();
	$returnValues = $dispatcher->trigger('plgVmOnPaymentNotification', array());

    }


	/**
	 * Alias for task=pluginNotification
	 *
	 * @return success of update
	 */
	function notify () {

		$this->pluginNotification();

	}
}

//pure php no Tag

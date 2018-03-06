<?php
/**
 *
 * Realex payment plugin
 *
 * @author Valerie Isaksen
 * @version $Id: response.php 8414 2014-10-12 20:30:38Z alatak $
 * @package VirtueMart
 * @subpackage payment
 * Copyright (C) 2004 - 2017 Virtuemart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */
defined('_JEXEC') or die();
vmJsApi::css( 'realex','plugins/vmpayment/realex_hpp_api/realex_hpp_api/assets/css/');

?>

<div class="realex response">

	<?php if ($viewData['success']) { ?>
		<div class="realex_auth_info">
			<span class="realex_auth_value"><?php echo $viewData['auth_info']; ?></span>
		</div>
		<div class="realex_dcc_info">
			<span class="realex_dcc_value"><?php echo $viewData['dcc_info']; ?></span>
		</div>
		<div class="realex_payer_info">
			<span class="realex_payer_value"><?php echo $viewData['payer_info']; ?></span>
		</div>
		<div class="realex_pasref">
			<span class="realex_pasref_label"><?php echo vmText::_('VMPAYMENT_REALEX_HPP_API_RESPONSE_PASREF'); ?></span>
			<span class="realex_pasref_value"><?php echo $viewData['pasref']; ?></span>
		</div>
		<div class="realex_vieworder">
			<a class="vm-button-correct"
			   href="<?php echo JRoute::_('index.php?option=com_virtuemart&view=orders&layout=details&order_number=' . $viewData["order_number"] . '&order_pass=' . $viewData["order_pass"], false) ?>"><?php echo vmText::_('COM_VIRTUEMART_ORDER_VIEW_ORDER'); ?></a>
		</div>
	<?php } else { ?>
		<div class="realex_auth_info">
			<span class="realex_auth_value"><?php echo $viewData['auth_info']; ?></span>
		</div>
	<?php } ?>
</div>

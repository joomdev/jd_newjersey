<?php
/**
 *
 * Klikandpay payment plugin
 *
 * @author Valerie Isaksen
 * @version $Id$
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
$doc = JFactory::getDocument();
$doc->addStyleSheet(JURI::root(true) . '/plugins/vmpayment/klikandpay/klikandpay/assets/css/klikandpay.css');
?>

<div class="klikandpay response">


	<?php if ($viewData['success']) { ?>
		<div class="status_confirmed">
			<?php echo vmText::sprintf('VMPAYMENT_KLIKANDPAY_PAYMENT_STATUS_CONFIRMED', $viewData['amountInCurrency'], $viewData["order_number"]); ?>
		</div>
		<div class="transaction_id">
			<?php echo vmText::_('VMPAYMENT_KLIKANDPAY_RESPONSE_NUMXKP') . ' ' . $viewData['$numxkp'];
			?>
		</div>
		<?php if (!empty($viewData['prochaine'])) { ?>
			<div class="extra_comment">
				<?php echo vmText::_('VMPAYMENT_KLIKANDPAY_RESPONSE_PROCHAINE') . ' ' . $viewData['prochaine'];
				?>
			</div>
		<?php
		}
		?>
		<?php if (!empty($viewData['extra_comment'])) { ?>
			<div class="extra_comment">
				<?php echo $viewData['extra_comment'];
				?>
			</div>
		<?php
		}
		?>
		<div class="vieworder">
			<a class="vm-button-correct"
			   href="<?php echo JRoute::_('index.php?option=com_virtuemart&view=orders&layout=details&order_number=' . $viewData["order_number"] . '&order_pass=' . $viewData["order_pass"], false) ?>"><?php echo vmText::_('COM_VIRTUEMART_ORDER_VIEW_ORDER'); ?></a>
		</div>
	<?php } else { ?>
		<div class="">
			<span class=""><?php echo $viewData['not_success']; ?></span>
		</div>
	<?php } ?>
</div>
<?php
/**
 *
 * Description
 *
 * @package    VirtueMart
 * @subpackage
 * @author VirtueMart Team, Max Milbers
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2016 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id$
 */
// Check to ensure this file is included in Joomla!
defined ('_JEXEC') or die('Restricted access');
AdminUIHelper::startAdminArea ($this);

$styleDateCol = 'style="width:5%;min-width:110px"';
?>

<form action="index.php?option=com_virtuemart&view=orders" method="post" name="adminForm" id="adminForm">
	<div id="header">
		<div id="filterbox">
			<table>
				<tr>
					<td align="left" style="min-width:420px;width:17%;">
						<?php echo $this->displayDefaultViewSearch ('COM_VIRTUEMART_ORDER_PRINT_NAME'); ?>
                        <div id="resultscounter"><?php echo $this->pagination->getResultsCounter (); ?></div>
					</td>
					<td align="left" style="min-width:190px;width:21%;">
						<?php echo vmText::_ ('COM_VIRTUEMART_ORDERSTATUS') . ':' . $this->lists['state_list']; ?>
                    </td>
					<td align="right" style="min-width:190px;width:25%;max-width:300px;border-style:solid none solid solid;border-width:1px;">
						<span style="text-align:left"><?php echo vmText::_ ('COM_VIRTUEMART_BULK_ORDERSTATUS') . $this->lists['bulk_state_list']; ?></span>
					</td>
					<td align="left" style="min-width:330px;width:22%;border-style:solid solid solid none;border-width:1px;">
						<?php echo VmHTML::checkbox ('customer_notified', 0) . vmText::_ ('COM_VIRTUEMART_ORDER_LIST_NOTIFY'); ?> <br>
						<?php echo VmHTML::checkbox ('customer_send_comment', 1) . vmText::_ ('COM_VIRTUEMART_ORDER_HISTORY_INCLUDE_COMMENT'); ?>
						<?php echo VmHTML::checkbox ('update_lines', 1) . vmText::_ ('COM_VIRTUEMART_ORDER_UPDATE_LINESTATUS'); ?>
						<textarea class="element-hidden vm-order_comment vm-showable" name="comments" cols="5" rows="5"></textarea>
						<?php echo JHtml::_ ('link', '#', vmText::_ ('COM_VIRTUEMART_ADD_COMMENT'), array('class' => 'show_comment')); ?>
					</td>
					<td align="right" style="min-width:220px;width:14%;">
						<?php echo $this->lists['vendors'] ?>
					</td>
				</tr>
			</table>
		</div>

	</div>
<div style="text-align: left;">
	<table class="adminlist table table-striped" cellspacing="0" cellpadding="0">
		<thead>
		<tr>
			<th class="admin-checkbox"><input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)"/></th>
			<th width="8%"><?php echo $this->sort ('order_number', 'COM_VIRTUEMART_ORDER_LIST_NUMBER')  ?> / <?php echo vmText::_('COM_VIRTUEMART_INVOICE') ?></th>
			<th width="26%"><?php echo $this->sort ('order_name', 'COM_VIRTUEMART_ORDER_PRINT_NAME').' / '; echo $this->sort ('order_email', 'COM_VIRTUEMART_EMAIL')  ?></th>
			<th width="18%"><?php echo $this->sort ('payment_method', 'COM_VIRTUEMART_ORDER_PRINT_PAYMENT_LBL')  ?></th>
			<th width="18%"><?php echo $this->sort('shipment_method', 'COM_VIRTUEMART_ORDER_PRINT_SHIPMENT_LBL') ?></th>
			<th style="min-width:100px;width:5%;"><?php echo vmText::_ ('COM_VIRTUEMART_PRINT_VIEW'); ?></th>
			<th class="admin-dates"><?php echo $this->sort ('created_on', 'COM_VIRTUEMART_ORDER_CDATE')  ?></th>
			<th class="admin-dates"><?php echo $this->sort ('modified_on', 'COM_VIRTUEMART_ORDER_LIST_MDATE')  ?></th>
			<th><?php echo $this->sort ('order_status', 'COM_VIRTUEMART_STATUS')  ?></th>
			<th style="min-width:130px;width:5%;"><?php echo vmText::_ ('COM_VIRTUEMART_ORDER_LIST_NOTIFY'); ?></th>
			<th width="10%"><?php echo $this->sort ('order_total', 'COM_VIRTUEMART_TOTAL')  ?></th>
			<th><?php echo $this->sort ('virtuemart_order_id', 'COM_VIRTUEMART_ORDER_LIST_ID')  ?></th>

		</tr>
		</thead>
		<tbody>
		<?php
		if (count ($this->orderslist) > 0) {
			$i = 0;
			$k = 0;
			$keyword = vRequest::getCmd ('keyword');

			foreach ($this->orderslist as $key => $order) {
				$checked = JHtml::_ ('grid.id', $i, $order->virtuemart_order_id);
				//vmdebug('My order',$order);
				?>
			<tr class="row<?php echo $k . ' status-'. strtolower($order->order_status); ?>">
				<!-- Checkbox -->
				<td class="admin-checkbox"><?php echo $checked; ?></td>
				<!-- Order id -->
				<?php
				$link = 'index.php?option=com_virtuemart&view=orders&task=edit&virtuemart_order_id=' . $order->virtuemart_order_id;
				?>
				<td><?php echo JHtml::_ ('link', JRoute::_ ($link, FALSE), $order->order_number, array('title' => vmText::_ ('COM_VIRTUEMART_ORDER_EDIT_ORDER_NUMBER') . ' ' . $order->order_number));
				echo '<br>';
				echo implode('<br>',$order->invoiceNumbers); ?>
				</td>
				<td>
					<?php
					if ($order->virtuemart_user_id) {
						$userlink = JROUTE::_ ('index.php?option=com_virtuemart&view=user&task=edit&virtuemart_user_id[]=' . $order->virtuemart_user_id, FALSE);
						echo JHtml::_ ('link', JRoute::_ ($userlink, FALSE), $order->order_name, array('title' => vmText::_ ('COM_VIRTUEMART_ORDER_EDIT_USER') . ' ' .  $order->order_name));
					} else {
						echo $order->order_name;
					}
					echo '<br>';
					echo $order->order_email;
					?>
				</td>

				<!-- Payment method -->
				<td><?php echo $order->payment_method; ?></td>
				<!-- Shipment method -->
				<td><?php echo $order->shipment_method; ?></td>
				<!-- Print view -->
				<?php
					$this->createPrintLinks($order,$print_link,$deliverynote_link,$invoice_link);
				?>
				<td><?php echo $print_link; echo $deliverynote_link; echo $invoice_link; ?></td>
				<!-- Order date -->
				<td><?php echo vmJsApi::date ($order->created_on, 'LC2', TRUE); ?></td>
				<!-- Last modified -->
				<td><?php echo vmJsApi::date ($order->modified_on, 'LC2', TRUE); ?></td>
				<!-- Status -->
				<?php
				$colorStyle = '';
				if (!empty($this->orderStatesColors[$order->order_status])) {
					$colorStyle = "background-color:" . $this->orderStatesColors[$order->order_status];
				}
				?>
				<td style="position:relative;<?php echo $colorStyle ?>">
					<?php echo JHtml::_ ('select.genericlist', $this->orderstatuses, "orders[" . $order->virtuemart_order_id . "][order_status]", 'class="orderstatus_select" style="width:180px;"', 'order_status_code', 'order_status_name', $order->order_status, 'order_status' . $i, TRUE); ?>
					<input type="hidden" name="orders[<?php echo $order->virtuemart_order_id; ?>][current_order_status]" value="<?php echo $order->order_status; ?>"/>
					<input type="hidden" name="orders[<?php echo $order->virtuemart_order_id; ?>][coupon_code]" value="<?php echo $order->coupon_code; ?>"/>					<br/>
					<textarea class="element-hidden vm-order_comment vm-showable" name="orders[<?php echo $order->virtuemart_order_id; ?>][comments]" cols="5" rows="5"></textarea>
					<?php echo JHtml::_ ('link', '#', vmText::_ ('COM_VIRTUEMART_ADD_COMMENT'), array('class' => 'show_comment')); ?>
				</td>
				<!-- Update -->
				<td><?php echo VmHTML::checkbox ('orders[' . $order->virtuemart_order_id . '][customer_notified]', 0) . vmText::_ ('COM_VIRTUEMART_ORDER_LIST_NOTIFY'); ?>
					<br/>
					<?php echo VmHTML::checkbox ('orders[' . $order->virtuemart_order_id . '][customer_send_comment]', 1) . vmText::_ ('COM_VIRTUEMART_ORDER_HISTORY_INCLUDE_COMMENT'); ?>
					<br/>
					<?php echo VmHTML::checkbox ('orders[' . $order->virtuemart_order_id . '][update_lines]', 1) . vmText::_ ('COM_VIRTUEMART_ORDER_UPDATE_LINESTATUS'); ?>
				</td>
				<!-- Total -->
				<td><?php echo $order->order_total; ?></td>
				<td><?php echo JHtml::_ ('link', JRoute::_ ($link, FALSE), $order->virtuemart_order_id, array('title' => vmText::_ ('COM_VIRTUEMART_ORDER_EDIT_ORDER_ID') . ' ' . $order->virtuemart_order_id)); ?></td>

			</tr>
				<?php
				$k = 1 - $k;
				$i++;
			}
		}
		?>
		</tbody>
		<tfoot>
		<tr>
			<td colspan="12">
				<?php echo $this->pagination->getListFooter (); ?>
			</td>
		</tr>
		</tfoot>
	</table>
</div>
	<!-- Hidden Fields -->
	<?php echo $this->addStandardHiddenToForm (); ?>
</form>
<?php AdminUIHelper::endAdminArea ();

$orderstatusForShopperEmail = VmConfig::get('email_os_s',array('U','C','S','R','X'));
if(!is_array($orderstatusForShopperEmail)) $orderstatusForShopperEmail = array($orderstatusForShopperEmail);
$jsOrderStatusShopperEmail = vmJsApi::safe_json_encode($orderstatusForShopperEmail);

$j = 'if (typeof Virtuemart === "undefined")
	var Virtuemart = {};
	Virtuemart.orderstatus = '.$jsOrderStatusShopperEmail.';
	jQuery(document).ready(function() {
		//Virtuemart.onReadyOrderItems();
		Virtuemart.onReadyOrderStatus()
	});';
vmJsApi::addJScript('onReadyOrders',$j);

vmJsApi::addJScript('/administrator/components/com_virtuemart/assets/js/orders.js',false,false);
?>
<?php
/**
 *
 * Handle the waitinglist, and the send an email to shoppers who bought this product
 *
 * @package    VirtueMart
 * @subpackage Product
 * @author Seyi, Valérie Isaksen
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2012 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: product_edit_customer.php 9722 2018-01-09 11:36:14Z Milbo $
 */
// Check to ensure this file is included in Joomla!
defined ('_JEXEC') or die('Restricted access');

$stockhandle = $this->product->product_stockhandle ? $this->product->product_stockhandle : VmConfig::get ('stockhandle', 0);

$i = 0;
?>
<table class="adminform">
	<tbody>
	<tr class="row<?php echo $i?>">
		<td width="21%" valign="top">
			<?php
			$mail_options = array(
				'customer'=> vmText::_ ('COM_VIRTUEMART_PRODUCT_SHOPPERS')
			);
			if ($stockhandle != 'disableadd' or empty($this->waitinglist)) {
				echo VmHtml::radioList ('customer_email_type', 'customer', $mail_options, 'style="display:none;"');
			}
			else {
				$mail_default = 'notify';
				$mail_options['notify'] = vmText::_ ('COM_VIRTUEMART_PRODUCT_WAITING_LIST_USERLIST');
				echo VmHtml::radioList ('customer_email_type', $mail_default, $mail_options);
			}
			?>

			<div id="notify_particulars" style="padding-left:20px;">
				<div><input type="checkbox" name="notification_template" id="notification_template" value="1" CHECKED>
					<label for="notification_template">
						<span class="hasTip" title="<?php echo vmText::_ ('COM_VIRTUEMART_PRODUCT_USE_NOTIFY_TEMPLATE_TIP'); ?>">
						<?php echo vmText::_ ('COM_VIRTUEMART_PRODUCT_USE_NOTIFY_TEMPLATE'); ?></span>
					</label>
				</div>
				<div><input type="text" name="notify_number" value="" size="4"/><?php echo vmText::_ ('COM_VIRTUEMART_PRODUCT_NOTIFY_NUMBER'); ?></div>
			</div>
			<br/>

			<div class="mailing">
				<div class="button2-left btn-wrapper btn btn-small" data-type="sendmail">
					<div class="blank" style="padding:0 6px;cursor: pointer;" title="<?php echo vmText::_ ('COM_VIRTUEMART_PRODUCT_EMAIL_SEND_TIP'); ?>">
						<span class="vmicon vmicon-16-email"></span>
						<?php echo vmText::_ ('COM_VIRTUEMART_PRODUCT_EMAIL_SEND'); ?>
					</div>
				</div>
				<div id="customers-list-msg"></div>
				<br/>
			</div>

		</td>
	</tr>
	<?php $i = 1 - $i; ?>
	<tr class="row<?php echo $i?>">
		<td width="21%" valign="top">
			<div id="customer-mail-content">
				<div><?php echo vmText::_ ('COM_VIRTUEMART_PRODUCT_EMAIL_SUBJECT') ?></div>
				<input type="text" class="mail-subject" id="mail-subject" size="100"   value="<?php echo vmText::sprintf ('COM_VIRTUEMART_PRODUCT_EMAIL_SHOPPERS_SUBJECT',htmlentities($this->product->product_name)) ?>">

				<div><?php echo vmText::_ ('COM_VIRTUEMART_PRODUCT_EMAIL_CONTENT') ?></div>
				<textarea style="width: 100%;" class="inputbox"   id="mail-body" cols="35" rows="10"></textarea>
				<br/>
			</div>
		</td>
	</tr>
	<?php $i = 1 - $i; ?>
	<tr class="row<?php echo $i?>">
		<td width="21%" valign="top">
			<div id="customer-mail-list">
				<span class="hasTip" title="<?php echo vmText::_ ('COM_VIRTUEMART_PRODUCT_EMAIL_ORDER_ITEM_STATUS_TIP'); ?>">
				<strong><?php echo vmText::_ ('COM_VIRTUEMART_PRODUCT_EMAIL_ORDER_ITEM_STATUS') ?></strong>
				</span><br/>
				<?php echo $this->lists['OrderStatus'];?>
				<br/> <br/>
				<div style="font-weight:bold;"><?php echo vmText::sprintf ('COM_VIRTUEMART_PRODUCT_SHOPPERS_LIST', vRequest::vmSpecialChars($this->product->product_name)); ?></div>
				<table class="adminlist table ui-sortable" cellspacing="0" cellpadding="0">
					<thead>
					<tr>
						<th class="title"><?php echo $this->sort ('ou.first_name', 'COM_VIRTUEMART_NAME','productShoppers');?></th>
						<th class="title"><?php echo $this->sort ('ou.email', 'COM_VIRTUEMART_EMAIL','productShoppers');?></th>
						<th class="title"><?php echo vmText::_ ('COM_VIRTUEMART_SHOPPER_FORM_PHONE');?></th>
						<th class="title"><?php echo vmText::_ ('COM_VIRTUEMART_ORDER_PRINT_QUANTITY');?></th>
						<th class="title"><?php echo vmText::_ ('COM_VIRTUEMART_ORDER_PRINT_ITEM_STATUS');?></th>
						<th class="title"><?php echo $this->sort ('o.order_number', 'COM_VIRTUEMART_ORDER_NUMBER', 'productShoppers');?></th>
						<th class="title"><?php echo $this->sort ('order_date', 'COM_VIRTUEMART_ORDER_CDATE','productShoppers');?></th>
					</tr>
					</thead>
					<tbody id="customers-list">
					<?php
					if(!class_exists('ShopFunctions'))require(VMPATH_ADMIN.DS.'helpers'.DS.'shopfunctions.php');
					echo ShopFunctions::renderProductShopperList($this->productShoppers);
					?>
					</tbody>
				</table>
			</div>

			<div id="customer-mail-notify-list">

				<?php if ($stockhandle == 'disableadd' && !empty($this->waitinglist)) { ?>
				<div style="font-weight:bold;"><?php echo vmText::_ ('COM_VIRTUEMART_PRODUCT_WAITING_LIST_USERLIST'); ?></div>
				<table class="adminlist table" cellspacing="0" cellpadding="0">
					<thead>
					<tr>
						<th class="title"><?php echo vmText::_ ('COM_VIRTUEMART_NAME');?></th>
						<th class="title"><?php echo vmText::_ ('COM_VIRTUEMART_USERNAME');?></th>
						<th class="title"><?php echo vmText::_ ('COM_VIRTUEMART_EMAIL');?></th>
                        <th class="title"><?php echo vmText::_ ('COM_VIRTUEMART_CREATED_ON');?></th>
					</tr>
					</thead>
					<tbody id="customers-notify-list">
						<?php
						if (isset($this->waitinglist) && count ($this->waitinglist) > 0) {
							$i=0;
							foreach ($this->waitinglist as $key => $wait) {
								if ($wait->virtuemart_user_id == 0) {
									$row = '<tr class="row'.$i.'"><td></td><td></td><td><a href="mailto:' . $wait->notify_email . '">' .
									$wait->notify_email . '</a></td><td>' .  vmJsApi::date($wait->created_on, 'LC2', TRUE) . '</td></tr>';
								}
								else {
									$row = '<tr class="row'.$i.'"><td>' . $wait->name . '</td><td>' . $wait->username . '</td><td>' . '<a href="mailto:' . $wait->notify_email . '">' . $wait->notify_email . '</a>' . '</td> <td>' . vmJsApi::date($wait->created_on, 'LC2', TRUE) . '</td></tr>';
								}
								echo $row;
								$i = 1 - $i;
							}
						}
						else {
							?>
						<tr>
							<td colspan="4">
								<?php echo vmText::_ ('COM_VIRTUEMART_PRODUCT_WAITING_NOWAITINGUSERS'); ?>
							</td>
						</tr>
							<?php
						} ?>
					</tbody>
				</table>

				<?php } ?>
			</div>

			</div>
		</td>
	</tr>
	<tr>
		<td>
			<?php
			$aflink = '<a target="_blank" href="https://www.acyba.com/?partner_id=19513"><img title="AcyMailing2" src="https://www.acyba.com/images/banners/affiliate2.png"/></a>';
			echo vmText::sprintf('COM_VIRTUEMART_AD_ACY',$aflink);
			?>
		</td>
	</tr>
	</tbody>
</table>

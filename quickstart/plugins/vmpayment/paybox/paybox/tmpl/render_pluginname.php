<?php
defined ('_JEXEC') or die();

/**
 * @author Valérie Isaksen
 * @version $Id: render_pluginname.php 7198 2013-09-13 13:09:01Z alatak $
 * @package VirtueMart
 * @subpackage vmpayment
 * @copyright Copyright (C) 2004-Copyright (C) 2004 - 2017 Virtuemart Team. All rights reserved.   - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */

?>
<span class="paybox vmpayment">
	<?php
	if (!empty($viewData['logo'])) {
		?>
		<span class="vmCartPaymentLogo" >
			<?php echo $viewData['logo'] ?>
        </span>
	<?php
	}
	?>
	<span class="vmpayment_name"><?php echo $viewData['payment_name'] ?> </span>
	<?php
	if ($viewData['shop_mode'] == 'test') {
		?>
		<span style="color:red;font-weight:bold">Sandbox (<?php echo $viewData['virtuemart_paymentmethod_id'] ?>)</span>
	<?php
	}
	?>
	<?php
	if (!empty($viewData['payment_description'])) {
		?>
		<span class="vmpayment_description"><?php echo $viewData['payment_description'] ?> </span>
	<?php
	}
	?>
	<?php
	if (isset($viewData['extraInfo']['recurring'])) {
		?>
		<div class="vmpayment_recurring">
			<?php echo vmText::sprintf('VMPAYMENT_'.$this->_name.'_COMMENT_RECURRING_INFO', $viewData['extraInfo']['recurring_number'], $viewData['extraInfo']['recurring_periodicity']) ?>
		</div>
	<?php
	}
	?>
	<?php
	if (isset($viewData['extraInfo']['subscribe'])) {
		?>
		<div class="vmpayment_subscribe">
			<?php
			echo vmText::_('VMPAYMENT_'.$this->_name.'_SUBSCRIBE_1MONT')." " .$viewData['extraInfo']['subscribe_1mont'] ."<br />" ;
			echo vmText::_('VMPAYMENT_'.$this->_name.'_SUBSCRIBE_2MONT')." " .$viewData['extraInfo']['subscribe_2mont'] ."<br />" ;
			echo vmText::_('VMPAYMENT_'.$this->_name.'_SUBSCRIBE_NBPAIE')." " .$viewData['extraInfo']['subscribe_nbpaie']  ."<br />" ;
			if ($viewData['extraInfo']['subscribe_quand']==1) {
				$viewData['extraInfo']['subscribe_quand'] ="";
			}
				echo vmText::sprintf('VMPAYMENT_'.$this->_name.'_SUBSCRIBE_QUAND', $viewData['extraInfo']['subscribe_quand']  ) ."<br />" ;
			echo vmText::sprintf('VMPAYMENT_'.$this->_name.'_SUBSCRIBE_FREQ',$viewData['extraInfo']['subscribe_freq'])   ."<br />" ;
			if ($viewData['extraInfo']['subscribe_delais']) {
			echo vmText::_('VMPAYMENT_'.$this->_name.'_SUBSCRIBE_DELAIS')." " .$viewData['extraInfo']['subscribe_delais']  ."<br />" ;
			}
			?>
		</div>
	<?php
	}
	?>
</span>




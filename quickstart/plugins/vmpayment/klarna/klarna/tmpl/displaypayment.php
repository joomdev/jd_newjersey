<?php  defined ('_JEXEC') or die();
/**
 * @version $Id: displaypayment.php 6630 2012-11-07 09:26:56Z alatak $
 *
 * @author Valérie Isaksen
 * @package VirtueMart
 * @copyright Copyright (C) 2012 iStraxx - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
?>
<fieldset>

	<table cellspacing="0" cellpadding="2" border="0" width="100%">
		<tbody>
		<tr>
			<td >
				<input class="klarnaPayment" data-stype="<?php echo $viewData['stype'] ?>" id="<?php echo $viewData['id'] ?>" type="radio"
				       name="virtuemart_paymentmethod_id" value="<?php echo  $viewData['virtuemart_paymentmethod_id'] ?>" <?php echo  $viewData['selected'] ?> />
				<input value="<?php echo $viewData['id'] ?>" type="hidden" name="klarna_paymentmethod"/>
				<label for="<?php echo $viewData['id']?>">
					<?php echo $viewData['module'] ?>
				</label>
				<br/>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo  $viewData['klarna_form']  ?>
			<td>
		</tr>
		</tbody>
	</table>
</fieldset>
<?php
// preventing 2 x load javascript
static $loadjavascript;
if ($loadjavascript) {
	return TRUE;
}
$loadjavascript = TRUE;
$html_js = '<script type="text/javascript">
            setTimeout(\'jQuery(":radio[value=' . $viewData['klarna_paymentmethod'] . ']").click();\', 200);
        </script>';
?>


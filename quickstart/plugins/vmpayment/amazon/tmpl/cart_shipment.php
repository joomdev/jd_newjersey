<?php
/**
 *
 * Template for the shipment selection for Amazon Cart layout
 *
 * @package    VirtueMart
 * @subpackage Cart
 * @author Max Milbers, Valérie Isaksen
 *
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id$
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
vmJsApi::jPrice();
$document = JFactory::getDocument();

$buttonclass = 'button vm-button-correct';
$buttonclass = 'default';
?>
<?php
if (isset($this->found_shipment_method) and $this->found_shipment_method) {
	echo "<h3>" . JText::_('COM_VIRTUEMART_CART_SELECT_SHIPMENT') . "</h3>";
	?>


	<fieldset>
		<?php
		// if only one Shipment , should be checked by default
		foreach ($this->shipments_shipment_rates as $shipment_shipment_rates) {
			if (is_array($shipment_shipment_rates)) {
				foreach ($shipment_shipment_rates as $shipment_shipment_rate) {
					echo "<div>" . $shipment_shipment_rate . "</div>\n";
				}
			}
		}
		?>
	</fieldset>
<?php
} else {

		vmInfo('VMPAYMENT_AMAZON_UPDATECART_SHIPMENT_NOT_FOUND');

}



?>


<?php
/**
*
* @version $Id$
* @package VirtueMart
* @subpackage Shipment
* @author Max Milbers
* @copyright Copyright (C) 2014 by the VirtueMart Team 
* All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.net
*/

//vmdebug('we have here ',$viewData['product']->prices,$viewData['method']);
$currency = $viewData['currency'];
if(!empty($viewData['method']->countries) and is_array($viewData['method']->countries) and count($viewData['method']->countries)>0){
	$countryM = VmModel::getModel('country');
	echo vmText::_('VMSHIPMENT_WEIGHT_COUNTRIES_SHIP_TO');
	$countryNames = array();
	foreach($viewData['method']->countries as $virtuemart_country_id){
		$c = $countryM->getData($virtuemart_country_id);
		$countryNames[] = $c->country_name;
		//vmdebug('my country ',$country);
	}
	if(!empty($countryNames)){
		echo implode(', ',$countryNames).'<br/>';
	}

}

echo vmtext::sprintf('VMSHIPMENT_WEIGHT_COUNTRIES_WITH_SHIPMENT', $viewData['method']->shipment_name, $currency->priceDisplay($viewData['product']->prices['shipmentPrice']));
?>
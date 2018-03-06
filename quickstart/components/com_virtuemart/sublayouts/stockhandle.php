<?php
$product = $viewData['product'];
// Availability
$stockhandle = VmConfig::get('stockhandle_products', false) && $product->product_stockhandle ? $product->product_stockhandle : VmConfig::get('stockhandle','none');
$product_available_date = substr($product->product_available_date,0,10);
$current_date = date("Y-m-d");
if (($product->product_in_stock - $product->product_ordered) < 1) {
	if ($product_available_date != '0000-00-00' and $current_date < $product_available_date) {
	?>	<div class="availability">
		<?php echo vmText::_('COM_VIRTUEMART_PRODUCT_AVAILABLE_DATE') .': '. JHtml::_('date', $product->product_available_date, vmText::_('DATE_FORMAT_LC4')); ?>
	</div>
	<?php
	} else if ($stockhandle == 'risetime' and VmConfig::get('rised_availability') and empty($product->product_availability)) {
		?>	<div class="availability">
			<?php echo (file_exists(JPATH_BASE . DS . VmConfig::get('assets_general_path') . 'images/availability/' . VmConfig::get('rised_availability'))) ? JHtml::image(JURI::root() . VmConfig::get('assets_general_path') . 'images/availability/' . VmConfig::get('rised_availability', '7d.gif'), VmConfig::get('rised_availability', '7d.gif'), array('class' => 'availability')) : vmText::_(VmConfig::get('rised_availability')); ?>
		</div>
	<?php
	} else if (!empty($product->product_availability)) {
		?>
		<div class="availability">
			<?php echo (file_exists(JPATH_BASE . DS . VmConfig::get('assets_general_path') . 'images/availability/' . $product->product_availability)) ? JHtml::image(JURI::root() . VmConfig::get('assets_general_path') . 'images/availability/' . $product->product_availability, $product->product_availability, array('class' => 'availability')) : vmText::_($product->product_availability); ?>
		</div>
	<?php
	}
}
else if ($product_available_date != '0000-00-00' and $current_date < $product_available_date) {
	?>	<div class="availability">
		<?php echo vmText::_('COM_VIRTUEMART_PRODUCT_AVAILABLE_DATE') .': '. JHtml::_('date', $product->product_available_date, vmText::_('DATE_FORMAT_LC4')); ?>
	</div>
<?php
}
?>
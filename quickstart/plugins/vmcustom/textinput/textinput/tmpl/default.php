<?php
	defined('_JEXEC') or die();
	$class='vmcustom-textinput';
$product = $viewData[0];
$params = $viewData[1];
$name = 'customProductData['.$product->virtuemart_product_id.']['.$params->virtuemart_custom_id.']['.$params->virtuemart_customfield_id .'][comment]';
?>

	<input class="<?php echo $class ?>"
		   type="text" value=""
		   size="<?php echo $params->custom_size ?>"
		   name="<?php echo $name?>"
		><br />
<?php

	//javascript to update price
	$j = 'var test = function($) {
	jQuery(".vmcustom-textinput").keyup(function() {
			formProduct = jQuery(this).parents("form.product");
			virtuemart_product_id = formProduct.find(\'input[name="virtuemart_product_id[]"]\').val();
		Virtuemart.setproducttype(formProduct,virtuemart_product_id);
		});
};
jQuery("body").on("updateVirtueMartProductDetail", test);
jQuery(document).ready(test);';
	vmJsApi::addJScript('textinput',$j);

?>
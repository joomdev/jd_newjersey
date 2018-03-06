<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
$product = $viewData['product'];
$currency = $viewData['currency'];
$view = vRequest::getCmd('view');
if($viewData['showRating']){
  $ratingModel = VmModel::getModel('Ratings');
  $productrating = $ratingModel->getRatingByProduct($product->virtuemart_product_id);
  $productratingcount = isset($productrating->ratingcount) ? $productrating->ratingcount:'';
}

?>

<script type="application/ld+json">
{
  "@context": "http://schema.org/",
  "@type": "Product",
  "name": "<?php echo htmlspecialchars($product->product_name); ?>",
  <?php if ( $product->images[0]->virtuemart_media_id > 0) { ?>
  "image": "<?php echo JURI::root().$product->images[0]->file_url; ?>",
  <?php } ?>
  <?php if (!empty($product->product_s_desc)) { ?>
  "description": "<?php echo htmlspecialchars(strip_tags($product->product_s_desc)); ?>",
  <?php } elseif (!empty($product->product_desc)) { ?>
  "description": "<?php echo htmlspecialchars(strip_tags($product->product_desc)); ?>",
  <?php } ?>
  <?php if ($viewData['showRating'] && !empty($product->rating)) { ?>
  "aggregateRating":{
    "@type": "AggregateRating",
    "ratingValue": "<?php echo $product->rating; ?>",
    "reviewCount": "<?php echo $productratingcount; ?>"
  },
  <?php } ?>
  "offers":{
    "@type": "Offer",
    "priceCurrency": "<?php echo $currency->_vendorCurrency_code_3; ?>",
    "price": "<?php echo $product->prices['salesPrice']; ?>"
  }
}
</script>
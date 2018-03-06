<?php
/**
 *
 * renders a customfield
 *
 * @package    VirtueMart
 * @subpackage
 * @author Max Milbers, Valerie Isaksen
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2015 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @version $Id: addtocartbtn.php 8024 2014-06-12 15:08:59Z Milbo $
 */
// Check to ensure this file is included in Joomla!
defined ('_JEXEC') or die('Restricted access');

class VirtueMartCustomFieldRenderer {



	static function renderCustomfieldsFE(&$product,&$customfields,$virtuemart_category_id){

		static $calculator = false;
		if(!$calculator){
			if (!class_exists ('calculationHelper')) {
				require(VMPATH_ADMIN . DS . 'helpers' . DS . 'calculationh.php');
			}
			$calculator = calculationHelper::getInstance ();
		}

		$selectList = array();

		$dynChilds = 1;

		static $currency = false;
		if(!$currency){
			if (!class_exists ('CurrencyDisplay'))
				require(VMPATH_ADMIN . DS . 'helpers' . DS . 'currencydisplay.php');
			$currency = CurrencyDisplay::getInstance ();
		}

		foreach($customfields as $k => $customfield){


			if(!isset($customfield->display))$customfield->display = '';

			$calculator->_product = $product;

			if (!class_exists ('vmCustomPlugin')) {
				require(VMPATH_PLUGINLIBS . DS . 'vmcustomplugin.php');
			}

			if ($customfield->field_type == "E") {

				JPluginHelper::importPlugin ('vmcustom');
				$dispatcher = JDispatcher::getInstance ();
				$ret = $dispatcher->trigger ('plgVmOnDisplayProductFEVM3', array(&$product, &$customfields[$k]));
				continue;
			}

			$fieldname = 'field['.$product->virtuemart_product_id.'][' . $customfield->virtuemart_customfield_id . '][customfield_value]';
			$customProductDataName = 'customProductData['.$product->virtuemart_product_id.']['.$customfield->virtuemart_custom_id.']';

			//This is a kind of fallback, setting default of custom if there is no value of the productcustom
			$customfield->customfield_value = empty($customfield->customfield_value) ? $customfield->custom_value : $customfield->customfield_value;

			$type = $customfield->field_type;

			$idTag = 'customProductData_'.(int)$product->virtuemart_product_id.'_'.$customfield->virtuemart_customfield_id;
			$idTag = VmHtml::ensureUniqueId($idTag);

			$emptyOption = new stdClass();
			$emptyOption->text = vmText::_ ('COM_VIRTUEMART_ADDTOCART_CHOOSE_VARIANT');
			$emptyOption->value = 0;
			switch ($type) {

				case 'C':
					$html = '';

					$dropdowns = array();

					if(isset($customfield->options->{$product->virtuemart_product_id})){
						$productSelection = $customfield->options->{$product->virtuemart_product_id};
					} else {
						$productSelection = false;
					}
					$stockhandle = VmConfig::get('stockhandle_products', false) && $product->product_stockhandle ? $product->product_stockhandle : VmConfig::get('stockhandle','none');

					$q = 'SELECT `virtuemart_product_id` FROM #__virtuemart_products WHERE product_parent_id = "'.$customfield->virtuemart_product_id.'" and ( published = "1" ';
					if($stockhandle == 'disableit_children'){
						$q .= ' AND (`product_in_stock` - `product_ordered`) > "0" ';
					}
					$q .= ');';
					$db = JFactory::getDbo();
					$db->setQuery($q);
					$avail = $db->loadColumn();
					if(!in_array($customfield->virtuemart_product_id,$avail)){
						array_unshift($avail,$customfield->virtuemart_product_id);
					}

					foreach($customfield->options as $product_id=>$variants){
						static $counter = 0;
						if(!in_array($product_id,$avail)){
							vmdebug('$customfield->options Product to ignore, continue ',$product_id);
							continue;
						}

						foreach($variants as $k => $variant){

							if(!isset($dropdowns[$k]) or !is_array($dropdowns[$k])) $dropdowns[$k] = array();
							if(!in_array($variant,$dropdowns[$k])  ){

								if($k==0 or !$productSelection){
									$dropdowns[$k][] = $variant;
								} else{
									if($productSelection[$k-1] == $variants[$k-1]) {
										$break = false;
										for( $h = 1; $h<=$k; $h++ ) {
											if($productSelection[$h - 1] != $variants[$h - 1]) {
												$break = true;
											}
										}
										if(!$break) {
											$dropdowns[$k][] = $variant;
										}
									}
								}
							}
						}
					}

					$class = 'vm-chzn-select';
					$selectType = 'select.genericlist';

					if(!empty($customfield->selectType)){
						$selectType = 'select.radiolist';
						$class = '';
						$dom = '';
					} else {
						vmJsApi::chosenDropDowns();
						$dom = 'select';
					}

					$attribs = array('class'=>$class.' cvselection no-vm-bind','style'=>'min-width:70px;');

					$view = 'productdetails';
					$attribs['reload'] = '1';
					if(VmConfig::get ('jdynupdate', TRUE)){
						$view = vRequest::getCmd('view','productdetails');
						if($view == 'productdetails' or ($customfield->browseajax and $view == 'category')){
							$attribs['data-dynamic-update'] = '1';
							unset($attribs['reload']);
						} else {
							$view = 'productdetails';
						}
					}

					foreach($customfield->selectoptions as $k => $soption){

						$options = array();
						$selected = false;
						if(isset($dropdowns[$k])){
							foreach($dropdowns[$k] as $i=> $elem){

								$elem = trim((string)$elem);
								$text = $elem;

								if($soption->clabel!='' and in_array($soption->voption,VirtueMartModelCustomfields::$dimensions) ){
									$rd = $soption->clabel;
									if(is_numeric($rd) and is_numeric($elem)){
										$text = number_format(round((float)$elem,(int)$rd),$rd);
									}
									//vmdebug('($dropdowns[$k] in DIMENSION value = '.$elem.' r='.$rd.' '.$text);
								} else if  ($soption->voption === 'clabels' and $soption->clabel!='') {
									$text = vmText::_($elem);
								}

								if(empty($elem)){
									$text = vmText::_('COM_VIRTUEMART_LIST_EMPTY_OPTION');
								}
								$o = new stdClass();
								$o->value = $elem;
								$o->text = $text;
								$options[] = $o;

								if($productSelection and $productSelection[$k] == $elem){
									$selected = $elem;
								}
							}
						}


						if(empty($selected)){
							$product->orderable=false;
						}
						//$idTagK = $idTag.'cvard'.$k;
						if($customfield->showlabels){
							if( in_array($soption->voption,VirtueMartModelCustomfields::$dimensions) ){
								$soption->slabel = vmText::_('COM_VIRTUEMART_'.strtoupper($soption->voption));
							} else if(!empty($soption->clabel) and !in_array($soption->voption,VirtueMartModelCustomfields::$dimensions) ){
								$soption->slabel = vmText::_($soption->clabel);
							}
							if(isset($soption->slabel)){
								$html .= '<span class="vm-cmv-label" >'.$soption->slabel.'</span>';
							}

						}
						$idTagK = '[';	//Joomla workaround to get a list without id
						$attribs['cvsel'] = 'field' . $customfield->virtuemart_customfield_id ;
						$fname = $fieldname.'['.$k.']';
						$html .= JHtml::_ ($selectType, $options, $fname, $attribs , "value", "text", $selected,$idTagK);

					}

					$Itemid = vRequest::getInt('Itemid',''); // '&Itemid=127';
					if(!empty($Itemid)){
						$Itemid = '&Itemid='.$Itemid;
					}

					//create array for js
					$jsArray = array();

					$url = '';

					foreach($customfield->options as $product_id=>$variants){

						if(!in_array($product_id,$avail)){continue;}

						$url = JRoute::_('index.php?option=com_virtuemart&view='.$view.'&virtuemart_category_id=' . $virtuemart_category_id . '&virtuemart_product_id='.$product_id.$Itemid,false);
						$jsArray[] = '["'.$url.'","'.implode('","',$variants).'"]';
					}

					vmJsApi::addJScript('cvfind');

					$BrowserNewState =  '';
					if($view != 'productdetails'){
						$BrowserNewState = 'Virtuemart.setBrowserState = false;';
					}

					$jsVariants = implode(',',$jsArray);

					$selector = $dom."[cvsel=\"".$attribs['cvsel']."\"]";
					$hash = md5($selector);
					$j = "jQuery(document).ready(function($) {
							".$BrowserNewState."
							$('".$selector."').off('change',Virtuemart.cvFind);
							$('".$selector."').on('change', { variants:[".$jsVariants."] },Virtuemart.cvFind);
						});";
					vmJsApi::addJScript('cvselvars'.$hash,$j,true,false,false,$hash);

					//Now we need just the JS to reload the correct product
					$customfield->display = $html;

					break;

				case 'A':

					$html = '';

					$productModel = VmModel::getModel ('product');

					//Note by Jeremy Magne (Daycounts) 2013-08-31
					//Previously the the product model is loaded but we need to ensure the correct product id is set because the getUncategorizedChildren does not get the product id as parameter.
					//In case the product model was previously loaded, by a related product for example, this would generate wrong uncategorized children list
					$productModel->setId($customfield->virtuemart_product_id);

					$uncatChildren = $productModel->getUncategorizedChildren ($customfield->withParent);

					$options = array();

					$selected = vRequest::getInt ('virtuemart_product_id',0);
					$selectedFound = false;

					$view = 'productdetails';
					$attribs['reload'] = '1';
					if(VmConfig::get ('jdynupdate', TRUE)){
						$view = vRequest::getCmd('view','productdetails');
						if($view == 'productdetails' or ($customfield->browseajax and $view == 'category')){
							$attribs['data-dynamic-update'] = '1';
							unset($attribs['reload']);
						} else {
							$view = 'productdetails';
						}
					}

					$Itemid = vRequest::getInt('Itemid',''); // '&Itemid=127';
					if(!empty($Itemid)){
						$Itemid = '&Itemid='.$Itemid;
					}

					if(!$customfield->withParent){
						$options[0] = $emptyOption;
						$options[0]->value = JRoute::_ ('index.php?option=com_virtuemart&view='.$view.'&virtuemart_category_id=' . $virtuemart_category_id . '&virtuemart_product_id=' . $customfield->virtuemart_product_id,FALSE);
						//$options[0] = array('value' => JRoute::_ ('index.php?option=com_virtuemart&view=productdetails&virtuemart_category_id=' . $virtuemart_category_id . '&virtuemart_product_id=' . $customfield->virtuemart_product_id,FALSE), 'text' => vmText::_ ('COM_VIRTUEMART_ADDTOCART_CHOOSE_VARIANT'));
					}

					$parentStock = 0;
					if($uncatChildren){
						foreach ($uncatChildren as $k => $child) {
							/*if(!isset($child[$customfield->customfield_value])){
								vmdebug('The child has no value at index '.$customfield->customfield_value,$customfield,$child);
							} else {*/

							$productChild = $productModel->getProduct((int)$child,true);

							if(!$productChild) continue;
							if(!isset($productChild->{$customfield->customfield_value})){
								vmdebug('The child has no value at index '.$child);
								continue;
							}
							$available = $productChild->product_in_stock - $productChild->product_ordered;
							if(VmConfig::get('stockhandle','none')=='disableit_children' and $available <= 0){
								continue;
							}
							$parentStock += $available;
							$priceStr = '';
							if($customfield->wPrice){
								//$product = $productModel->getProductSingle((int)$child['virtuemart_product_id'],false);
								$productPrices = $calculator->getProductPrices ($productChild);
								$priceStr =  ' (' . $currency->priceDisplay ($productPrices['salesPrice']) . ')';
							}
							$options[] = array('value' => JRoute::_ ('index.php?option=com_virtuemart&view='.$view.'&virtuemart_category_id=' . $virtuemart_category_id . '&virtuemart_product_id=' . $productChild->virtuemart_product_id,false), 'text' => $productChild->{$customfield->customfield_value}.$priceStr);

							if($selected==$child){
								$selectedFound = true;
								vmdebug($customfield->virtuemart_product_id.' $selectedFound by vRequest '.$selected);
							}
							//vmdebug('$child productId ',$child['virtuemart_product_id'],$customfield->customfield_value,$child);
							//}
						}
					}

					if(!$selectedFound){
						$pos = array_search($customfield->virtuemart_product_id, $product->allIds);
						if(isset($product->allIds[$pos-1])){
							$selected = $product->allIds[$pos-1];
							//vmdebug($customfield->virtuemart_product_id.' Set selected to - 1 allIds['.($pos-1).'] = '.$selected.' and count '.$dynChilds);
							//break;
						} elseif(isset($product->allIds[$pos])){
							$selected = $product->allIds[$pos];
							//vmdebug($customfield->virtuemart_product_id.' Set selected to allIds['.$pos.'] = '.$selected.' and count '.$dynChilds);
						} else {
							$selected = $customfield->virtuemart_product_id;
							//vmdebug($customfield->virtuemart_product_id.' Set selected to $customfield->virtuemart_product_id ',$selected,$product->allIds);
						}
					}

					$url = 'index.php?option=com_virtuemart&view='.$view.'&virtuemart_category_id='.
					$virtuemart_category_id .'&virtuemart_product_id='. $selected;
					$attribs['option.key.toHtml'] = false;
					$attribs['id'] = '[';//$idTag;


					$och = '';
					if(!empty($attribs['reload'])){
						$och = ' onchange="window.top.location.href=this.options[this.selectedIndex].value" reload=1';
						unset($attribs['reload']);
					} else {
						$och = ' data-dynamic-update="1"';
						unset($attribs['data-dynamic-update']);
					}

					$attribs['list.attr'] = 'size="1" class="vm-chzn-select no-vm-bind avselection"'.$och;
					$attribs['list.translate'] = false;
					$attribs['option.key'] = 'value';
					$attribs['option.text'] = 'text';
					$attribs['list.select'] = JRoute::_ ($url,false);


					$html .= JHtml::_ ('select.genericlist', $options, $fieldname, $attribs);
					//vmdebug('My view $attribs',$attribs,$html);
					vmJsApi::chosenDropDowns();

					if($customfield->parentOrderable==0){
						if($product->virtuemart_product_id==$customfield->virtuemart_product_id){
							$product->orderable = false;
							$product->product_in_stock = $parentStock;
						}
					}

					$dynChilds++;
					$customfield->display = $html;

					vmJsApi::addJScript('cvfind');

					$BrowserNewState =  '';
					if($view != 'productdetails'){
						$BrowserNewState = 'Virtuemart.setBrowserState = false;';
					}

					if($customfield->browseajax){
						$j = "jQuery(document).ready(function($) {
							".$BrowserNewState."
							$('select.avselection').off('change',Virtuemart.avFind);
							$('select.avselection').on('change',{},Virtuemart.avFind);
						});";
						vmJsApi::addJScript('avselvars',$j,true);
					}
					break;

				/*Date variant*/
				case 'D':
					if(empty($customfield->custom_value)) $customfield->custom_value = 'LC2';
					//Customer selects date
					if($customfield->is_input){
						$customfield->display =  '<span class="product_custom_date">' . vmJsApi::jDate ($customfield->customfield_value,$customProductDataName) . '</span>'; //vmJsApi::jDate($field->custom_value, 'field['.$row.'][custom_value]','field_'.$row.'_customvalue').$priceInput;
					}
					//Customer just sees a date
					else {
						$customfield->display =  '<span class="product_custom_date">' . vmJsApi::date ($customfield->customfield_value, $customfield->custom_value, TRUE) . '</span>';
					}

					break;
				/* text area or editor No vmText, only displayed in BE */
				case 'X':
				case 'Y':
					$customfield->display =  $customfield->customfield_value;

					break;
				/* string or integer */
				case 'B':
				case 'S':
				case 'M':

					//vmdebug('Example for params ',$customfield);
					if(isset($customfield->selectType)){
						if(empty($customfield->selectType)){
							$selectType = 'select.genericlist';
							if(!empty($customfield->is_input)){
								vmJsApi::chosenDropDowns();
								$class = 'class="vm-chzn-select"';
								$idTag = '[';
							}
						} else {
							$selectType = 'select.radiolist';
							$class = '';
						}
					} else {
						if($type== 'M'){
							$selectType = 'select.radiolist';
							$class = '';
						} else {
							$selectType = 'select.genericlist';
							if(!empty($customfield->is_input)){
								vmJsApi::chosenDropDowns();
								$class = 'class="vm-chzn-select"';
								$idTag = '[';
							}
						}
					}

					if($customfield->is_list and $customfield->is_list!=2){

						if(!empty($customfield->is_input)){

							$options = array();

							if($customfield->addEmpty){
								$options[0] = $emptyOption;
							}

							$values = explode (';', $customfield->custom_value);

							foreach ($values as $key => $val) {

								//if($val == 0 and $customfield->addEmpty){
									//continue;
								//}
								if($type == 'M'){
									$tmp = array('value' => $val, 'text' => VirtueMartModelCustomfields::displayCustomMedia ($val,'product',$customfield->width,$customfield->height));
								} else {
									$tmp = array('value' => $val, 'text' => vmText::_($val));
								}
								$options[] = (object)$tmp;
							}
							$currentValue = $customfield->customfield_value;

							$customfield->display = JHtml::_ ($selectType, $options, $customProductDataName.'[' . $customfield->virtuemart_customfield_id . ']', $class, 'value', 'text', $currentValue,$idTag);
						} else {
							if($type == 'M'){
								$customfield->display =  VirtueMartModelCustomfields::displayCustomMedia ($customfield->customfield_value,'product',$customfield->width,$customfield->height);
							} else {
								$customfield->display =  vmText::_ ($customfield->customfield_value);
							}
						}
					} else {

						if(!empty($customfield->is_input)){

							if(!isset($selectList[$customfield->virtuemart_custom_id])) {
								$selectList[$customfield->virtuemart_custom_id] = $k;
								if($customfield->addEmpty){
									if(empty($customfields[$selectList[$customfield->virtuemart_custom_id]]->options)){
										$customfields[$selectList[$customfield->virtuemart_custom_id]]->options[0] = $emptyOption;
										$customfields[$selectList[$customfield->virtuemart_custom_id]]->options[0]->virtuemart_customfield_id = $emptyOption->value;
										//$customfields[$selectList[$customfield->virtuemart_custom_id]]->options['nix'] = array('virtuemart_customfield_id' => 'none', 'text' => vmText::_ ('COM_VIRTUEMART_ADDTOCART_CHOOSE_VARIANT'));
									}
								}

								$tmpField = clone($customfield);
								$tmpField->options = null;
								$customfield->options[$customfield->virtuemart_customfield_id] = $tmpField;

								$customfield->customProductDataName = $customProductDataName;

							} else {
								$customfields[$selectList[$customfield->virtuemart_custom_id]]->options[$customfield->virtuemart_customfield_id] = $customfield;
								unset($customfields[$k]);

							}

							$default = reset($customfields[$selectList[$customfield->virtuemart_custom_id]]->options);
							foreach ($customfields[$selectList[$customfield->virtuemart_custom_id]]->options as &$productCustom) {
								if(!isset($productCustom->customfield_price)) $productCustom->customfield_price = 0.0;
								if(!isset($productCustom->customfield_value)) $productCustom->customfield_value = '';
								$price = VirtueMartModelCustomfields::renderCustomfieldPrice($productCustom, $product, $calculator);
								if($type == 'M'){
									$productCustom->text = VirtueMartModelCustomfields::displayCustomMedia ($productCustom->customfield_value,'product',$customfield->width,$customfield->height).' '.$price;
								} else {
									$trValue = vmText::_($productCustom->customfield_value);
									if($productCustom->customfield_value!=$trValue and strpos($trValue,'%1')!==false){
										$productCustom->text = vmText::sprintf($productCustom->customfield_value,$price);
									} else {
										$productCustom->text = $trValue.' '.$price;
									}
								}
							}


							$customfields[$selectList[$customfield->virtuemart_custom_id]]->display = JHtml::_ ($selectType, $customfields[$selectList[$customfield->virtuemart_custom_id]]->options,
							$customfields[$selectList[$customfield->virtuemart_custom_id]]->customProductDataName,
							$class, 'virtuemart_customfield_id', 'text', $default->customfield_value,$idTag);	//*/
						} else {
							if($type == 'M'){
								$customfield->display = VirtueMartModelCustomfields::displayCustomMedia ($customfield->customfield_value,'product',$customfield->width,$customfield->height);
							} else {
								$customfield->display = vmText::_ ($customfield->customfield_value);
							}
						}
					}

					break;

				// Property
				case 'P':
					//$customfield->display = vmText::_ ('COM_VIRTUEMART_'.strtoupper($customfield->customfield_value));
					$attr = $customfield->customfield_value;
					$lkey = 'COM_VIRTUEMART_'.strtoupper($customfield->customfield_value).'_FE';
					$trValue = vmText::_ ($lkey);
					$options[] = array('value' => 'product_length', 'text' => vmText::_ ('COM_VIRTUEMART_PRODUCT_LENGTH'));
					$options[] = array('value' => 'product_width', 'text' => vmText::_ ('COM_VIRTUEMART_PRODUCT_WIDTH'));
					$options[] = array('value' => 'product_height', 'text' => vmText::_ ('COM_VIRTUEMART_PRODUCT_HEIGHT'));
					$options[] = array('value' => 'product_weight', 'text' => vmText::_ ('COM_VIRTUEMART_PRODUCT_WEIGHT'));

					$dim = '';

					if($attr == 'product_length' or $attr == 'product_width' or $attr == 'product_height'){
						$dim = $product->product_lwh_uom;
					} else if($attr == 'product_weight') {
						$dim = $product->product_weight_uom;
					}
					if(!isset($product->$attr)){
						logInfo('customfield.php: case P, property '.$attr.' does not exists. virtuemart_custom_id: '.$customfield->virtuemart_custom_id);
						break;
					}
					$val = $product->$attr;
					if($customfield->round!=0){
						if(empty($customfield->digits)) $customfield->digits = 0;
						$val = $currency->getFormattedNumber($val,$customfield->digits);
					}
					if($lkey!=$trValue and strpos($trValue,'%1')!==false) {
						$customfield->display = vmText::sprintf( $customfield->customfield_value, $val , $dim );
					} else if($lkey!=$trValue) {
						$customfield->display = $trValue.' '.$val;
					} else {
						$customfield->display = vmText::_ ('COM_VIRTUEMART_'.strtoupper($customfield->customfield_value)).' '.$val.$dim;
					}

					break;
				case 'Z':
					if(empty($customfield->customfield_value)) break;
					$html = '';
					$q = 'SELECT * FROM `#__virtuemart_categories_' . VmConfig::$vmlang . '` as l INNER JOIN `#__virtuemart_categories` AS c ON (l.`virtuemart_category_id`=c.`virtuemart_category_id`) WHERE `published`=1 AND l.`virtuemart_category_id`= "' . (int)$customfield->customfield_value . '" ';
					$db = JFactory::getDBO();
					$db->setQuery ($q);
					if ($category = $db->loadObject ()) {

						if(empty($category->virtuemart_category_id)) break;

						$q = 'SELECT `virtuemart_media_id` FROM `#__virtuemart_category_medias`WHERE `virtuemart_category_id`= "' . $category->virtuemart_category_id . '" ';
						$db->setQuery ($q);
						$thumb = '';
						if ($media_id = $db->loadResult ()) {
							$thumb = VirtueMartModelCustomfields::displayCustomMedia ($media_id,'category',$customfield->width,$customfield->height);
						}
						$customfield->display = JHtml::link (JRoute::_ ('index.php?option=com_virtuemart&view=category&virtuemart_category_id=' . $category->virtuemart_category_id), $thumb . ' ' . $category->category_name, array('title' => $category->category_name,'target'=>'_blank'));
					}

					break;
				case 'R':
					if(empty($customfield->customfield_value)){
						$customfield->display = 'customfield related product has no value';
						break;
					}
					$pModel = VmModel::getModel('product');
					$related = $pModel->getProduct((int)$customfield->customfield_value,TRUE,$customfield->wPrice,TRUE,1);

					if(!$related) break;

					$thumb = '';
					if($customfield->wImage) {
						if(!empty( $related->virtuemart_media_id[0] )) {
							$thumb = VirtueMartModelCustomfields::displayCustomMedia( $related->virtuemart_media_id[0], 'product', $customfield->width, $customfield->height ).' ';
						} else {
							$thumb = VirtueMartModelCustomfields::displayCustomMedia( 0, 'product', $customfield->width, $customfield->height ).' ';
						}
					}

					if($customfield->waddtocart){
						if (!empty($related->customfields)) {

							if (!class_exists ('vmCustomPlugin')) {
								require(VMPATH_PLUGINLIBS . DS . 'vmcustomplugin.php');
							}
							$customfieldsModel = VmModel::getModel ('customfields');
							if(empty($customfield->from)) {
								$customfield->from = $related->virtuemart_product_id;
								$customfieldsModel -> displayProductCustomfieldFE ($related, $related->customfields);
							} else if($customfield->from!=$related->virtuemart_product_id){
								$customfieldsModel -> displayProductCustomfieldFE ($related, $related->customfields);
							}

						}
						$isCustomVariant = false;
						if (!empty($related->customfields)) {
							foreach ($related->customfields as $k => $custom) {
								if($custom->field_type == 'C' and $custom->virtuemart_product_id != (int)$customfield->customfield_value){
									$isCustomVariant = $custom;
								}
								if (!empty($custom->layout_pos)) {
									$related->customfieldsSorted[$custom->layout_pos][] = $custom;
								} else {
									$related->customfieldsSorted['normal'][] = $custom;
								}
								unset($related->customfields);
							}

						}
					}
					$customfield->display = shopFunctionsF::renderVmSubLayout('related',array('customfield'=>$customfield,'related'=>$related, 'thumb'=>$thumb));

					break;
			}

			$viewData['customfields'][$k] = $customfield;
			//vmdebug('my customfields '.$type,$viewData['customfields'][$k]->display);
		}

	}

	static function renderCustomfieldsCart($product, $html, $trigger){
		if(isset($product->param)){
			vmTrace('param found, seek and destroy');
			return false;
		}
		$row = 0;
		if (!class_exists ('shopFunctionsF'))
			require(VMPATH_SITE . DS . 'helpers' . DS . 'shopfunctionsf.php');

		$variantmods = isset($product -> customProductData)?$product -> customProductData:$product -> product_attribute;

		if(!is_array($variantmods)){
			$variantmods = json_decode($variantmods,true);
		}

		//We let that here as Fallback
		if(empty($product->customfields)){

			$productDB = VmModel::getModel('product')->getProduct($product->virtuemart_product_id);
			if($productDB and $productDB->customfields){

				$product->customfields = $productDB->customfields;
			} else {
				$product->customfields = array();
			}
		}
		//vmdebug('renderCustomfieldsCart $variantmods',$variantmods);
		$productCustoms = array();
		foreach( (array)$product->customfields as $prodcustom){

			//We just add the customfields to be shown in the cart to the variantmods
			if(is_object($prodcustom)){

				//We need this here to ensure that broken customdata of order items is shown updated info, or at least displayed,
				if($prodcustom->is_cart_attribute or $prodcustom->is_input){

					//The problem here is that a normal value and an array can be valid. The function should complement, or update the product. So we are not allowed
					//to override existing values. When the $variantmods array is not set for the key, then we add an array, when the customproto is used more than one time
					//the missing values are added with an own key.
					if(!isset($variantmods[$prodcustom->virtuemart_custom_id]) or (is_array($variantmods[$prodcustom->virtuemart_custom_id]) and !isset($variantmods[$prodcustom->virtuemart_custom_id][$prodcustom->virtuemart_customfield_id])) ){
						$variantmods[$prodcustom->virtuemart_custom_id][$prodcustom->virtuemart_customfield_id] = $prodcustom->virtuemart_customfield_id;
						//vmdebug('foreach $variantmods $customfield_id ', $prodcustom->virtuemart_custom_id, $prodcustom->virtuemart_customfield_id,$variantmods);
					}
				}

				$productCustoms[$prodcustom->virtuemart_customfield_id] = $prodcustom;
			}
		}
		//vmdebug('renderCustomfieldsCart $variantmods foreach',$variantmods);
		foreach ( (array)$variantmods as $i => $customfield_ids) {

			if(!is_array($customfield_ids)){
				$customfield_ids = array( $customfield_ids =>false);
			}

			foreach($customfield_ids as $customfield_id=>$params){

				if(empty($productCustoms) or !isset($productCustoms[$customfield_id])){
					//vmdebug('displayProductCustomfieldSelected continue',$customfield_id,$productCustoms);
					continue;
				}
				$productCustom = $productCustoms[$customfield_id];
				//vmdebug('displayProductCustomfieldSelected ',$customfield_id,$productCustom);
				//The stored result in vm2.0.14 looks like this {"48":{"textinput":{"comment":"test"}}}
				//and now {"32":[{"invala":"100"}]}
				if (!empty($productCustom)) {
					$otag = ' <span class="product-field-type-' . $productCustom->field_type . '">';
					$tmp = '';
					if ($productCustom->field_type == "E") {

						if (!class_exists ('vmCustomPlugin'))
							require(VMPATH_PLUGINLIBS . DS . 'vmcustomplugin.php');
						JPluginHelper::importPlugin ('vmcustom');
						$dispatcher = JDispatcher::getInstance ();
						$dispatcher->trigger ($trigger.'VM3', array(&$product, &$productCustom, &$tmp));
					}
					else {
						$value = '';

						if (($productCustom->field_type == 'G')) {
							$db = JFactory::getDBO ();
							$db->setQuery ('SELECT  `product_name` FROM `#__virtuemart_products_' . VmConfig::$vmlang . '` WHERE virtuemart_product_id=' . (int)$productCustom->customfield_value);
							$child = $db->loadObject ();
							$value = $child->product_name;
						}
						elseif (($productCustom->field_type == 'M')) {
							$customFieldModel = VmModel::getModel('customfields');
							$value = $customFieldModel->displayCustomMedia ($productCustom->customfield_value,'product',$productCustom->width,$productCustom->height,VirtueMartModelCustomfields::$useAbsUrls);
						}
						elseif (($productCustom->field_type == 'S')) {

							if($productCustom->is_list and $productCustom->is_input){
								if($productCustom->is_list==2){
									$value = vmText::_($productCustom->customfield_value);
								} else {
									$value = vmText::_($params);
								}

							} else {
								$value = vmText::_($productCustom->customfield_value);
							}
						}
						elseif (($productCustom->field_type == 'A')) {
							if(!property_exists($product,$productCustom->customfield_value)){
								$productDB = VmModel::getModel('product')->getProduct($product->virtuemart_product_id);
								if($productDB){
									$attr = $productCustom->customfield_value;
									$product->$attr = $productDB->$attr;
								}
							}
							$value = vmText::_( $product->{$productCustom->customfield_value} );
						}
						elseif (($productCustom->field_type == 'C')) {

							foreach($productCustom->options->{$product->virtuemart_product_id} as $k=>$option){
								$value .= '<span> ';
								if(!empty($productCustom->selectoptions[$k]->clabel) and in_array($productCustom->selectoptions[$k]->voption,VirtueMartModelCustomfields::$dimensions)){
									$value .= vmText::_('COM_VIRTUEMART_'.$productCustom->selectoptions[$k]->voption);
									$rd = $productCustom->selectoptions[$k]->clabel;
									if(is_numeric($rd) and is_numeric($option)){
										$value .= ' '.number_format(round((float)$option,(int)$rd),$rd);
									}
								} else {
									if(!empty($productCustom->selectoptions[$k]->clabel)) $value .= vmText::_($productCustom->selectoptions[$k]->clabel);
									$value .= ' '.vmText::_($option).' ';
								}
								$value .= '</span><br>';
							}
							$value = trim($value);
							if(!empty($value)){
								$html .= $otag.$value.'</span><br />';
							}

							continue;
						}
						else {
							$value = vmText::_($productCustom->customfield_value);
						}
						$trTitle = vmText::_($productCustom->custom_title);
						$tmp = '';

						if($productCustom->custom_title!=$trTitle and strpos($trTitle,'%1')!==false){
							$tmp .= vmText::sprintf($productCustom->custom_title,$value);
						} else {
							$tmp .= $trTitle.' '.$value;
						}
					}
					if(!empty($tmp)){
						$html .= $otag.$tmp.'</span><br />';
					}


				}
				else {
					foreach ((array)$customfield_id as $key => $value) {
						$html .= '<br/ >Couldnt find customfield' . ($key ? '<span>' . $key . ' </span>' : '') . $value;
					}
					vmdebug ('customFieldDisplay, $productCustom is EMPTY '.$customfield_id);
				}
			}

		}

		return $html . '</div>';
	}
}


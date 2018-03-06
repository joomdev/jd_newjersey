<?php
/**
 *
 * Description
 *
 * @packageVirtueMart
 * @subpackage Config
 * @author RickG
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: default_shopfront.php 9694 2017-12-06 17:41:34Z StefanSTS $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');?>
<table width="100%">
<tr>
<td valign="top" width="50%">
<fieldset>
<legend><?php echo vmText::_('COM_VIRTUEMART_ADMIN_CFG_MORE_CORE_SETTINGS'); ?></legend>
<table class="admintable">
	<?php
	echo VmHTML::row('raw','COM_VIRTUEMART_WEIGHT_UNIT_DEFAULT',ShopFunctions::renderWeightUnitList('weight_unit_default', VmConfig::get('weight_unit_default')));
	echo VmHTML::row('raw','COM_VIRTUEMART_LWH_UNIT_DEFAULT',ShopFunctions::renderLWHUnitList('lwh_unit_default', VmConfig::get('lwh_unit_default')));
	echo VmHtml::row('input','COM_VM_PROVIDED_UNITS','norm_units',VmConfig::get('norm_units', 'KG,100G,M,SM,CUBM,L,100ML,P'));
	echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_SHOW_PRINTICON','show_printicon',VmConfig::get('show_printicon',1));
	echo VmHTML::row('checkbox','COM_VIRTUEMART_PDF_ICON_SHOW','pdf_icon',VmConfig::get('pdf_icon',0));
?>
</table>
</fieldset>
<fieldset>
<legend><?php echo vmText::_('COM_VIRTUEMART_CFG_RECOMMEND_ASK'); ?></legend>
<table class="admintable">
<?php
	echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_SHOW_EMAILFRIEND','show_emailfriend',VmConfig::get('show_emailfriend',0));
	echo VmHTML::row('checkbox','COM_VIRTUEMART_RECCOMEND_UNATUH','recommend_unauth',VmConfig::get('recommend_unauth',0));
	echo VmHTML::row('checkbox','COM_VIRTUEMART_ASK_QUESTION_CAPTCHA','ask_captcha',VmConfig::get('ask_captcha',0));
	echo VmHTML::row('checkbox','COM_VIRTUEMART_ASK_QUESTION_SHOW','ask_question',VmConfig::get('ask_question',0));
	echo VmHTML::row('input','COM_VIRTUEMART_ASK_QUESTION_MIN_LENGTH','asks_minimum_comment_length',VmConfig::get('asks_minimum_comment_length',50),'class="inputbox"','',4,4);
	echo VmHTML::row('input','COM_VIRTUEMART_ASK_QUESTION_MAX_LENGTH','asks_maximum_comment_length',VmConfig::get('asks_maximum_comment_length',2000),'class="inputbox"','',5,5);
?>
</table>
</fieldset>
<fieldset>
<legend><?php echo vmText::_('COM_VIRTUEMART_COUPONS_ENABLE'); ?></legend>
	<table class="admintable">
		<?php echo VmHTML::row('checkbox','COM_VIRTUEMART_COUPONS_ENABLE','coupons_enable',VmConfig::get('coupons_enable',0));

		$_defaultExpTime = array(
		'1,D' => '1 ' . vmText::_('COM_VIRTUEMART_DAY')
		, '1,W' => '1 ' . vmText::_('COM_VIRTUEMART_WEEK')
		, '2,W' => '2 ' . vmText::_('COM_VIRTUEMART_WEEK_S')
		, '1,M' => '1 ' . vmText::_('COM_VIRTUEMART_MONTH')
		, '3,M' => '3 ' . vmText::_('COM_VIRTUEMART_MONTH_S')
		, '6,M' => '6 ' . vmText::_('COM_VIRTUEMART_MONTH_S')
		, '1,Y' => '1 ' . vmText::_('COM_VIRTUEMART_YEAR')
		);
		echo VmHTML::row('raw','COM_VIRTUEMART_COUPONS_EXPIRE',VmHTML::selectList('coupons_default_expire', VmConfig::get('coupons_default_expire'), $_defaultExpTime));
		$attrlist = 'class="inputbox" multiple="multiple" ';
		echo VmHTML::row('genericlist','COM_VIRTUEMART_COUPONS_REMOVE',$this->os_Options,'cp_rm[]',$attrlist, 'order_status_code', 'order_status_name', VmConfig::get('cp_rm',array('C')), 'cp_rm',true);
	?>
	</table>
</fieldset>
<fieldset>
<legend><?php echo vmText::_('COM_VIRTUEMART_ADMIN_CFG_PRODUCT_LISTING'); ?></legend>
<table class="admintable">
<?php
	echo VmHTML::row('checkbox','COM_VIRTUEMART_PRODUCT_NAVIGATION_SHOW','product_navigation',VmConfig::get('product_navigation',1));
	echo VmHTML::row('checkbox','COM_VIRTUEMART_DISPLAY_STOCK','display_stock',VmConfig::get('display_stock',1));
	echo VmHTML::row('checkbox','COM_VIRTUEMART_SHOW_PRODUCT_CUSTOMS','show_pcustoms',VmConfig::get('show_pcustoms',1));
    echo VmHTML::row('checkbox','COM_VIRTUEMART_SUBCAT_PRODUCTS_SHOW','show_subcat_products',VmConfig::get('show_subcat_products',0));
	echo VmHTML::row('checkbox','COM_VIRTUEMART_UNCAT_PARENT_PRODUCTS_SHOW','show_uncat_parent_products',VmConfig::get('show_uncat_parent_products',0));
	echo VmHTML::row('checkbox','COM_VIRTUEMART_UNCAT_CHILD_PRODUCTS_SHOW','show_uncat_child_products',VmConfig::get('show_uncat_child_products',0));
	echo VmHTML::row('checkbox','COM_VIRTUEMART_SHOW_PRODUCTS_UNPUBLISHED_CATEGORIES','show_unpub_cat_products',VmConfig::get('show_unpub_cat_products',1));
	echo VmHTML::row('checkbox','COM_VM_PRODUCTDETAILS_DISPL_CATS','cat_productdetails', VmConfig::get('cat_productdetails',0));
	echo VmHTML::row('input','COM_VIRTUEMART_LATEST_PRODUCTS_DAYS','latest_products_days',VmConfig::get('latest_products_days',7),'class="inputbox"','',4,4);
	$latest_products_orderBy = array(
		'modified_on' => vmText::_('COM_VIRTUEMART_LATEST_PRODUCTS_ORDERBY_MODIFIED'),
		'created_on' => vmText::_('COM_VIRTUEMART_LATEST_PRODUCTS_ORDERBY_CREATED')
	);
	echo VmHTML::row('selectList','COM_VIRTUEMART_LATEST_PRODUCTS_ORDERBY','latest_products_orderBy',VmConfig::get('latest_products_orderBy', 'created_on'),$latest_products_orderBy);
?>
</table>
</fieldset>
</td>
<td>
	<fieldset class="checkboxes">
		<legend>
			<span class="hasTip" title="<?php echo vmText::_('COM_VIRTUEMART_CFG_POOS_ENABLE_EXPLAIN'); ?>">
				<?php echo vmText::_('COM_VIRTUEMART_CFG_POOS_ENABLE'); ?>
			</span>
		</legend>
		<div>
			<?php echo VmHTML::checkbox('lstockmail', VmConfig::get('lstockmail')); ?>
			<span class="hasTip" title="<?php echo vmText::_('COM_VIRTUEMART_CFG_LOWSTOCK_NOTIFY_TIP'); ?>">
				<label for="reviews_autopublish">
					<?php echo vmText::_('COM_VIRTUEMART_CFG_LOWSTOCK_NOTIFY'); ?>
				</label>
			</span>
		</div>
		<div>
			<?php echo VmHTML::checkbox('stockhandle_products', VmConfig::get('stockhandle_products')); ?>
			<span class="hasTip" title="<?php echo vmText::_('COM_VIRTUEMART_CFG_POOS_DISCONTINUED_PRODUCTS_TIP'); ?>">
				<label for="stockhandle_products">
					<?php echo vmText::_('COM_VIRTUEMART_CFG_POOS_DISCONTINUED_PRODUCTS'); ?>
				</label>
			</span>
		</div>
		<?php
		$options = array(
			'none' => vmText::_('COM_VIRTUEMART_ADMIN_CFG_POOS_NONE'),
			'disableit' => vmText::_('COM_VIRTUEMART_ADMIN_CFG_POOS_DISABLE_IT'),
			'disableit_children' => vmText::_('COM_VIRTUEMART_ADMIN_CFG_POOS_DISABLE_IT_CHILDREN'),
			'disableadd' => vmText::_('COM_VIRTUEMART_ADMIN_CFG_POOS_DISABLE_ADD'),
			'risetime' => vmText::_('COM_VIRTUEMART_ADMIN_CFG_POOS_RISE_AVATIME')
		);
		echo VmHTML::radioList('stockhandle', VmConfig::get('stockhandle', 'none'), $options);
		?>
		<div style="font-weight:bold;">
					<span class="hasTip" title="<?php echo vmText::_('COM_VIRTUEMART_AVAILABILITY_EXPLAIN'); ?>">
						<?php echo vmText::_('COM_VIRTUEMART_AVAILABILITY'); ?>
					</span>
		</div>
		<input type="text" class="inputbox" id="product_availability" name="rised_availability" value="<?php echo VmConfig::get('rised_availability'); ?>"/>
		<span class="icon-nofloat vmicon vmicon-16-info tooltip" title="<?php echo '<b>' . vmText::_('COM_VIRTUEMART_AVAILABILITY') . '</b><br/ >' . vmText::_('COM_VIRTUEMART_PRODUCT_FORM_AVAILABILITY_TOOLTIP1') ?>"></span>

		<div class="clr"></div>
		<?php if(!empty($this->imagePath) and JFolder::exists(VMPATH_ROOT . $this->imagePath)) {
			echo JHtml::_('list.images', 'image', VmConfig::get('rised_availability'), " ", $this->imagePath);
		} else {
			echo vmText::_('COM_VIRTUEMART_ADMIN_CFG_ASSETS_GENERAL_PATH_MISSING');
		}?>
		<span class="icon-nofloat vmicon vmicon-16-info tooltip" title="<?php echo '<b>' . vmText::_('COM_VIRTUEMART_AVAILABILITY') . '</b><br/ >' . vmText::sprintf('COM_VIRTUEMART_PRODUCT_FORM_AVAILABILITY_TOOLTIP2', $this->imagePath) ?>"></span>

		<div class="clr"></div>
		<img id="imagelib" alt="<?php echo vmText::_('COM_VIRTUEMART_PREVIEW'); ?>" name="imagelib" src="<?php if (VmConfig::get('rised_availability')) {
			echo JURI::root(true) . $this->imagePath . VmConfig::get('rised_availability');
		}?>"/>
	</fieldset>
	<fieldset>
		<legend><?php echo vmText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_TITLE'); ?></legend>
		<table class="admintable">
			<?php
			echo VmHTML::row('checkbox','COM_VIRTUEMART_REVIEWS_AUTOPUBLISH','reviews_autopublish',VmConfig::get('reviews_autopublish',0));
			echo VmHTML::row('input','COM_VIRTUEMART_ADMIN_CFG_REVIEW_MINIMUM_COMMENT_LENGTH','reviews_minimum_comment_length',VmConfig::get('reviews_minimum_comment_length',0));
			echo VmHTML::row('input','COM_VIRTUEMART_ADMIN_CFG_REVIEW_MAXIMUM_COMMENT_LENGTH','reviews_maximum_comment_length',VmConfig::get('reviews_maximum_comment_length',0));
			echo VmHTML::row('input','COM_VM_ADMIN_CFG_NUM_RATINGS','vm_num_ratings_show',VmConfig::get('vm_num_ratings_show',3));
			$showReviewFor = array('none' => vmText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_SHOW_NONE'),
				'registered' => vmText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_SHOW_REGISTERED'),
				'all' => vmText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_SHOW_ALL')
			); //showReviewFor
			echo VmHTML::row('radioList','COM_VIRTUEMART_ADMIN_CFG_REVIEW_SHOW','showReviewFor',VmConfig::get('showReviewFor','all'),$showReviewFor);

			$reviewMode = array('none' => vmText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_MODE_NONE'),
				'bought' => vmText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_MODE_BOUGHT_PRODUCT'),
				'registered' => vmText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_MODE_REGISTERED')
				//	3 => vmText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_MODE_ALL')
			);
			echo VmHTML::row('radioList','COM_VIRTUEMART_ADMIN_CFG_REVIEW','reviewMode',VmConfig::get('reviewMode','bought'),$reviewMode);

			echo VmHTML::row('radioList','COM_VIRTUEMART_ADMIN_CFG_RATING_SHOW','showRatingFor',VmConfig::get('showRatingFor','all'),$showReviewFor);
			echo VmHTML::row('radioList','COM_VIRTUEMART_ADMIN_CFG_RATING','ratingMode',VmConfig::get('ratingMode','bought'),$reviewMode);

			$attrlist = 'class="inputbox" multiple="multiple" ';
			echo VmHTML::row('genericlist','COM_VIRTUEMART_REVIEWS_OS',$this->os_Options,'rr_os[]',$attrlist, 'order_status_code', 'order_status_name', VmConfig::get('rr_os',array('C')), 'rr_os',true);
			?>

		</table>
	</fieldset>
</td>
</tr>
</table>
<?php
vmJsApi::addJScript('vm.imagechange','
	jQuery("#image").change(function () {
		var $newimage = jQuery(this).val();
		jQuery("#product_availability").val($newimage);
		jQuery("#imagelib").attr({ src:"'.JURI::root(true) . $this->imagePath.'" + $newimage, alt:$newimage });
	});');
?>


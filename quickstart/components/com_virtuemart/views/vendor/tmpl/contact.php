<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage vendor
* @author Kohl Patrick, Eugen Stranz
* @link https://virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: default.php 2701 2011-02-11 15:16:49Z impleri $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

?>

<div class="vendor-details-view vendor-details-view-contact">
	<h1><?php echo $this->vendor->vendor_store_name;
	if (!empty($this->vendor->images[0])) { ?>
		<div class="vendor-image">
		<?php echo $this->vendor->images[0]->displayMediaThumb('',false); ?>
		</div>
	<?php
	}
?>	</h1>

<?php

	echo shopFunctionsF::renderVendorAddress($this->vendor->virtuemart_vendor_id);

/*	foreach($this->userFields as $userfields){

		foreach($userfields['fields'] as $item){
			if(!empty($item['value'])){
				if($item['name']==='agreed'){
					$item['value'] =  ($item['value']===0) ? vmText::_('COM_VIRTUEMART_USER_FORM_BILLTO_TOS_NO'):vmText::_('COM_VIRTUEMART_USER_FORM_BILLTO_TOS_YES');
				}
			?><!-- span class="titles"><?php echo $item['title'] ?></span -->
						<span class="values vm2<?php echo '-'.$item['name'] ?>" ><?php echo $this->escape($item['value']) ?></span>
					<?php if ($item['name'] != 'title' and $item['name'] != 'first_name' and $item['name'] != 'middle_name' and $item['name'] != 'zip') { ?>
						<br class="clear" />
					<?php
				}
			}
		}
	} */


	$min = VmConfig::get('asks_minimum_comment_length', 50);
	$max = VmConfig::get('asks_maximum_comment_length', 2000) ;
	vmJsApi::JvalideForm();
	vmJsApi::addJScript('askform', '
		jQuery(function($){
				$("#askform").validationEngine("attach");
				$("#comment").keyup( function () {
					var result = $(this).val();
						$("#counter").val( result.length );
				});
		});
	');
?>

		<h3><?php echo vmText::_('COM_VIRTUEMART_VENDOR_ASK_QUESTION')  ?></h3>

		<div class="clear"></div>

		<div class="form-field">

			<form method="post" class="form-validate" action="<?php echo JRoute::_('index.php') ; ?>" name="askform" id="askform">

				<label><?php echo vmText::_('COM_VIRTUEMART_USER_FORM_NAME')  ?> : <input type="text" class="validate[required,minSize[4],maxSize[64]]" value="<?php echo $this->user->name ?>" name="name" id="name" size="30"  validation="required name"/></label>
				<br />
				<label><?php echo vmText::_('COM_VIRTUEMART_USER_FORM_EMAIL')  ?> : <input type="text" class="validate[required,custom[email]]" value="<?php echo $this->user->email ?>" name="email" id="email" size="30"  validation="required email"/></label>
				<br/>
				<label>
					<?php
					$ask_comment = vmText::sprintf('COM_VIRTUEMART_ASK_COMMENT', $min, $max);
					echo $ask_comment;
					?>
					<br />
					<textarea title="<?php echo $ask_comment ?>" class="validate[required,minSize[<?php echo $min ?>],maxSize[<?php echo $max ?>]] field" id="comment" name="comment" cols="30" rows="10"></textarea>
				</label>
				<div class="submit">
					<input class="highlight-button" type="submit" name="submit_ask" title="<?php echo vmText::_('COM_VIRTUEMART_ASK_SUBMIT')  ?>" value="<?php echo vmText::_('COM_VIRTUEMART_ASK_SUBMIT')  ?>" />

					<div class="width50 floatright right paddingtop">
						<?php echo vmText::_('COM_VIRTUEMART_ASK_COUNT')  ?>
						<input type="text" value="0" size="4" class="counter" id="counter" name="counter" maxlength="4" readonly="readonly" />
					</div>
				</div>

				<input type="hidden" name="view" value="vendor" />
				<input type="hidden" name="virtuemart_vendor_id" value="<?php echo $this->vendor->virtuemart_vendor_id ?>" />
				<input type="hidden" name="option" value="com_virtuemart" />
				<input type="hidden" name="task" value="mailAskquestion" />
				<?php echo JHtml::_( 'form.token' ); ?>
			</form>

		</div>


	<br class="clear" />
	<?php echo $this->linkdetails ?>

	<br class="clear" />

	<?php echo $this->linktos ?>

	<br class="clear" />
</div>
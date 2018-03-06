<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage
* @author Max Milbers
* @link https://virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: default.php 9591 2017-06-27 13:24:53Z Milbo $
*/

AdminUIHelper::startAdminArea($this);

jimport('joomla.filesystem.file');

/* Get the component name */
$option = vRequest::getCmd('option');

?>
<form action="index.php?option=com_virtuemart&view=media" method="post" name="adminForm" id="adminForm">
	<div id="header">
		<div id="filterbox">
		<table>
		  <tr>
			 <td align="left" width="60%">
				<?php echo $this->displayDefaultViewSearch('COM_VIRTUEMART_NAME','searchMedia') .' '. $this->lists['search_type'].' '. $this->lists['search_role']; ?>
			 </td>
			  <td align="left" style="width:20%;min-width:60px">
				  <?php echo VmHtml::checkbox('missing','missing',1,0); echo '<span class="hasTip" title="'.vmText::_('COM_VM_MEDIA_MISSING_TIP').'" style="vertical-align:middle;padding:4px 0 0;">'.vmText::_('COM_VM_MEDIA_MISSING').'</span>'?>
			  </td>
              <td>
				  <?php echo $this->lists['vendors'] ?>
              </td>
		  </tr>
		</table>
		</div>
		<div id="resultscounter"><?php echo $this->pagination->getResultsCounter();?></div>
	</div>
<?php
$productfileslist = $this->files;
//$roles = $this->productfilesroles;
?>
<div style="text-align: left;">
	<table class="adminlist table table-striped" cellspacing="0" cellpadding="0">
	<thead>
	<tr>
		<th class="admin-checkbox"><input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" /></th>
		<?php /*<th><?php echo vmText::_('COM_VIRTUEMART_PRODUCT_NAME'); ?></th>*/ ?>
		<th width="30%"><?php echo $this->sort('file_title', 'COM_VIRTUEMART_FILES_LIST_FILETITLE') ?></th>
		<th style="min-width:100px;width:5%;"><?php echo $this->sort('file_type', 'COM_VIRTUEMART_FILES_LIST_ROLE') ?></th>
		<th width="50%"><?php echo vmText::_('COM_VIRTUEMART_VIEW'); ?></th>
		<th style="min-width:120px;width:15%;"><?php echo vmText::_('COM_VIRTUEMART_FILES_LIST_FILENAME'); ?></th>
		<th style="min-width:30px;width:1%;max-width:40px;"><?php echo vmText::_('COM_VIRTUEMART_FILES_LIST_FILETYPE'); ?></th>
		<th><?php echo $this->sort('published', 'COM_VIRTUEMART_PUBLISHED'); ?></th>
	  <th><?php echo $this->sort('virtuemart_media_id', 'COM_VIRTUEMART_ID')  ?></th>
	</tr>
	</thead>
	<tbody>
	<?php
	if (count($productfileslist) > 0) {
		$i = 0;
		$k = 0;
		$onlyMissing = vRequest::getCmd('missing',false);
		foreach ($productfileslist as $key => $productfile) {

			if($productfile->file_is_forSale){
				$fullSizeFilenamePath = $productfile->file_url_folder.$productfile->file_name.'.'.$productfile->file_extension;
			} else {
				$rel_path = str_replace('/',DS,$productfile->file_url_folder);
				$fullSizeFilenamePath = VMPATH_ROOT.DS.$rel_path.$productfile->file_name.'.'.$productfile->file_extension;
			}

			if($onlyMissing){
				if(file_exists($fullSizeFilenamePath)){
					continue;
				}
			}
			$checked = JHtml::_('grid.id', $i , $productfile->virtuemart_media_id,null,'virtuemart_media_id');
			if (!is_null($productfile->virtuemart_media_id)) 	$published = $this->gridPublished( $productfile, $i );
			else $published = '';
			?>
			<tr class="row<?php echo $k ; ?>">
				<!-- Checkbox -->
				<td class="admin-checkbox"><?php echo $checked;   ?></td>
				<!-- Product name -->
				<?php
				$link = ""; //"index.php?view=media&limitstart=".$pagination->limitstart."&keyword=".urlencode($keyword)."&option=".$option;
			/*	?>
				<td><?php echo JHtml::_('link', JRoute::_($link, FALSE), empty($productfile->product_name)? '': htmlentities($productfile->product_name)); ?></td>
				<!-- File name -->
				<?php */
				$link = 'index.php?option='.$option.'&view=media&task=edit&virtuemart_media_id[]='.$productfile->virtuemart_media_id;
				?>
				<td><?php echo JHtml::_('link', JRoute::_($link, FALSE), $productfile->file_title, array('title' => vmText::_('COM_VIRTUEMART_EDIT').' '.$productfile->file_title)); ?></td>
				<!-- File role -->
				<td><?php
					//Just to have something, we could make this nicer with Icons
					if(!empty($productfile->file_is_product_image)) echo vmText::_('COM_VIRTUEMART_'.strtoupper($productfile->file_type).'_IMAGE') ;
					if(!empty($productfile->file_is_downloadable)) echo vmText::_('COM_VIRTUEMART_DOWNLOADABLE') ;
					if(!empty($productfile->file_is_forSale)) echo  vmText::_('COM_VIRTUEMART_FOR_SALE');

					?>
				</td>
				<!-- Preview -->
				<td>
				<?php


					if(file_exists($fullSizeFilenamePath)){
						echo $productfile->displayMediaThumb();
					} else {
						$file_url = $productfile->theme_url.'assets/images/vmgeneral/'.VmConfig::get('no_image_found');
						$file_alt = vmText::_('COM_VIRTUEMART_NO_IMAGE_SET').' '.$productfile->file_description;
						vmdebug('check path $file_url',$file_url,$fullSizeFilenamePath);
						echo $productfile->displayIt($file_url, $file_alt,'',false);
					}


				?>
				</td>
				<!-- File title -->
				<td><?php echo $productfile->file_name; ?></td>
				<!-- File extension -->
				<td style="overflow:hidden;"><span class="vmicon vmicon-16-ext_<?php echo $productfile->file_extension; ?>"></span><?php echo $productfile->file_extension; ?></td>
				<!-- published -->
				<td><?php echo $published; ?></td>
				<td><?php echo $productfile->virtuemart_media_id; ?></td>
			</tr>
		<?php
			$k = 1 - $k;
			$i++;
		}
	}
	?>
	</tbody>
	<tfoot>
	<tr>
	<td colspan="15">
		<?php echo $this->pagination->getListFooter(); ?>
	</td>
	</tr>
	</tfoot>
	</table>
<!-- Hidden Fields -->
<?php if (vRequest::getInt('virtuemart_product_id', false)) { ?>
	<input type="hidden" name="virtuemart_product_id" value="<?php echo vRequest::getInt('virtuemart_product_id',0); ?>" />
<?php } ?>
	<?php echo $this->addStandardHiddenToForm(); ?>
</form>
</div>
<?php AdminUIHelper::endAdminArea(); ?>
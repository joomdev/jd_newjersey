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
* @version $Id: edit.php 9497 2017-03-30 12:05:02Z Milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
AdminUIHelper::startAdminArea($this);
AdminUIHelper::imitateTabs('start','COM_VIRTUEMART_PRODUCT_MEDIA');

echo'<form name="adminForm" id="adminForm" method="post" enctype="multipart/form-data">';
echo '<fieldset>';

$this->media->addHidden('view','media');
$this->media->addHidden('task','');
$this->media->addHidden(JSession::getFormToken(),1);
$this->media->addHidden('file_type',$this->media->file_type);

$virtuemart_product_id = vRequest::getInt('virtuemart_product_id', '');
if(!empty($virtuemart_product_id)) $this->media->addHidden('virtuemart_product_id',$virtuemart_product_id);

$virtuemart_category_id = vRequest::getInt('virtuemart_category_id', '');
if(!empty($virtuemart_category_id)) $this->media->addHidden('virtuemart_category_id',$virtuemart_category_id);

echo $this->media->displayFileHandler();
echo '</fieldset>';
echo '</form>';

vmJsApi::addJScript('mediahandler');

AdminUIHelper::imitateTabs('end');
AdminUIHelper::endAdminArea();

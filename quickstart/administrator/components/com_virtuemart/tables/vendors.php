<?php
/**
*
* Vendor Table
*
* @package	VirtueMart
* @subpackage Vendor
* @author Max Milbers
* @link https://virtuemart.net
* @copyright Copyright (c) 2009 - 2014 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: vendors.php 9413 2017-01-04 17:20:58Z Milbo $
*/

defined('_JEXEC') or die('Restricted access');

if(!class_exists('VmTableData')) require(VMPATH_ADMIN.DS.'helpers'.DS.'vmtabledata.php');

class TableVendors extends VmTableData {

    // @var int Primary key
    var $virtuemart_vendor_id			= 0;
    var $vendor_name  	         	= '';
    var $vendor_store_name		= '';
	var $vendor_phone		= '';
    var $vendor_store_desc   		= '';
    var $vendor_currency	  		= 0;
    var $vendor_terms_of_service	= '';
    var $vendor_url					= '';
    var $vendor_accepted_currencies = array();
    var $vendor_params = '';
	var $metadesc	= '';
	var $metakey	= '';
	var $metarobot	= '';
	var $metaauthor	= '';
	var $customtitle ='';
    var $vendor_legal_info = '';
    var $vendor_letter_css = '';

    var $vendor_letter_header_html = '';
    var $vendor_letter_footer_html = '';

	var $vendor_invoice_free1 = '';
	var $vendor_invoice_free2 = '';

	var $vendor_mail_free1 = '';
	var $vendor_mail_free2 = '';
	var $vendor_mail_css = '';

    function __construct(&$db) {
		parent::__construct('#__virtuemart_vendors', 'virtuemart_vendor_id', $db);
		$this->setPrimaryKey('virtuemart_vendor_id');
		$this->setUniqueName('vendor_name');
		$this->setSlug('vendor_store_name'); //Attention the slug autoname MUST be also in the translatable, if existing
		$this->setLoggable();
		$this->setTranslatable(array(
			'vendor_store_name',
			'vendor_phone',
			'vendor_store_desc',
			'vendor_terms_of_service',
			'vendor_legal_info',
			'vendor_url',
			'metadesc',
			'metakey',
			'customtitle',
			'vendor_letter_css',
			'vendor_letter_header_html',
			'vendor_letter_footer_html',
			'vendor_invoice_free1',
			'vendor_invoice_free2',
			'vendor_mail_free1',
			'vendor_mail_free2',
			'vendor_mail_css')
		);

		$varsToPushParam = array(
			'max_cats_per_product'=>array(-1,'int'),
			'vendor_min_pov'=>array(0.0,'float'),
			'vendor_min_poq'=>array(1,'int'),
			'vendor_freeshipment'=>array(0.0,'float'),
			'vendor_address_format'=>array('','string'),
			'vendor_date_format'=>array('','string'),

			'vendor_letter_format'=>array('A4','string'),
			'vendor_letter_orientation'=>array('P','string'),

			'vendor_letter_margin_top'=>array(55,'int'),
			'vendor_letter_margin_left'=>array(25,'int'),
			'vendor_letter_margin_right'=>array(25,'int'),
			'vendor_letter_margin_bottom'=>array(25,'int'),
			'vendor_letter_margin_header'=>array(20,'int'),
			'vendor_letter_margin_footer'=>array(20,'int'),

			'vendor_letter_font'=>array('helvetica','string'),
			'vendor_letter_font_size'=>array(8, 'int'),
			'vendor_letter_header_font_size'=>array(7, 'int'),
			'vendor_letter_footer_font_size'=>array(6, 'int'),
			
			'vendor_letter_header'=>array(1,'int'),
			'vendor_letter_header_line'=>array(1,'int'),
			'vendor_letter_header_line_color'=>array("#000000",'string'),
			'vendor_letter_header_image'=>array(1,'int'),
			'vendor_letter_header_imagesize'=>array(60,'int'),
			'vendor_letter_header_cell_height_ratio'=>array(1,'float'),

			'vendor_letter_footer'=>array(1,'int'),
			'vendor_letter_footer_line'=>array(1,'int'),
			'vendor_letter_footer_line_color'=>array("#000000",'string'),
			'vendor_letter_footer_cell_height_ratio'=>array(1,'float'),
			
			'vendor_letter_add_tos' => array(0,'int'),
			'vendor_letter_add_tos_newpage' => array(1,'int'),
			'vendor_letter_for_product_pdf' => array(0,'int'),

			'vendor_mail_width' => array(640, 'int'),
			'vendor_mail_header' => array(1, 'int'),
			'vendor_mail_tos' => array(1, 'int'),
			'vendor_mail_logo' => array(1, 'int'),
			'vendor_mail_logo_width' => array(200, 'int'),
			'vendor_mail_font'=>array('helvetica','string'),
			'vendor_mail_header_font_size' => array(11, 'int'),
			'vendor_mail_font_size' => array(12, 'int'),
			'vendor_mail_footer_font_size' => array(10, 'int')
		);

		$this->setParameterable('vendor_params',$varsToPushParam);
		$this->setTableShortCut('v');

    }

}

//pure php no closing tag

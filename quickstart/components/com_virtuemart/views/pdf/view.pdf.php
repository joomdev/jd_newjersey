<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage
* @author P.Kohl
* @link https://virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: view.pdf.php 5320 2012-01-25 14:28:40Z Electrocity $
 */
defined('_JEXEC') or die;

if(!class_exists('VmView'))require(VMPATH_SITE.DS.'helpers'.DS.'vmview.php');

class VirtueMartViewPdf extends VmView
{

	var $virtuemart_vendor_id = 1;

	function __construct( $config = array() ) {

		$config['base_path'] = JPATH_COMPONENT_SITE;

		parent::__construct( $config );

	}


	function display($tpl = 'pdf'){

		if(!file_exists(VMPATH_LIBS.DS.'tcpdf'.DS.'tcpdf.php')){
			vmError('View pdf: For the pdf invoice, you must install the tcpdf library at '.VMPATH_LIBS.DS.'tcpdf');
		} else {
			$vendorModel = VmModel::getModel('vendor');
			$vendor = $vendorModel->getVendor($this->virtuemart_vendor_id);

			$viewName = vRequest::getCmd('view','productdetails');
			$class= 'VirtueMartView'.ucfirst($viewName);
			if(!class_exists($class)) require(VMPATH_SITE.DS.'views'.DS.$viewName.DS.'view.html.php');
			$view = new $class ;

			if($vendor->vendor_letter_for_product_pdf) {
				ob_start();
				$view->display($tpl);
				$html = ob_get_contents();
				ob_end_clean();

				if(!class_exists('VmPdf')) require(VMPATH_SITE.DS.'helpers'.DS.'vmpdf.php');
				$pdf = new VmVendorPDF();
				$pdf->AddPage();
				$pdf->PrintContents($html);

				$doc = JFactory::getDocument();
				$page_title = $doc->getTitle();

				$pdf->Output($page_title .'.pdf', 'I');
				JFactory::getApplication()->close();
			} else {
				$view->display($tpl);
			}
		}

	}

}

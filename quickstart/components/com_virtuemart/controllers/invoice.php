<?php
/**
 *
 * Controller for the front end Orderviews
 *
 * @package	VirtueMart
 * @subpackage User
 * @author Oscar van Eijk
 * @author Max Milbers
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2014 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id$
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access for invoices');
if(!class_exists('VmModel'))require(VMPATH_ADMIN.DS.'helpers'.DS.'vmmodel.php');
if(!class_exists('VmPdf'))require(VMPATH_SITE.DS.'helpers'.DS.'vmpdf.php');

// Load the controller framework
jimport('joomla.application.component.controller');

/**
 * VirtueMart Component Controller
 *
 * @package		VirtueMart
 */
class VirtueMartControllerInvoice extends JControllerLegacy
{

	public function __construct()
	{
		parent::__construct();
		$this->useSSL = vmURI::useSSL();
		$this->useXHTML = false;
	}

	/**
	 * Override of display to prevent caching
	 *
	 * @return  JController  A JController object to support chaining.
	 */
	public function display($cachable = false, $urlparams = false)  {
		$format = vRequest::getCmd('format','html');
		$layout = vRequest::getCmd('layout', 'invoice');

		if ($format != 'pdf') {
			$viewName='invoice';

			$view = $this->getViewWithTemplate($viewName, $format);
			//$view = $this->getView($viewName, $format);
			$view->headFooter = true;
			$view->display();
		} else {
			//PDF needs more RAM than usual
			VmConfig::ensureMemoryLimit(96);

			//PDF needs xhtml links
			$this->useXHTML = true;

			$app = JFactory::getApplication();
			// Create the invoice PDF file on disk and send that back
			$orderDetails = $this->getOrderDetails();
			if(!$orderDetails){
				$app->redirect(JRoute::_('/index.php?option=com_virtuemart'));
			}
			$fileLocation = $this->getInvoicePDF($orderDetails, 'invoice',$layout);
			if(!$fileLocation){
				$app->redirect(JRoute::_('/index.php?option=com_virtuemart'),'Invoice not created');
			}

			$fileName = basename ($fileLocation);

			if (file_exists ($fileLocation)) {
				$maxSpeed = 200;
				$range = 0;
				$size = filesize ($fileLocation);
				$contentType = 'application/pdf';
				header ("Cache-Control: public");
				header ("Content-Transfer-Encoding: binary\n");
				header ('Content-Type: application/pdf');

				$contentDisposition = 'attachment';

				$agent = strtolower ($_SERVER['HTTP_USER_AGENT']);

				if (strpos ($agent, 'msie') !== FALSE) {
					$fileName = preg_replace ('/\./', '%2e', $fileName, substr_count ($fileName, '.') - 1);
				}

				header ("Content-Disposition: $contentDisposition; filename=\"$fileName\"");

				header ("Accept-Ranges: bytes");

				if (isset($_SERVER['HTTP_RANGE'])) {
					list($a, $range) = explode ("=", $_SERVER['HTTP_RANGE']);
					str_replace ($range, "-", $range);
					$size2 = $size - 1;
					$new_length = $size - $range;
					header ("HTTP/1.1 206 Partial Content");
					header ("Content-Length: $new_length");
					header ("Content-Range: bytes $range$size2/$size");
				}
				else {
					$size2 = $size - 1;
					header ("Content-Range: bytes 0-$size2/$size");
					header ("Content-Length: " . $size);
				}

				if ($size == 0) {
					die('Zero byte file! Aborting download');
				}

				//	set_magic_quotes_runtime(0);
				$fp = fopen ("$fileLocation", "rb");
				fseek ($fp, $range);

				while (!feof ($fp) and (connection_status () == 0)) {
					set_time_limit (0);
					print(fread ($fp, 1024 * $maxSpeed));
					flush ();
					ob_flush ();
					sleep (1);
				}
				fclose ($fp);

				$app->close();

			} else {
				// TODO: Error message
				// vmError("File $fileName not found!");
			}
		}
	}

	public function getOrderDetails() {

		$orderModel = VmModel::getModel('orders');

		return $orderModel->getMyOrderDetails(0,false,false,true);
	}


	public function samplePDF() {
		if(!class_exists('VmVendorPDF')){
			vmError('vmPdf: For the pdf, you must install the tcpdf library at '.VMPATH_LIBS.DS.'tcpdf');
			return 0;
		}

		$pdf = new VmVendorPDF();
		$pdf->AddPage();
		$pdf->PrintContents(vmText::_('COM_VIRTUEMART_PDF_SAMPLEPAGE'));
		$pdf->Output("vminvoice_sample.pdf", 'I');
		JFactory::getApplication()->close();
	}

	function getViewWithTemplate($viewName, $format){

		$this->addViewPath( VMPATH_SITE.DS.'views' );
		$view = $this->getView($viewName, $format);
		$this->writeJs = false;
		$view->addTemplatePath( VMPATH_SITE.DS.'views'.DS.$viewName.DS.'tmpl' );

		if(!class_exists('VmTemplate')) require(VMPATH_SITE.DS.'helpers'.DS.'vmtemplate.php');
		$template = VmTemplate::loadVmTemplateStyle();
		$templateName = VmTemplate::setTemplate($template);

		if(!empty($templateName)){
			$TemplateOverrideFolder = JPATH_SITE.DS."templates".DS.$templateName.DS."html".DS."com_virtuemart".DS."invoice";
			if(file_exists($TemplateOverrideFolder)){
				$view->addTemplatePath( $TemplateOverrideFolder);
			}
		}
		return $view;
	}

	function getInvoicePDF($orderDetails, $viewName='invoice', $layout='invoice', $format='html', $force = false){

		vmLanguage::loadJLang('com_virtuemart',1);

		$path = VmConfig::get('forSale_path',0);
		if(empty($path) ){
			vmError('No path set to store invoices');
			return false;
		} else {
			$path .= shopFunctionsF::getInvoiceFolderName().DS;
			if(!file_exists($path)){
				vmError('Path wrong to store invoices, folder invoices does not exist '.$path);
				return false;
			} else if(!is_writable( $path )){
				vmError('Cannot store pdf, directory not writeable '.$path);
				return false;
			}
		}

		$orderModel = VmModel::getModel('orders');
		$invoiceNumberDate=array();
		if (!  $orderModel->createInvoiceNumber($orderDetails['details']['BT'], $invoiceNumberDate)) {
		    return false;
		}

		if(!empty($invoiceNumberDate[0])){
			$invoiceNumber = $invoiceNumberDate[0];
		} else {
			$invoiceNumber = FALSE;
		}

		if(!$invoiceNumber or empty($invoiceNumber)){
			vmError('Cant create pdf, createInvoiceNumber failed');
			return 0;
		}
		if (shopFunctionsF::InvoiceNumberReserved($invoiceNumber)) {
			return 0;
		}
		
		//$path .= preg_replace('/[^A-Za-z0-9_\-\.]/', '_', 'vm'.$layout.'_'.$invoiceNumber.'.pdf');
		$path .= shopFunctionsF::getInvoiceName($invoiceNumber, $layout).'.pdf';

		if(file_exists($path) and !$force){
			return $path;
		}



		$view = $this->getViewWithTemplate($viewName, $format);

		$view->invoiceNumber = $invoiceNumberDate[0];
		$view->invoiceDate = $invoiceNumberDate[1];

		$view->orderDetails = $orderDetails;
		$view->uselayout = $layout;
		$view->showHeaderFooter = false;

		$vendorModel = VmModel::getModel('vendor');
		$virtuemart_vendor_id = 1;	//We could set this automatically by the vendorId stored in the order.
		$vendor = $vendorModel->getVendor($virtuemart_vendor_id);
		
		$metadata = array (
			'title' => vmText::sprintf('COM_VIRTUEMART_INVOICE_TITLE',
				$vendor->vendor_store_name, $view->invoiceNumber, 
				$orderDetails['details']['BT']->order_number),
			'keywords' => vmText::_('COM_VIRTUEMART_INVOICE_CREATOR'));

		return VmPdf::createVmPdf($view, $path, 'F', $metadata);
	}
}

// No closing tag

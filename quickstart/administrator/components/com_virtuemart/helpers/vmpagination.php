<?php defined('_JEXEC') or die();
/**
* vmPagination,
* derived from JPagination Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
*
* @package	VirtueMart
* @subpackage Helpers
* @author Max Milbers
* @copyright Copyright (c) 2011-2014 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.net
*/

class VmPagination extends vObject {

	private $_perRow = 5;

	/**
	 * Constructor.
	 *
	 * @author Max Milbers just slightly changed
	 * @param   integer  $total       The total number of items.
	 * @param   integer  $limitstart  The offset of the item to start at.
	 * @param   integer  $limit       The number of items to display per page.
	 * @param   string   $prefix      The prefix used for request variables.
	 *
	 * @since   11.1 Joomla Plateform
	 */
	function __construct ($total, $limitstart, $limit, $perRow = 5, $prefix = '') {

		if($perRow !== 0) {
			$this->_perRow = $perRow;
		}
		// Value/type checking.
		$this->total = (int) $total;
		$this->limitstart = (int) max($limitstart, 0);
		$this->limit = (int) max($limit, 0);
		$this->prefix = $prefix;

		if ($this->limit > $this->total)
		{
			$this->limitstart = 0;
		}

		if (!$this->limit)
		{
			$this->limit = $total;
			$this->limitstart = 0;
		}

		/*
		 * If limitstart is greater than total (i.e. we are asked to display records that don't exist)
		 * then set limitstart to display the last natural page of results
		 */
		if ($this->limitstart > ($this->total - $this->limit))
		{
			$this->limitstart = max(0, (int) (ceil($this->total / $this->limit) - 1) * $this->limit);
		}

		// Set the total pages and current page values.
		if ($this->limit > 0)
		{
			$this->set('pages.total', ceil($this->total / $this->limit));
			$this->set('pages.current', ceil(($this->limitstart + 1) / $this->limit));
		}

		// Set the pagination iteration loop values.
		$displayedPages = 10;
		$this->set('pages.start', $this->get('pages.current') - ($displayedPages / 2));
		if ($this->get('pages.start') < 1)
		{
			$this->set('pages.start', 1);
		}
		if (($this->get('pages.start') + $displayedPages) > $this->get('pages.total'))
		{
			$this->set('pages.stop', $this->get('pages.total'));
			if ($this->get('pages.total') < $displayedPages)
			{
				$this->set('pages.start', 1);
			}
			else
			{
				$this->set('pages.start', $this->get('pages.total') - $displayedPages + 1);
			}
		}
		else
		{
			$this->set('pages.stop', ($this->get('pages.start') + $displayedPages - 1));
		}

		// If we are viewing all records set the view all flag to true.
		if ($limit == 0)
		{
			$this->_viewall = true;
		}
	}



	/** Creates a dropdown box for selecting how many records to show per page.
	 * Modification of Joomla Core libraries/html/pagination.php getLimitBox function
	 * The function uses as sequence a generic function or a sequence configured in the vmconfig
	 *
	 * use in a view.html.php $vmModel->setPerRow($perRow); to activate it
	 *
	 * @author Joe Motacek (Cleanshooter)
	 * @author Max Milbers
	 * @return  string   The HTML for the limit # input box.
	 * @since   11.1
	 */

	function setSequence ($sequence) {

		$this->_sequence = $sequence;
	}

	function getLimitBox ($sequence = 0) {

		$app = JFactory::getApplication();

		// Initialize variables
		$limits = array();
		$selected = $this->limit;

		if(!class_exists('VmHtml')) require(VMPATH_ADMIN.DS.'helpers'.DS.'html.php');

		// Build the select list
		if($app->isAdmin()) {

			if(empty($sequence)) {
				$sequence = VmConfig::get( 'pagseq', 0 );
			}

			if(!empty($sequence)) {
				$sequenceArray = explode( ',', $sequence );
				if(count( $sequenceArray>1 )) {
					foreach( $sequenceArray as $items ) {
						$limits[$items] = JHtml::_( 'select.option', $items );
					}
				}
			}

			if(empty($limits)) {
				$limits[15] = JHtml::_( 'select.option', 15 );
				$limits[30] = JHtml::_( 'select.option', 30 );
				$limits[50] = JHtml::_( 'select.option', 50 );
				$limits[100] = JHtml::_( 'select.option', 100 );
				$limits[200] = JHtml::_( 'select.option', 200 );
				$limits[400] = JHtml::_( 'select.option', 400 );
			}

			if(!array_key_exists( $this->limit, $limits )) {
				$limits[$this->limit] = JHtml::_( 'select.option', $this->limit );
				ksort( $limits );
			}

			$namespace = 'Joomla.';

			$id = VmHtml::ensureUniqueId('limit');
			$html = JHtml::_( 'select.genericlist', $limits, 'limit', 'class="inputbox" size="1" onchange="'.$namespace.'submitform();"', 'value', 'text', $selected, $id );
		} else {

			$getArray = vRequest::getGet();

			$link = '';
			unset ($getArray['limit']);

			foreach ($getArray as $key => $value) {
				if (is_array ($value)) {
					foreach ($value as $k => $v) {
						$link .= '&' . urlencode($key) . '[' . urlencode($k) . ']' . '=' . urlencode($v);
					}
				}
				else {
					$link .= '&' . urlencode($key) . '=' . urlencode($value);
				}
			}

			$link = 'index.php?'. ltrim( $link, '&' );

			if(empty($sequence)) {
				$sequence = VmConfig::get( 'pagseq_'.$this->_perRow );
			}
			if(!empty($sequence)) {
				$sequenceArray = explode( ',', $sequence );
				if(count( $sequenceArray>1 )) {
					foreach( $sequenceArray as $items ) {
						$limits[$items] = JHtml::_( 'select.option', JRoute::_( $link.'&limit='.$items, false ), $items );
					}
				}
			}

			if(empty($limits) or !is_array( $limits )) {
				if($this->_perRow === 1) $this->_perRow = 5;
				$limits[$this->_perRow*5] = JHtml::_( 'select.option', JRoute::_( $link.'&limit='.$this->_perRow*5, false ), $this->_perRow*5 );
				$limits[$this->_perRow*10] = JHtml::_( 'select.option', JRoute::_( $link.'&limit='.$this->_perRow*10, false ), $this->_perRow*10 );
				$limits[$this->_perRow*20] = JHtml::_( 'select.option', JRoute::_( $link.'&limit='.$this->_perRow*20, false ), $this->_perRow*20 );
				$limits[$this->_perRow*50] = JHtml::_( 'select.option', JRoute::_( $link.'&limit='.$this->_perRow*50, false ), $this->_perRow*50 );
			}
			if(!array_key_exists( $this->limit, $limits )) {
				$limits[$this->limit] = JHtml::_( 'select.option', JRoute::_( $link.'&limit='.$this->limit, false ), $this->limit );
				ksort( $limits );
			}
			$selected = JRoute::_( $link.'&limit='.$selected, false );
			$js = 'onchange="window.top.location.href=this.options[this.selectedIndex].value"';

			$id = VmHtml::ensureUniqueId('limit');
			$html = JHtml::_( 'select.genericlist', $limits, 'limit', 'class="inputbox" size="1" '.$js, 'value', 'text', $selected,$id );
		}
		return $html;
	}

	/**
	 * Return the icon to move an item UP.
	 *
	 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
	 * @license     GNU General Public License version 2 or later; see LICENSE
	 * @param   integer $i The row index.
	 * @param   boolean $condition True to show the icon.
	 * @param   string $task The task to fire.
	 * @param   string $alt The image alternative text string.
	 * @param   boolean $enabled An optional setting for access control on the action.
	 * @param   string $checkbox An optional prefix for checkboxes.
	 *
	 * @return  string   Either the icon to move an item up or a space.
	 *
	 * @since   11.1
	 */

	public function vmOrderUpIcon ($i, $ordering = true, $task = 'orderup', $alt = 'JLIB_HTML_MOVE_UP', $enabled = true, $checkbox = 'cb') {

		if(($ordering>1)) {
			return JHtml::_( 'jgrid.orderUp', $i, $task, '', $alt, $enabled, $checkbox );
		} else {
			return '&#160;';
		}
	}

	/**
	 * Return the icon to move an item DOWN.
	 *
	 * @param   integer $i The row index.
	 * @param   integer $n The number of items in the list.
	 * @param   boolean $condition True to show the icon.
	 * @param   string $task The task to fire.
	 * @param   string $alt The image alternative text string.
	 * @param   boolean $enabled An optional setting for access control on the action.
	 * @param   string $checkbox An optional prefix for checkboxes.
	 *
	 * @return  string   Either the icon to move an item down or a space.
	 *
	 * @since   11.1
	 */
	public function vmOrderDownIcon ($i, $ordering, $n, $condition = true, $task = 'orderdown', $alt = 'JLIB_HTML_MOVE_DOWN', $enabled = true, $checkbox = 'cb') {

		if($ordering<$n) {
			return JHtml::_( 'jgrid.orderDown', $i, $task, '', $alt, $enabled, $checkbox );
		} else {
			return '&#160;';
	}
}
	/**
	 * @var    integer  The record number to start displaying from.
	 * @since  11.1
	 */
	public $limitstart = null;

	/**
	 * @var    integer  Number of rows to display per page.
	 * @since  11.1
	 */
	public $limit = null;

	/**
	 * @var    integer  Total number of rows.
	 * @since  11.1
	 */
	public $total = null;

	/**
	 * @var    integer  Prefix used for request variables.
	 * @since  11.1
	 */
	public $prefix = null;

	/**
	 * @var    boolean  View all flag
	 * @since  11.1
	 */
	protected $_viewall = false;

	/**
	 * Additional URL parameters to be added to the pagination URLs generated by the class.  These
	 * may be useful for filters and extra values when dealing with lists and GET requests.
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected $_additionalUrlParams = array();


	/**
	 * Return the rationalised offset for a row with a given index.
	 *
	 * @param   integer  $index  The row index
	 *
	 * @return  integer  Rationalised offset for a row with a given index.
	 *
	 * @since   11.1
	 */
	public function getRowOffset($index)
	{
		return $index + 1 + $this->limitstart;
	}

	/**
	 * Return the pagination data object, only creating it if it doesn't already exist.
	 *
	 * @return  object   Pagination data object.
	 *
	 * @since   11.1
	 */
	public function getData()
	{
		static $data;
		if (!is_object($data))
		{
			$data = $this->_buildDataObject();
		}
		return $data;
	}

	/**
	 * Create and return the pagination pages counter string, ie. Page 2 of 4.
	 *
	 * @return  string   Pagination pages counter string.
	 *
	 * @since   11.1
	 */
	public function getPagesCounter()
	{
		// Initialise variables.
		$html = null;
		if ($this->get('pages.total') > 1)
		{
			$html .= vmText::sprintf('JLIB_HTML_PAGE_CURRENT_OF_TOTAL', $this->get('pages.current'), $this->get('pages.total'));
		}
		return $html;
	}

	/**
	 * Create and return the pagination result set counter string, e.g. Results 1-10 of 42
	 *
	 * @return  string   Pagination result set counter string.
	 *
	 * @since   11.1
	 */
	public function getResultsCounter()
	{
		// Initialise variables.
		$html = null;
		$fromResult = $this->limitstart + 1;

		// If the limit is reached before the end of the list.
		if ($this->limitstart + $this->limit < $this->total)
		{
			$toResult = $this->limitstart + $this->limit;
		}
		else
		{
			$toResult = $this->total;
		}

		// If there are results found.
		if ($this->total > 0)
		{
			$msg = vmText::sprintf('JLIB_HTML_RESULTS_OF', $fromResult, $toResult, $this->total);
			$html .= "\n" . $msg;
		}
		else
		{
			$html .= "\n" . vmText::_('JLIB_HTML_NO_RECORDS_FOUND');
		}

		return $html;
	}

	/**
	 * Create and return the pagination page list string, ie. Previous, Next, 1 2 3 ... x.
	 *
	 * @return  string  Pagination page list string.
	 *
	 * @since   11.1
	 */
	public function getPagesLinks()
	{
		$app = JFactory::getApplication();

		// Build the page navigation list.
		$data = $this->_buildDataObject();

		$list = array();
		$list['prefix'] = $this->prefix;

		$itemOverride = false;
		$listOverride = false;

		$chromePath = VMPATH_THEMES . '/' . $app->getTemplate() . '/html/pagination.php';
		if (file_exists($chromePath))
		{
			include_once $chromePath;
			if (function_exists('pagination_item_active') && function_exists('pagination_item_inactive'))
			{
				$itemOverride = true;
			}
			if (function_exists('pagination_list_render'))
			{
				$listOverride = true;
			}
		}

		// Build the select list
		if ($data->all->base !== null)
		{
			$list['all']['active'] = true;
			$list['all']['data'] = ($itemOverride) ? pagination_item_active($data->all) : $this->_item_active($data->all);
		}
		else
		{
			$list['all']['active'] = false;
			$list['all']['data'] = ($itemOverride) ? pagination_item_inactive($data->all) : $this->_item_inactive($data->all);
		}

		if ($data->start->base !== null)
		{
			$list['start']['active'] = true;
			$list['start']['data'] = ($itemOverride) ? pagination_item_active($data->start) : $this->_item_active($data->start);
		}
		else
		{
			$list['start']['active'] = false;
			$list['start']['data'] = ($itemOverride) ? pagination_item_inactive($data->start) : $this->_item_inactive($data->start);
		}
		if ($data->previous->base !== null)
		{
			$list['previous']['active'] = true;
			$list['previous']['data'] = ($itemOverride) ? pagination_item_active($data->previous) : $this->_item_active($data->previous);
		}
		else
		{
			$list['previous']['active'] = false;
			$list['previous']['data'] = ($itemOverride) ? pagination_item_inactive($data->previous) : $this->_item_inactive($data->previous);
		}

		$list['pages'] = array(); //make sure it exists
		foreach ($data->pages as $i => $page)
		{
			if ($page->base !== null)
			{
				$list['pages'][$i]['active'] = true;
				$list['pages'][$i]['data'] = ($itemOverride) ? pagination_item_active($page) : $this->_item_active($page);
			}
			else
			{
				$list['pages'][$i]['active'] = false;
				$list['pages'][$i]['data'] = ($itemOverride) ? pagination_item_inactive($page) : $this->_item_inactive($page);
			}
		}

		if ($data->next->base !== null)
		{
			$list['next']['active'] = true;
			$list['next']['data'] = ($itemOverride) ? pagination_item_active($data->next) : $this->_item_active($data->next);
		}
		else
		{
			$list['next']['active'] = false;
			$list['next']['data'] = ($itemOverride) ? pagination_item_inactive($data->next) : $this->_item_inactive($data->next);
		}

		if ($data->end->base !== null)
		{
			$list['end']['active'] = true;
			$list['end']['data'] = ($itemOverride) ? pagination_item_active($data->end) : $this->_item_active($data->end);
		}
		else
		{
			$list['end']['active'] = false;
			$list['end']['data'] = ($itemOverride) ? pagination_item_inactive($data->end) : $this->_item_inactive($data->end);
		}

		if ($this->total > $this->limit)
		{
			return ($listOverride) ? pagination_list_render($list) : $this->_list_render($list);
		}
		else
		{
			return '';
		}
	}

	/**
	 * Return the pagination footer.
	 *
	 * @return  string   Pagination footer.
	 *
	 * @since   11.1
	 */
	public function getListFooter($lbox=true)
	{
		$app = JFactory::getApplication();

		$list = array();
		$list['prefix'] = $this->prefix;
		$list['limit'] = $this->limit;
		$list['limitstart'] = $this->limitstart;
		$list['total'] = $this->total;
		$list['limitfield'] = $lbox? $this->getLimitBox():'';
		$list['pagescounter'] = $this->getPagesCounter();
		$list['pageslinks'] = $this->getPagesLinks();

		$chromePath = VMPATH_THEMES . '/' . $app->getTemplate() . '/html/pagination.php';
		if (file_exists($chromePath))
		{
			include_once $chromePath;
			if (function_exists('pagination_list_footer'))
			{
				return pagination_list_footer($list);
			}
		}
		return $this->_list_footer($list);
	}

	/**
	 * Return the icon to move an item UP.
	 *
	 * @param   integer  $i          The row index.
	 * @param   boolean  $condition  True to show the icon.
	 * @param   string   $task       The task to fire.
	 * @param   string   $alt        The image alternative text string.
	 * @param   boolean  $enabled    An optional setting for access control on the action.
	 * @param   string   $checkbox   An optional prefix for checkboxes.
	 *
	 * @return  string   Either the icon to move an item up or a space.
	 *
	 * @since   11.1
	 */
	public function orderUpIcon($i, $condition = true, $task = 'orderup', $alt = 'JLIB_HTML_MOVE_UP', $enabled = true, $checkbox = 'cb')
	{
		if (($i > 0 || ($i + $this->limitstart > 0)) && $condition)
		{
			return JHtml::_('jgrid.orderUp', $i, $task, '', $alt, $enabled, $checkbox);
		}
		else
		{
			return '&#160;';
		}
	}

	/**
	 * Return the icon to move an item DOWN.
	 *
	 * @param   integer  $i          The row index.
	 * @param   integer  $n          The number of items in the list.
	 * @param   boolean  $condition  True to show the icon.
	 * @param   string   $task       The task to fire.
	 * @param   string   $alt        The image alternative text string.
	 * @param   boolean  $enabled    An optional setting for access control on the action.
	 * @param   string   $checkbox   An optional prefix for checkboxes.
	 *
	 * @return  string   Either the icon to move an item down or a space.
	 *
	 * @since   11.1
	 */
	public function orderDownIcon($i, $n, $condition = true, $task = 'orderdown', $alt = 'JLIB_HTML_MOVE_DOWN', $enabled = true, $checkbox = 'cb')
	{
		if (($i < $n - 1 || $i + $this->limitstart < $this->total - 1) && $condition)
		{
			return JHtml::_('jgrid.orderDown', $i, $task, '', $alt, $enabled, $checkbox);
		}
		else
		{
			return '&#160;';
		}
	}

	/**
	 * Create the HTML for a list footer
	 *
	 * @param   array  $list  Pagination list data structure.
	 *
	 * @return  string  HTML for a list footer
	 *
	 * @since   11.1
	 */
	protected function _list_footer($list)
	{
		$html = "<div class=\"list-footer pagination pagination-toolbar clearfix\">\n";

		$html .= "\n<div class=\"limit\">" . vmText::_('JGLOBAL_DISPLAY_NUM') . $list['limitfield'] . "</div>";
		$html .= $list['pageslinks'];
		$html .= "\n<div class=\"counter\">" . $list['pagescounter'] . "</div>";

		$html .= "\n<input type=\"hidden\" name=\"" . $list['prefix'] . "limitstart\" value=\"" . $list['limitstart'] . "\" />";
		$html .= "\n</div>";

		return $html;
	}

	/**
	 * Create the html for a list footer
	 *
	 * @param   array  $list  Pagination list data structure.
	 *
	 * @return  string  HTML for a list start, previous, next,end
	 *
	 * @since   11.1
	 */
	protected function _list_render($list)
	{
		// Reverse output rendering for right-to-left display.
		$html = '<ul>';
		$html .= '<li class="pagination-start">' . $list['start']['data'] . '</li>';
		$html .= '<li class="pagination-prev">' . $list['previous']['data'] . '</li>';
		foreach ($list['pages'] as $page)
		{
			$html .= '<li>' . $page['data'] . '</li>';
		}
		$html .= '<li class="pagination-next">' . $list['next']['data'] . '</li>';
		$html .= '<li class="pagination-end">' . $list['end']['data'] . '</li>';
		$html .= '</ul>';

		return $html;
	}

	/**
	 * Method to create an active pagination link to the item
	 *
	 * @param   vPaginationObject  &$item  The object with which to make an active link.
	 *
	 * @return   string  HTML link
	 *
	 * @since    11.1
	 */
	protected function _item_active(&$item)
	{
		$app = JFactory::getApplication();
		if ($app->isAdmin())
		{
			if ($item->base > 0)
			{
				return "<a title=\"" . $item->text . "\" onclick=\"document.adminForm." . $this->prefix . "limitstart.value=" . $item->base
				. "; Joomla.submitform();return false;\">" . $item->text . "</a>";
			}
			else
			{
				return "<a title=\"" . $item->text . "\" onclick=\"document.adminForm." . $this->prefix
				. "limitstart.value=0; Joomla.submitform();return false;\">" . $item->text . "</a>";
			}
		}
		else
		{

			$rel = '';
			if($item->text==vmText::_('JNEXT')){
				$rel = 'rel="next"';
			} else if($item->text==vmText::_('JPREV')){
				$rel = 'rel="prev"';
			}
			return '<a '.$rel.' title="' . $item->text . '" href="' . $item->link . '" class="pagenav">' . $item->text . '</a>';
		}
	}

	/**
	 * Method to create an inactive pagination string
	 *
	 * @param   object  &$item  The item to be processed
	 *
	 * @return  string
	 *
	 * @since   11.1
	 */
	protected function _item_inactive(&$item)
	{
		$app = JFactory::getApplication();
		if ($app->isAdmin())
		{
			return "<span>" . $item->text . "</span>";
		}
		else
		{
			return "<span class=\"pagenav\">" . $item->text . "</span>";
		}
	}

	/**
	 * Create and return the pagination data object.
	 *
	 * @return  object  Pagination data object.
	 *
	 * @since   11.1
	 */
	protected function _buildDataObject()
	{
		// Initialise variables.
		$data = new stdClass;

		// Build the additional URL parameters string.
		$params = '';
		if (!empty($this->_additionalUrlParams))
		{
			foreach ($this->_additionalUrlParams as $key => $value)
			{
				$params .= '&' . $key . '=' . $value;
			}
		}

		$data->all = new vPaginationObject(vmText::_('JLIB_HTML_VIEW_ALL'), $this->prefix);
		if (!$this->_viewall)
		{
			$data->all->base = '0';
			$data->all->link = JRoute::_($params . '&' . $this->prefix . 'limitstart=');
		}

		// Set the start and previous data objects.
		$data->start = new vPaginationObject(vmText::_('JLIB_HTML_START'), $this->prefix);
		$data->previous = new vPaginationObject(vmText::_('JPREV'), $this->prefix);

		if ($this->get('pages.current') > 1)
		{
			$page = ($this->get('pages.current') - 2) * $this->limit;

			// Set the empty for removal from route
			//$page = $page == 0 ? '' : $page;

			$data->start->base = '0';
			$data->start->link = JRoute::_($params . '&' . $this->prefix . 'limitstart=0');
			$data->previous->base = $page;
			$data->previous->link = JRoute::_($params . '&' . $this->prefix . 'limitstart=' . $page);
		}

		// Set the next and end data objects.
		$data->next = new vPaginationObject(vmText::_('JNEXT'), $this->prefix);
		$data->end = new vPaginationObject(vmText::_('JLIB_HTML_END'), $this->prefix);

		if ($this->get('pages.current') < $this->get('pages.total'))
		{
			$next = $this->get('pages.current') * $this->limit;
			$end = ($this->get('pages.total') - 1) * $this->limit;

			$data->next->base = $next;
			$data->next->link = JRoute::_($params . '&' . $this->prefix . 'limitstart=' . $next);
			$data->end->base = $end;
			$data->end->link = JRoute::_($params . '&' . $this->prefix . 'limitstart=' . $end);
		}

		$data->pages = array();
		$stop = $this->get('pages.stop');
		for ($i = $this->get('pages.start'); $i <= $stop; $i++)
		{
			$offset = ($i - 1) * $this->limit;
			// Set the empty for removal from route
			//$offset = $offset == 0 ? '' : $offset;

			$data->pages[$i] = new vPaginationObject($i, $this->prefix);
			if ($i != $this->get('pages.current') || $this->_viewall)
			{
				$data->pages[$i]->base = $offset;
				$data->pages[$i]->link = JRoute::_($params . '&' . $this->prefix . 'limitstart=' . $offset);
			}
		}
		return $data;
	}
}

/**
 * Pagination object representing a particular item in the pagination lists.
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @package     Joomla.Platform
 * @subpackage  HTML
 * @since       11.1
 */
class vPaginationObject extends JObject
{
	/**
	 * @var    string  The link text.
	 * @since  11.1
	 */
	public $text;

	/**
	 * @var    integer  The number of rows as a base offset.
	 * @since  11.1
	 */
	public $base;

	/**
	 * @var    string  The link URL.
	 * @since  11.1
	 */
	public $link;

	/**
	 * @var    integer  The prefix used for request variables.
	 * @since  11.1
	 */
	public $prefix;

	/**
	 * Class constructor.
	 *
	 * @param   string   $text    The link text.
	 * @param   integer  $prefix  The prefix used for request variables.
	 * @param   integer  $base    The number of rows as a base offset.
	 * @param   string   $link    The link URL.
	 *
	 * @since   11.1
	 */
	public function __construct($text, $prefix = '', $base = null, $link = null)
	{
		$this->text = $text;
		$this->prefix = $prefix;
		$this->base = $base;
		$this->link = $link;
	}
}

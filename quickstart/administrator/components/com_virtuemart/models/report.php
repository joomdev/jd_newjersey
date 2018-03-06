<?php
if (!defined ('_JEXEC')) {
	die('Direct Access to ' . basename (__FILE__) . ' is not allowed.');
}

/**
 *
 * Report Model
 *
 * @author Max Milbers, Wicksj
 * @version $Id: report.php 9609 2017-07-27 17:45:27Z Milbo $
 * @package VirtueMart
 * @subpackage Report
 * @copyright Copyright (C) 2011 - 2014VirtueMart Team - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.org
 */

if (!class_exists ('VmModel')) {
	require(VMPATH_ADMIN . DS . 'helpers' . DS . 'vmmodel.php');
}

class VirtuemartModelReport extends VmModel {

	var $from_period = '';
	var $until_period = '';
	private $date_presets = NULL;
	private $period = NULL;

	function __construct () {

		parent::__construct ();
		$this->setMainTable ('orders');

		$this->setDatePresets ();

		$app = JFactory::getApplication ();
		$this->period = $app->getUserStateFromRequest ('com_virtuemart.revenue.period', 'period', 'last30', 'string');

		$task = vRequest::getCmd('task',false);
		if (empty($this->period) or $task == 'setPeriod' ) { //$this->period != 'none') {
			$this->setPeriodByPreset ();
		}
		else {
			$this->setPeriod ();
			$this->period = 'none';
			$app->setUserState('com_virtuemart.revenue.period','none');
		}

		$this->removevalidOrderingFieldName ('virtuemart_order_id');
		$this->addvalidOrderingFieldName (array('product_quantity', 'o.virtuemart_order_id'));
		$this->_selectedOrdering = 'created_on';

	}


	function correctTimeOffset(&$inputDate){

		$config = JFactory::getConfig();
		$this->siteOffset = $config->get('offset');

		//This is important to remove already set Timezones
		$date = new JDate($inputDate);
		$inputDate = $date->format('Y-m-d',true);

		$date = new JDate($inputDate);

		$date->setTimezone($this->siteTimezone);
		$inputDate = $date->format('Y-m-d H:i:s',true);
	}

	/*
	* Set Start & end Date
	*/
	function setPeriod () {

		$this->from_period = vRequest::getVar ('from_period', $this->date_presets['last30']['from']);
		$this->until_period = vRequest::getVar ('until_period', $this->date_presets['last30']['until']);

		$config = JFactory::getConfig();
		$siteOffset = $config->get('offset');
		$this->siteTimezone = new DateTimeZone($siteOffset);
		$this->correctTimeOffset($this->from_period);
		$this->correctTimeOffset($this->until_period);
		//vmdebug('setPeriod',$this->siteTimezone,$this->until_period );

	}

	/*
	* Set Start & end Date if Var peroid
	*/
	function setPeriodByPreset () {

		$this->from_period = $this->date_presets[$this->period]['from'];
		$this->until_period = $this->date_presets[$this->period]['until'];

		$config = JFactory::getConfig();
		$siteOffset = $config->get('offset');
		$this->siteTimezone = new DateTimeZone($siteOffset);

		$this->correctTimeOffset($this->from_period);
		$this->correctTimeOffset($this->until_period);

	}

	function  getItemsByRevenue ($revenue) {

		$q = 'select SUM(`product_quantity`) as product_quantity from `#__virtuemart_order_items` as i LEFT JOIN #__virtuemart_orders as o ON o.virtuemart_order_id=i.virtuemart_order_id ' . $this->whereItem . ' CAST(' . $this->intervals . ' AS DATE) = CAST("' . $revenue['intervals'] . '" AS DATE) ';
		$db = JFactory::getDBO();
		$db->setQuery ($q);

		return $db->loadResult ();

	}

	/**
	 * Retrieve a list of report items from the database.
	 *
	 * @param string $noLimit True if no record count limit is used, false otherwise
	 * @return object List of order objects
	 */
	function getRevenue ($cache = 0) {

		if(!vmAccess::manager('report')){
			return false;
		}

		$vendorId = vmAccess::isSuperVendor();
		if(vmAccess::manager('managevendors')){
			$vendorId = vRequest::getInt('virtuemart_vendor_id',$vendorId);
		}

		$orderstates = vRequest::getVar ('order_status_code', array('C','S'));
		$intervals = vRequest::getCmd ('intervals', 'day');
		$filterorders = vRequest::getVar ('filter_order', 'intervals');
		$orderdir = (vRequest::getCmd ('filter_order_Dir', NULL) == 'desc') ? 'desc' : '';
		$virtuemart_product_id = vRequest::getInt ('virtuemart_product_id', FALSE);

		if($cache){
			$c = VmConfig::getCache ('com_virtuemart_revenue','callback',null,true);
			$c->setCaching (1);
			$c->setLifeTime($cache);

			return $c->call (array('VirtuemartModelReport', 'getRevenueDiag'),$vendorId,$orderstates,$intervals,$filterorders,$orderdir,$virtuemart_product_id,$this->from_period,$this->until_period);
		} else {
			return $this->getRevenueSortListOrderQuery ($vendorId,$orderstates,$intervals,$filterorders,$orderdir,$virtuemart_product_id);
		}

	}

	static public function getRevenueDiag ($vendorId,$orderstates,$intervals,$filterorders,$orderdir,$virtuemart_product_id,$from_period,$until_period) {
		$reportM = VmModel::getModel('report');
		$report = $reportM->getRevenueSortListOrderQuery($vendorId,$orderstates,$intervals,$filterorders,$orderdir,$virtuemart_product_id);

		$rows = count( $report );
		$intervalTitle='day';
		$addDateInfo = false;

		$i = 0;
		$reports=array_reverse($report);
		$reports_date=array();
		foreach($reports as $_report) {
			$reports_date[$_report['intervals']]=$_report;
		}

		$begin = new DateTime($reportM->from_period );
		$end = new DateTime( $reportM->until_period );


		$js="
  google.load(\"visualization\", \"1\", {packages:[\"corechart\"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['".vmText::_('COM_VIRTUEMART_DAY')."', '".vmText::_('COM_VIRTUEMART_REPORT_BASIC_ORDERS')."', '".vmText::_('COM_VIRTUEMART_REPORT_BASIC_TOTAL_ITEMS')."', '".vmText::_('COM_VIRTUEMART_REPORT_BASIC_REVENUE_NETTO')."'],";

		$interval = DateInterval::createFromDateString('1 day');
		$period = new DatePeriod($begin, $interval, $end);
		foreach ( $period as $dt ) {
			$day=$dt->format('Y-m-d');
			if (array_key_exists($day, $reports_date)) {
				$r = $reports_date[$day];
			} else {
				$r=array('intervals'=>$day, 'count_order_id'=>0, 'product_quantity'=>0, 'order_subtotal_netto'=>0);
			}
			$js .= " ['" . $r['intervals'] . "', " . $r['count_order_id'] . "," . $r['product_quantity'] .  "," . $r['order_subtotal_netto'] . "],";
		}

		$js = substr($js,0,-1);
		$js .= "  ]);";
		$js .="
        var options = {
          title: '". vmText::sprintf('COM_VIRTUEMART_REPORT_TITLE', vmJsApi::date( $reportM->from_period, 'LC',true) , vmJsApi::date( $reportM->until_period, 'LC',true) )."',
            series: {0: {targetAxisIndex:0},
                   1:{targetAxisIndex:0},
                   2:{targetAxisIndex:1},
                  },
                  colors: [\"#00A1DF\", \"#A4CA37\",\"#E66A0A\"],
        };

        var chart = new google.visualization.LineChart(document.getElementById('vm_stats_chart'));

        chart.draw(data, options);
      }
";
		return array('report'=>$report,'js'=>$js);
	}

	function getRevenueSortListOrderQuery ($vendorId,$orderstates,$intervals,$filterorders,$orderdir,$virtuemart_product_id) {

		$selectFields = array();
		$mainTable = '';
		$joinTables = array();
		$joinedTables = '';
		$where = array();

		$until_period = $this->until_period;
		// group always by intervals (day,week, ... or ID) and set grouping and default ordering


		$date = new JDate($this->until_period);
		//$twenty4h = new DateInterval('PT24H');
		$d24_1 = new DateInterval('PT86399S');
		$date->add($d24_1);
		$until_period = $date->format('Y-m-d H:i:s',true);

		$this->intervals = 'o.created_on';
		$groupBy = 'GROUP BY intervals ';
		$weekMode = VmConfig::get('sql_weekmode',false);//'3';	//2 USA, 3 german,

		switch ($intervals) {

			case 'product_s':
				$selectFields[] = '`order_item_name`';
				$selectFields[] = '`virtuemart_product_id`';
				$this->intervals = 'DATE( o.created_on )';
				$groupBy = 'GROUP BY `virtuemart_product_id` ';
				break;
			case 'orders':
				$selectFields[] = 'o.virtuemart_order_id';
				$selectFields[] = 'o.`order_number`';
				$groupBy = 'GROUP BY o.`virtuemart_order_id` ';
				break;
			case 'day':
				$this->intervals = 'DATE( o.created_on )';
				break;
			case 'week':
				if($weekMode===false){
					$this->intervals = 'WEEK( o.created_on )';
				} else {
					$this->intervals = 'WEEK( o.created_on, '.$weekMode.' )';
				}

				break;
			case 'month':
				$this->intervals = 'MONTH( o.created_on )';
				break;
			case 'year':
				$this->intervals = 'YEAR( o.created_on )';
				break;
			default:
				break;
		}

		$selectFields['intervals'] = $this->intervals . ' AS intervals, o.`created_on` AS created_on';

		//$multix = VmConfig::get('multix','none');

		$selectFields[] = 'SUM( (product_discountedPriceWithoutTax * product_quantity)) as order_subtotal_netto';

		$selectFields[] = 'SUM(product_subtotal_with_tax) as order_subtotal_brutto';
		$selectFields[] = 'SUM(coupon_discount) as coupon_discount';

		$this->dates = ' o.created_on BETWEEN "' . $this->from_period . '" AND "' . $until_period . '" ';

		$statusList = array();
		// Filter by status
		if ($orderstates) {
			$query = 'SELECT `order_status_code`
				FROM `#__virtuemart_orderstates`
				WHERE published=1 ';
			$db = JFactory::getDBO();
			$db->setQuery ($query);
			$list = $db->loadColumn ();
			foreach ($orderstates as $val) {
				if (in_array ($val, $list)) {
					$statusList[] = '`i`.`order_status` = "' . $val . '"';
				}
			}
			if ($statusList) {
				$where[] = '(' . implode (' OR ', $statusList) . ')';
			}
		}
		//getRevenue
		// select wich table to order sum ordered

		switch ($filterorders) {

			case 'o.virtuemart_order_id':
				$orderBy = ' ORDER BY count_order_id ' . $orderdir;
				$groupBy = 'GROUP BY intervals ';
				break;
			case 'product_quantity'   :
				// GROUP BY product_quantity, intervals
				// ORDER BY `product_quantity` ASC
				// TODO grouping and ordering
				$orderBy = ' ORDER BY product_quantity ' . $orderdir;
				$groupBy = 'GROUP BY intervals ';

				//$selectFields['intervals'] = $this->intervals.' AS intervals, i.`created_on` ';
				break;
			case 'o.order_subtotal'   :
				$orderBy = ' ORDER BY order_subtotal';
				break;
				//getOrderItemsSumGrouped($this->intervals , $filterorders);
				break;
			default:
				// invidual grouping
				$orderBy = $this->_getOrdering ();
				vmdebug ('default case', $orderBy);
				//$this->intervals= '`o`.`created_on`';
// 				$orderBy = ' ORDER BY '.$filterorders.' '.$orderdir;
				break;
		}

		$selectFields[] = 'COUNT(DISTINCT o.virtuemart_order_id) as count_order_id';
		$selectFields[] = 'SUM(product_quantity) as product_quantity';

		$mainTable = '`#__virtuemart_order_items` as i';

		$joinTables['orders'] = ' LEFT JOIN `#__virtuemart_orders` as o ON o.virtuemart_order_id=i.virtuemart_order_id ';

		if (count ($selectFields) > 0) {

			$select = implode (', ', $selectFields) . ' FROM ' . $mainTable;
			//$selectFindRows = 'SELECT COUNT(*) FROM '.$mainTable;
			if (count ($joinTables) > 0) {
				foreach ($joinTables as $table) {
					$joinedTables .= $table;
				}
			}
		}
		else {
			vmError ('No select fields given in getRevenueSortListOrderQuery', 'No select fields given');
			return FALSE;
		}


		if ($virtuemart_product_id) {
			$where[] = 'i.virtuemart_product_id = "' . $virtuemart_product_id . '" ';
		}

		if (VmConfig::get ('multix', 'none') != 'none') {
			if ($vendorId != 0) {
				$where[] = 'i.virtuemart_vendor_id = "' . $vendorId . '" ';
			}
		}
		if (count ($where) > 0) {
			$this->whereItem = ' WHERE ' . implode (' AND ', $where) . ' AND ';
		}
		else {
			$this->whereItem = ' WHERE ';
		}

		/* WHERE differences with orders and items from orders are only date periods and ordering */
		$whereString = $this->whereItem . $this->dates;
		return $this->exeSortSearchListQuery (1, $select, $joinedTables, $whereString, $groupBy, $orderBy);

	}




	/**
	 * Retrieve a list of report items from the database.
	 * DONT know why this ???? Patrick Kohl
	 *
	 * @author Wicksj
	 * @param string $noLimit True if no record count limit is used, false otherwise
	 * @return object List of order objects
	 */
	function getOrderItems ($noLimit = FALSE) {

		// $db = JFactory::getDBO();

		$query = "SELECT `product_name`, `product_sku`, ";
		$query .= "i.created_on as order_date, ";
		$query .= "SUM(product_quantity) as product_quantity ";
		$query .= "FROM #__virtuemart_order_items i, #__virtuemart_orders o, #__virtuemart_products p ";
		$query .= "WHERE i.created_on BETWEEN '{$this->start_date} 00:00:00' AND '{$this->until_period} 23:59:59' ";
		$query .= "AND o.virtuemart_order_id=i.virtuemart_order_id ";
		$query .= "AND i.virtuemart_product_id=p.virtuemart_product_id ";
		$query .= "GROUP BY product_sku, product_name, order_date ";
		$query .= " ORDER BY order_date, product_name ASC";

		if ($noLimit) {
			$this->_data = $this->_getList ($query);
		}
		else {
			$this->_data = $this->_getList ($query, $this->getState ('limitstart'), $this->getState ('limit'));
		}
		if (!$this->_total) {
			$this->_total = $this->_getListCount ($query);
		}

		return $this->_data;
	}


	public function setDatePresets () {

		if ($this->date_presets) {
			return $this->date_presets;
		}
		// set date presets
		$curDate = JFactory::getDate ();
		$curDate = $curDate->toUnix ();
		$curDate = mktime (0, 0, 0, date ('m', $curDate), date ('d', $curDate), date ('Y', $curDate));
		$monday = (date ('w', $curDate) == 1) ? $curDate : strtotime ('last Monday', $curDate);
		$this->date_presets['last90'] = array(
			'name'  => vmText::_ ('COM_VIRTUEMART_REPORT_PERIOD_LAST90'),
			'from'  => date ('Y-m-d', strtotime ('-89 day', $curDate)),
			'until' => date ('Y-m-d', $curDate));
		$this->date_presets['last60'] = array(
			'name'  => vmText::_ ('COM_VIRTUEMART_REPORT_PERIOD_LAST60'),
			'from'  => date ('Y-m-d', strtotime ('-59 day', $curDate)),
			'until' => date ('Y-m-d', $curDate));
		$this->date_presets['last30'] = array(
			'name'  => vmText::_ ('COM_VIRTUEMART_REPORT_PERIOD_LAST30'),
			'from'  => date ('Y-m-d', strtotime ('-29 day', $curDate)),
			'until' => date ('Y-m-d', $curDate));
		$this->date_presets['today'] = array(
			'name'  => vmText::_ ('COM_VIRTUEMART_REPORT_PERIOD_TODAY'),
			'from'  => date ('Y-m-d', $curDate),
			'until' => date ('Y-m-d', $curDate));
		$this->date_presets['this-week'] = array(
			'name'  => vmText::_ ('COM_VIRTUEMART_REPORT_PERIOD_THIS_WEEK'),
			'from'  => date ('Y-m-d', $monday),
			'until' => date ('Y-m-d', strtotime ('+6 day', $monday)));
		$this->date_presets['this-month'] = array(
			'name'  => vmText::_ ('COM_VIRTUEMART_REPORT_PERIOD_THIS_MONTH'),
			'from'  => date ('Y-m-d', mktime (0, 0, 0, date ('n', $curDate), 1, date ('Y', $curDate))),
			'until' => date ('Y-m-d', mktime (0, 0, 0, date ('n', $curDate) + 1, 0, date ('Y', $curDate))));
		$this->date_presets['this-year'] = array(
			'name'  => vmText::_ ('COM_VIRTUEMART_REPORT_PERIOD_THIS_YEAR'),
			'from'  => date ('Y-m-d', mktime (0, 0, 0, 1, 1, date ('Y', $curDate))),
			'until' => date ('Y-m-d', mktime (0, 0, 0, 12, 31, date ('Y', $curDate))));

	}

	public function renderDateSelectList () {

		// simpledate select
		$select = '';
		$options = array(JHtml::_ ('select.option', 'none', '- ' . vmText::_ ('COM_VIRTUEMART_REPORT_SET_PERIOD') . ' -', 'text', 'value'));

		$app = JFactory::getApplication ();
		$select = $app->getUserStateFromRequest ('com_virtuemart.revenue.period', 'period', 'last30', 'string');

		foreach ($this->date_presets as $name => $value) {
			$options[] = JHtml::_ ('select.option', $name, vmText::_ ($value['name']), 'text', 'value');
		}
		$listHTML = JHtml::_ ('select.genericlist', $options, 'period', 'size="7" class="inputbox" onchange="this.form.task.value=\'setPeriod\';this.form.submit();" ', 'text', 'value', $select);
		//$listHTML = JHtml::_ ('select.genericlist', $options, 'period', 'size="7" class="inputbox" ', 'text', 'value', $select);

		return $listHTML;
	}

	public function renderIntervalsList () {

		$intervals = vRequest::getCmd ('intervals', 'day');

		$options = array();
		$options[] = JHtml::_ ('select.option', vmText::_ ('COM_VIRTUEMART_PRODUCT_S'), 'product_s');
		$options[] = JHtml::_ ('select.option', vmText::_ ('COM_VIRTUEMART_ORDERS'), 'orders');
		$options[] = JHtml::_ ('select.option', vmText::_ ('COM_VIRTUEMART_REPORT_INTERVAL_GROUP_DAILY'), 'day');
		$options[] = JHtml::_ ('select.option', vmText::_ ('COM_VIRTUEMART_REPORT_INTERVAL_GROUP_WEEKLY'), 'week');
		$options[] = JHtml::_ ('select.option', vmText::_ ('COM_VIRTUEMART_REPORT_INTERVAL_GROUP_MONTHLY'), 'month');
		$options[] = JHtml::_ ('select.option', vmText::_ ('COM_VIRTUEMART_REPORT_INTERVAL_GROUP_YEARLY'), 'year');
		//$listHTML = JHtml::_ ('select.genericlist', $options, 'intervals', 'class="inputbox" onchange="this.form.submit();" size="5"', 'text', 'value', $intervals);
		$listHTML = JHtml::_ ('select.genericlist', $options, 'intervals', 'class="inputbox" size="6"', 'text', 'value', $intervals);
		return $listHTML;
	}

	public function updateOrderItems () {
		$q = 'UPDATE #__virtuemart_order_items SET `product_discountedPriceWithoutTax`=( (IF(product_final_price is NULL, 0.00,product_final_price)   - IF(product_tax is NULL, 0.00,product_tax)  )) WHERE `product_discountedPriceWithoutTax` IS NULL';
		$db = JFactory::getDBO();
		$db->setQuery($q);
		$db->execute();
	}

}

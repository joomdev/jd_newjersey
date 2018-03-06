<?php
/**
* vObject derived from JObject Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
*
* @package	VirtueMart
* @subpackage Helpers
* @author Max Milbers
* @copyright Copyright (c) 2014 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.net
*/

class vObject {

	public function __toString() {
		return get_class($this);
	}

	public function get($prop, $def = null) {
		if (isset($this->$prop)) {
			return $this->$prop;
		}
		return $def;
	}

	public function set($prop, $value = null) {
		$prev = isset($this->$prop) ? $this->$prop : null;
		$this->$prop = $value;
		return $prev;
	}

	public function setProperties($props) {

		if (is_array($props) || is_object($props)) {

			foreach ( $props as $k => $v) {
				$this->$k = $v;
			}
			return true;
		} else {
			return false;
		}
	}
}

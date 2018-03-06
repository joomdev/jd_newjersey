<?php
/**
 *
 * Paypal payment plugin
 *
 * @author Valerie
 * @version $Id: paypal.php 7217 2013-09-18 13:42:54Z alatak $
 * @package VirtueMart
 * @subpackage payment
 * Copyright (C) 2004 - 2017 Virtuemart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */



if ($viewData['isMobile'] ) {
    $width="100%";
    $height="100%";
} else {
	$width="100%";
	$height="600px";
}
 ?>
<iframe name="hss_iframe" width="<?php echo $width ?>" height="<?php echo $height ?>"  src="<?php echo $viewData['url'] ?>"></iframe>

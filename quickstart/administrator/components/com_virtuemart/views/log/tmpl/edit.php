<?php
/**
 *
 * Description
 *
 * @package	VirtueMart
 * @subpackage OrderStatus
 * @author Oscar van Eijk
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: edit.php 5804 2012-04-04 18:28:45Z Milbo $
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
AdminUIHelper::startAdminArea($this);

?>
<form name='adminForm' id="adminForm">
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_virtuemart" />
	<input type="hidden" name="view" value="log" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
<pre class="code">
	<ol class="logline">
	<?php
	foreach($this->fileContentByLine as $line)
echo "<li>".str_replace(array("<pre>","</pre>"),"",$line)."</li>";
?>


	<pre>
<?php AdminUIHelper::endAdminArea(); ?>

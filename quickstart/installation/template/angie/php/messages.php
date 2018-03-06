<?php
/**
 * @package angi4j
 * @copyright Copyright (C) 2009-2017 Nicholas K. Dionysopoulos. All rights reserved.
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

foreach(array('error','warning','success','info') as $type):
	$messages = AApplication::getInstance()->getMessageQueueFor($type);
	$class = ($type == 'warning') ? '' : "alert-$type";
	if(!empty($messages)):
?>
<div class="alert <?php echo $class ?>">
<?php foreach($messages as $message):?>
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	<p><?php echo $message ?></p>
<?php endforeach; ?>
</div>
<?php
	endif;
endforeach;
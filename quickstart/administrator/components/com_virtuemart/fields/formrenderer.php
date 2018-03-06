<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
/**
 *
 * @version $Id: formrenderer.php 7256 2013-09-29 18:42:44Z Milbo $
 * @package VirtueMart
 * @subpackage core
 * @copyright Copyright (C) 2014 VirtueMart Team - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */

if (JVM_VERSION < 3){
	$control_field_class="width100 floatleft control-field";
	$control_group_class="width100 control-group";
	$control_label_class="width25 floatleft control-label";
	$control_input_class="width74 floatright control-input";
} else {
	$control_field_class="control-field";
	$control_group_class="control-group";
	$control_label_class="control-label";
	$control_input_class="control-input";
}

if ($form) {
	$fieldSets = $form->getFieldsets();
	if (!empty($fieldSets)) {
		?>
		<?php
		foreach ($fieldSets as $name => $fieldSet) {
			?>
			<div class="<?php echo $control_field_class ?>">
				<?php
				$label = !empty($fieldSet->label) ? $fieldSet->label: '';

				if (!empty($label)) {
					$class = isset($fieldSet->class) && !empty($fieldSet->class) ? "class=\"".$fieldSet->class."\"" : '';
					?>
					<h3> <span<?php echo $class  ?>><?php echo vmText::_($label) ?></span></h3>
					<?php
					if (isset($fieldSet->description) && trim($fieldSet->description)) {
						echo '<p class="tip">' . $this->escape(vmText::_($fieldSet->description)) . '</p>';
					}
				}
				?>

				<?php $i=0; ?>
				<?php foreach ($form->getFieldset($name) as $field) { ?>
					<?php if (!$field->hidden) {
						?>
						<div class="<?php echo $control_group_class ?>">
							<div class="<?php echo $control_label_class ?>">
								<?php echo $field->label; ?>
							</div>
							<div class="<?php echo $control_input_class ?>">
								<?php echo $field->input; ?>
							</div>
						</div>
					<?php } ?>
				<?php } ?>
			</div>
		<?php
		}
	}
}

?>
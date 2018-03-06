<?php

/**
 *
 * Modify user form view, User info
 *
 * @package	VirtueMart
 * @subpackage User
 * @author Oscar van Eijk, Eugen Stranz
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: edit_address_userfields.php 9625 2017-08-17 12:49:57Z Milbo $
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Status Of Delimiter
$closeDelimiter = false;
$openTable = true;
$hiddenFields = '';

$i=0;
//When only one Delimiter exists, set it to begin of the array
//not an elegant solution, but works for the moment.
foreach($this->userFields['fields'] as $k=>$field){
	if($field['type'] == 'delimiter') {
	    $tmp = $field;
	    $pos = $k;
	    $i++;
	}
	if($i>1){
	    $tmp = false;
	    break;
	}
}

if($tmp){
    unset($this->userFields['fields'][$pos]);
    array_unshift($this->userFields['fields'],$tmp);
}

// Output: Userfields
foreach($this->userFields['fields'] as $field) {

	if($field['type'] == 'delimiter') {

		// For Every New Delimiter
		// We need to close the previous
		// table and delimiter
		if($closeDelimiter) { ?>
			</table>
		</fieldset>
		<?php
			$closeDelimiter = false;
		} else if(!$openTable){ ?>
            </table>
			<?php
		}

        ?>
        <fieldset>
        <legend class="userfields_info"><?php echo $field['title'] ?></legend>

        <?php
        $closeDelimiter = true;
        $openTable = true;

	} elseif ($field['hidden'] == true) {

		// We collect all hidden fields
		// and output them at the end
		$hiddenFields .= $field['formcode'] . "\n";

	} else {

		// If we have a new delimiter
		// we have to start a new table
		if($openTable) {
			$openTable = false;
			?>

			<table class="adminForm user-details">

		<?php
		}
		$descr = empty($field['description'])? $field['title']:$field['description'];
		// Output: Userfields
		?>
				<tr title="<?php echo strip_tags($descr) ?>">
					<td class="key"  >
						<label class="<?php echo $field['name'] ?>" for="<?php echo $field['name'] ?>_field">
							<?php echo $field['title'] . ($field['required'] ? ' <span class="asterisk">*</span>' : '') ?>
						</label>
					</td>
					<td>
						<?php echo $field['formcode'] ?>
					</td>
				</tr>
	<?php
	}

}

if($closeDelimiter) { ?>
    </table>
    </fieldset>
	<?php
	$closeDelimiter = false;
}

// At the end we have to close the current
// table and delimiter ?>

			</table>
		</fieldset>

<?php // Output: Hidden Fields
echo $hiddenFields
?>
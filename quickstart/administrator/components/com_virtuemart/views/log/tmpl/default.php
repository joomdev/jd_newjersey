<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage Log
* @author Valérie Isaksen
* @link https://virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: default.php 6307 2012-08-07 07:39:45Z alatak $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

AdminUIHelper::startAdminArea($this);
//$finfo = finfo_open(FILEINFO_MIME);
if(class_exists('finfo')){
	$finfo = new finfo(FILEINFO_MIME);
} else {
	vmInfo('The function finfo should be activated on the server');
	$finfo = false;
}

?>
<table class="adminlist table table-striped" cellspacing="0" cellpadding="0">
		<thead>
		<tr>
			<th>
				<?php echo vmText::_('COM_VIRTUEMART_LOG_FILENAME'); ?>
			</th>
			<th>
				<?php echo vmText::_('COM_VIRTUEMART_LOG_FILEINFO'); ?>
			</th>
			<th>
				<?php echo vmText::_('COM_VIRTUEMART_LOG_FILESIZE'); ?>
			</th>

		</tr>
		</thead>
		<?php
		$k = 0;
		if ($this->logFiles) {
			foreach ($this->logFiles as $logFile ) {
				$addLink=false;
				$fileSize = round(filesize($this->path.DS.$logFile)/1024.0,2);
				$fileInfo= $finfo?$finfo->file($this->path.DS.$logFile):0;
				$fileInfoMime=substr($fileInfo, 0 ,strlen("text/plain"));
				if (!$finfo or strcmp("text/plain", $fileInfoMime)==0) {
					$addLink=true;
				}
				?>
				<tr class="row<?php echo $k ; ?>">

					<td align="left">
						<?php if ($fileSize > 0 and $addLink) { ?>
							<a href="<?php echo JRoute::_('index.php?option=com_virtuemart&view=log&task=edit&logfile='.$logFile); ?>">
					<?php	}?>
						 <?php echo $logFile; ?>
						<?php if ($fileSize > 0) { ?>
						</a>
							<?php	}?>
					</td>
				<td align="left">
					<?php
					echo  $fileInfo; ?>

				</td>
					<td align="left">
<?php
						echo  $fileSize." ".vmText::_('COM_VIRTUEMART_LOG_KB'); ?>

					</td>

				</tr>
				<?php
				$k = 1 - $k;
			}
		}
		?>
	</table>

	<?php
	echo $this->addStandardHiddenToForm();
    AdminUIHelper::endAdminArea();
	?>

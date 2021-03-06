<?php
/**
 * Kunena Component
 * @package     Kunena.Template.Crypsis
 * @subpackage  Pages.User
 *
 * @copyright   (C) 2008 - 2018 Kunena Team. All rights reserved.
 * @license     https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link        https://www.kunena.org
 **/
defined('_JEXEC') or die;

$content = $this->execute('User/List');

$this->addBreadcrumb(
	JText::_('COM_KUNENA_USRL_USERLIST'),
	'index.php?option=com_kunena&view=user&layout=list'
);

echo $content;

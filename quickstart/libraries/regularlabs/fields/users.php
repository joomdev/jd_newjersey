<?php
/**
 * @package         Regular Labs Library
 * @version         17.10.24881
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2017 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

if ( ! is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php'))
{
	return;
}

require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';

class JFormFieldRL_Users extends \RegularLabs\Library\Field
{
	public $type = 'Users';

	protected function getInput()
	{
		$this->params = $this->element->attributes();

		if ( ! is_array($this->value))
		{
			$this->value = explode(',', $this->value);
		}

		$size     = (int) $this->get('size');
		$multiple = $this->get('multiple');

		return $this->selectListSimpleAjax(
			$this->type, $this->name, $this->value, $this->id,
			compact('size', 'multiple')
		);
	}

	function getAjaxRaw()
	{
		$input = JFactory::getApplication()->input;

		$options = $this->getUsers();

		$name     = $input->getString('name', $this->type);
		$id       = $input->get('id', strtolower($name));
		$value    = json_decode($input->getString('value', '[]'));
		$size     = $input->getInt('size');
		$multiple = $input->getBool('multiple');

		return $this->selectListSimple($options, $name, $value, $id, $size, $multiple);
	}

	function getUsers()
	{
		$query = $this->db->getQuery(true)
			->select('COUNT(*)')
			->from('#__users AS u');
		$this->db->setQuery($query);
		$total = $this->db->loadResult();

		if ($total > $this->max_list_count)
		{
			return -1;
		}

		$query->clear('select')
			->select('u.name, u.username, u.id, u.block as disabled')
			->order('name');
		$this->db->setQuery($query);
		$list = $this->db->loadObjectList();

		$list = array_map(function ($item) {
			if ($item->disabled)
			{
				$item->name .= ' (' . JText::_('JDISABLED') . ')';
			}

			return $item;
		}, $list);

		return $this->getOptionsByList($list, ['username', 'id']);
	}
}

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

class JFormFieldRL_AccessLevel extends \RegularLabs\Library\Field
{
	public $type = 'AccessLevel';

	protected function getInput()
	{
		$this->params = $this->element->attributes();

		$size      = (int) $this->get('size');
		$multiple  = $this->get('multiple');
		$show_all  = $this->get('show_all');
		$use_names = $this->get('use_names');

		return $this->selectListAjax(
			$this->type, $this->name, $this->value, $this->id,
			compact('size', 'multiple', 'show_all', 'use_names')
		);
	}

	function getAjaxRaw()
	{
		$input = JFactory::getApplication()->input;

		$options = $this->getOptions(
			$input->getBool('show_all'),
			$input->getBool('use_names')
		);

		$name     = $input->getString('name', $this->type);
		$id       = $input->get('id', strtolower($name));
		$value    = json_decode($input->getString('value', '[]'));
		$size     = $input->getInt('size');
		$multiple = $input->getBool('multiple');

		return $this->selectList($options, $name, $value, $id, $size, $multiple);
	}

	protected function getOptions($show_all = false, $use_names = false)
	{
		$options = $this->getAccessLevels($use_names);

		if ($show_all)
		{
			$option          = (object) [];
			$option->value   = -1;
			$option->text    = '- ' . JText::_('JALL') . ' -';
			$option->disable = '';
			array_unshift($options, $option);
		}

		return $options;
	}

	protected function getAccessLevels($use_names = false)
	{
		$value = $use_names ? 'a.title' : 'a.id';

		$query = $this->db->getQuery(true)
			->select($value . ' as value, a.title as text')
			->from('#__viewlevels AS a')
			->group('a.id')
			->order('a.ordering ASC');
		$this->db->setQuery($query);

		return $this->db->loadObjectList();
	}
}

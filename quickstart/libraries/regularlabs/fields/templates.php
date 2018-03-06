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

class JFormFieldRL_Templates extends \RegularLabs\Library\Field
{
	public $type = 'Templates';

	protected function getInput()
	{
		$this->params = $this->element->attributes();

		// fix old '::' separator and change it to '--'
		$value = json_encode($this->value);
		$value = str_replace('::', '--', $value);
		$value = (array) json_decode($value, true);

		$size     = (int) $this->get('size');
		$multiple = $this->get('multiple');

		return $this->selectListAjax(
			$this->type, $this->name, $value, $this->id,
			compact('size', 'multiple')
		);
	}

	function getAjaxRaw()
	{
		$input = JFactory::getApplication()->input;

		$options = $this->getOptions();

		$name     = $input->getString('name', $this->type);
		$id       = $input->get('id', strtolower($name));
		$value    = json_decode($input->getString('value', '[]'));
		$size     = $input->getInt('size');
		$multiple = $input->getBool('multiple');

		return $this->selectList($options, $name, $value, $id, $size, $multiple);
	}

	protected function getOptions()
	{
		$options = [];

		$templates = $this->getTemplates();

		foreach ($templates as $styles)
		{
			$level = 0;
			foreach ($styles as $style)
			{
				$style->level = $level;
				$options[]    = $style;

				if (count($styles) <= 2)
				{
					break;
				}

				$level = 1;
			}
		}

		return $options;
	}

	protected function getTemplates()
	{
		$groups = [];
		$lang   = JFactory::getLanguage();

		// Get the database object and a new query object.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('s.id, s.title, e.name as name, s.template')
			->from('#__template_styles as s')
			->where('s.client_id = 0')
			->join('LEFT', '#__extensions as e on e.element=s.template')
			->where('e.enabled=1')
			->where($db->quoteName('e.type') . '=' . $db->quote('template'))
			->order('s.template')
			->order('s.title');

		// Set the query and load the styles.
		$db->setQuery($query);
		$styles = $db->loadObjectList();

		// Build the grouped list array.
		if ($styles)
		{
			foreach ($styles as $style)
			{
				$template = $style->template;
				$lang->load('tpl_' . $template . '.sys', JPATH_SITE)
				|| $lang->load('tpl_' . $template . '.sys', JPATH_SITE . '/templates/' . $template);
				$name = JText::_($style->name);

				// Initialize the group if necessary.
				if ( ! isset($groups[$template]))
				{
					$groups[$template]   = [];
					$groups[$template][] = JHtml::_('select.option', $template, $name);
				}

				$groups[$template][] = JHtml::_('select.option', $template . '--' . $style->id, $style->title);
			}
		}

		return $groups;
	}
}

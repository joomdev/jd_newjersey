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

class JFormFieldRL_Languages extends \RegularLabs\Library\Field
{
	public $type = 'Languages';

	protected function getInput()
	{
		$this->params = $this->element->attributes();

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

		$name     = $input->getString('name', $this->type);
		$id       = $input->get('id', strtolower($name));
		$value    = json_decode($input->getString('value', '[]'));
		$size     = $input->getInt('size');
		$multiple = $input->getBool('multiple');

		$options = $this->getLanguages($value);

		return $this->selectListSimple($options, $name, $value, $id, $size, $multiple);
	}

	function getLanguages($value)
	{
		$langs = JHtml::_('contentlanguage.existing');

		if ( ! is_array($value))
		{
			$value = [$value];
		}

		$options = [];

		foreach ($langs as $lang)
		{
			if (empty($lang->value))
			{
				continue;
			}

			$options[] = (object) [
				'value'    => $lang->value,
				'text'     => $lang->text . ' [' . $lang->value . ']',
				'selected' => in_array($lang->value, $value),
			];
		}

		return $options;
	}
}

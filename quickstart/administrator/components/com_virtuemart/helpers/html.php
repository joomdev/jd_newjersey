<?php
/**
 * HTML helper class
 *
 * This class was developed to provide some standard HTML functions.
 *
 * @package	VirtueMart
 * @subpackage Helpers
 * @author RickG
 * @copyright Copyright (c) 2004-2008 Soeren Eberhardt-Biermann, 2009 VirtueMart Team. All rights reserved.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * HTML Helper
 *
 * @package VirtueMart
 * @subpackage Helpers
 * @author RickG
 */
class VmHtml{

	/**
	 * Default values for options. Organized by option group.
	 *
	 * @var     array
	 * @since   11.1
	 */
	static protected $_optionDefaults = array(
		'option' => array('option.attr' => null, 'option.disable' => 'disable', 'option.id' => null, 'option.key' => 'value',
			'option.key.toHtml' => true, 'option.label' => null, 'option.label.toHtml' => true, 'option.text' => 'text',
			'option.text.toHtml' => true));

	static protected $_usedId = array();

	static function ensureUniqueId($id){

		if(isset(self::$_usedId[$id])){
			$c = 1;
			while(isset(self::$_usedId[$id.$c])){
				$c++;
			}
			$id = $id.$c;
		}
		self::$_usedId[$id] = 1;
		return $id;
	}

	/**
	 * Converts all special chars to html entities
	 *
	 * @param string $string
	 * @param string $quote_style
	 * @param boolean $only_special_chars Only Convert Some Special Chars ? ( <, >, &, ... )
	 * @return string
	 */
	static function shopMakeHtmlSafe( $string, $quote_style='ENT_QUOTES', $use_entities=false ) {

		if( defined( $quote_style )) {
			$quote_style = constant($quote_style);
		}
		if( $use_entities ) {
			$string = @htmlentities( $string, constant($quote_style), 'UTF-8' );
		} else {
			$string = @htmlspecialchars( $string, $quote_style, 'UTF-8' );
		}
		return $string;
	}


	/**
	 * Returns the charset string from the global _ISO constant
	 *
	 * @deprecated
	 * @return string UTF-8 by default
	 * @since 1.0.5
	 */
	static function vmGetCharset() {
		return 'UTF-8';
	}


    /**
     * Generate HTML code for a row using VmHTML function
     * works also with shopfunctions, for example
	 * $html .= VmHTML::row (array('ShopFunctions', 'renderShopperGroupList'),
	 * 			'VMCUSTOM_BUYER_GROUP_SHOPPER', $field->shopper_groups, TRUE, 'custom_param['.$row.'][shopper_groups][]', ' ');
	 *
     * @func string  : function to call
     * @label string : Text Label
     * @args array : arguments
     * @return string: HTML code for row table
     */
    static function row($func,$label){
		$VmHTML="VmHtml";
		if (!is_array($func)) {
			$func = array($VmHTML, $func);
		}
		$passedArgs = func_get_args();
		array_shift( $passedArgs );//remove function
		array_shift( $passedArgs );//remove label
			$args = array();
			foreach ($passedArgs as $k => $v) {
			    $args[] = &$passedArgs[$k];
			}
		$lang =JFactory::getLanguage();
		if($lang->hasKey($label.'_TIP')){
			$label = '<span class="hasTip" title="'.htmlentities(vmText::_($label.'_TIP')).'">'.vmText::_($label).'</span>' ;
		} //Fallback
		else if($lang->hasKey($label.'_EXPLAIN')){
			$label = '<span class="hasTip" title="'.htmlentities(vmText::_($label.'_EXPLAIN')).'">'.vmText::_($label).'</span>' ;
		} else {
			$label = vmText::_($label);
		}
		if ($func[1]=="checkbox" OR $func[1]=="input") {
			$label = "\n\t" . '<label for="' . $args[0] . '" id="' . $args[0] . '-lbl"  >'.$label."</label>";
		}
		$html = '
		<tr>
			<td class="key">
				'.$label.'
			</td>
			<td>';
		if($func[1]=='radioList'){
			$html .= '<fieldset class="checkboxes">';
		}

		$html .= call_user_func_array($func, $args).'
			</td>';
		if($func[1]=='radioList'){
			$html .= '</fieldset>';
		}
		$html .= '</tr>';
		return $html ;
	}
	/* simple value display */
	static function value( $value ){
		$lang =JFactory::getLanguage();
		return $lang->hasKey($value) ? vmText::_($value) : $value;
	}

	/**
	 * The sense is unclear !
	 * @deprecated
	 * @param $value
	 * @return mixed
	 */
	static function raw( $value ){
		return $value;
	}
    /**
     * Generate HTML code for a checkbox
     *
     * @param string Name for the checkbox
     * @param mixed Current value of the checkbox
     * @param mixed Value to assign when checkbox is checked
     * @param mixed Value to assign when checkbox is not checked
     * @return string HTML code for checkbox
     */
    static function checkbox($name, $value, $checkedValue=1, $uncheckedValue=0, $extraAttribs = '', $id = null) {
		if (!$id){
			$id ='id="' . $name.'"';
		} else {
			$id = 'id="' . $id.'"';
		}

		if ($value == $checkedValue) {
			$checked = 'checked="checked"';
		}
		else {
			$checked = '';
		}

		$htmlcode = '<input type="hidden" name="' . $name . '" value="' . $uncheckedValue . '" />';
		$htmlcode .= '<input '.$extraAttribs.' ' . $id . ' type="checkbox" name="' . $name . '" value="' . $checkedValue . '" ' . $checked . ' />';
		return $htmlcode;
    }

	/**
	 *
	 * @author Patrick Kohl
	 * @param array $options( value & text)
	 * @param string $name option name
	 * @param string $defaut defaut value
	 * @param string $key option value
	 * @param string $text option text
	 * @param boolean $zero add  a '0' value in the option
	 * return a select list
	 */
	public static function select($name, $options, $default = '0',$attrib = "onchange='submit();'",$key ='value' ,$text ='text', $zero=true, $chosenDropDowns=true,$tranlsate=true){
		if ($zero==true) {
			$option  = array($key =>"0", $text => vmText::_('COM_VIRTUEMART_LIST_EMPTY_OPTION'));
			$options = array_merge(array($option), $options);
		}
		if ($chosenDropDowns) {
			vmJsApi::chosenDropDowns();
			$attrib .= ' class="vm-chzn-select"';

		}
		return VmHtml::genericlist($options,$name,$attrib,$key,$text,$default,false,$tranlsate);
	}

	/**
	 * Generates an HTML selection list.
	 * @author Joomla 2.5.14
	 * @param   array    $data       An array of objects, arrays, or scalars.
	 * @param   string   $name       The value of the HTML name attribute.
	 * @param   mixed    $attribs    Additional HTML attributes for the <select> tag. This
	 *                               can be an array of attributes, or an array of options. Treated as options
	 *                               if it is the last argument passed. Valid options are:
	 *                               Format options, see {@see JHtml::$formatOptions}.
	 *                               Selection options, see {@see JHtmlSelect::options()}.
	 *                               list.attr, string|array: Additional attributes for the select
	 *                               element.
	 *                               id, string: Value to use as the select element id attribute.
	 *                               Defaults to the same as the name.
	 *                               list.select, string|array: Identifies one or more option elements
	 *                               to be selected, based on the option key values.
	 * @param   string   $optKey     The name of the object variable for the option value. If
	 *                               set to null, the index of the value array is used.
	 * @param   string   $optText    The name of the object variable for the option text.
	 * @param   mixed    $selected   The key that is selected (accepts an array or a string).
	 * @param   mixed    $idtag      Value of the field id or null by default
	 * @param   boolean  $translate  True to translate
	 *
	 * @return  string  HTML for the select list.
	 *
	 * @since   11.1
	 */
	public static function genericlist($data, $name, $attribs = null, $optKey = 'value', $optText = 'text', $selected = null, $idtag = false,
									   $translate = false)
	{
		// Set default options
		$options = array_merge(JHtml::$formatOptions, array('format.depth' => 0, 'id' => false));
		if (is_array($attribs) && func_num_args() == 3)
		{
			// Assume we have an options array
			$options = array_merge($options, $attribs);
		}
		else
		{
			// Get options from the parameters
			$options['id'] = $idtag;
			$options['list.attr'] = $attribs;
			$options['list.translate'] = $translate;
			$options['option.key'] = $optKey;
			$options['option.text'] = $optText;
			$options['list.select'] = $selected;
		}
		$attribs = '';
		if (isset($options['list.attr']))
		{
			if (is_array($options['list.attr']))
			{
				$attribs = JArrayHelper::toString($options['list.attr']);
			}
			else
			{
				$attribs = $options['list.attr'];
			}
			if ($attribs != '')
			{
				$attribs = ' ' . $attribs;
			}
		}

		$id = $options['id'] !== false ? $options['id'] : $name;
		$id = str_replace(array('[', ']'), '', $id);

		$baseIndent = str_repeat($options['format.indent'], $options['format.depth']++);
		$html = $baseIndent . '<select' . ($id !== '' ? ' id="' . $id . '"' : '') . ' name="' . $name . '"' . $attribs . '>' . $options['format.eol']
			. self::options($data, $options) . $baseIndent . '</select>' . $options['format.eol'];
		return $html;
	}

	/**
	 * Generates the option tags for an HTML select list (with no select tag
	 * surrounding the options).
	 * @author Joomla 2.5.14
	 * @param   array    $arr        An array of objects, arrays, or values.
	 * @param   mixed    $optKey     If a string, this is the name of the object variable for
	 *                               the option value. If null, the index of the array of objects is used. If
	 *                               an array, this is a set of options, as key/value pairs. Valid options are:
	 *                               -Format options, {@see JHtml::$formatOptions}.
	 *                               -groups: Boolean. If set, looks for keys with the value
	 *                                "&lt;optgroup>" and synthesizes groups from them. Deprecated. Defaults
	 *                                true for backwards compatibility.
	 *                               -list.select: either the value of one selected option or an array
	 *                                of selected options. Default: none.
	 *                               -list.translate: Boolean. If set, text and labels are translated via
	 *                                vmText::_(). Default is false.
	 *                               -option.id: The property in each option array to use as the
	 *                                selection id attribute. Defaults to none.
	 *                               -option.key: The property in each option array to use as the
	 *                                selection value. Defaults to "value". If set to null, the index of the
	 *                                option array is used.
	 *                               -option.label: The property in each option array to use as the
	 *                                selection label attribute. Defaults to null (none).
	 *                               -option.text: The property in each option array to use as the
	 *                               displayed text. Defaults to "text". If set to null, the option array is
	 *                               assumed to be a list of displayable scalars.
	 *                               -option.attr: The property in each option array to use for
	 *                                additional selection attributes. Defaults to none.
	 *                               -option.disable: The property that will hold the disabled state.
	 *                                Defaults to "disable".
	 *                               -option.key: The property that will hold the selection value.
	 *                                Defaults to "value".
	 *                               -option.text: The property that will hold the the displayed text.
	 *                               Defaults to "text". If set to null, the option array is assumed to be a
	 *                               list of displayable scalars.
	 * @param   string   $optText    The name of the object variable for the option text.
	 * @param   mixed    $selected   The key that is selected (accepts an array or a string)
	 * @param   boolean  $translate  Translate the option values.
	 *
	 * @return  string  HTML for the select list
	 *
	 * @since   11.1
	 */
	public static function options($arr, $optKey = 'value', $optText = 'text', $selected = null, $translate = false)
	{
		$options = array_merge(
			JHtml::$formatOptions,
			self::$_optionDefaults['option'],
			array('format.depth' => 0, 'groups' => true, 'list.select' => null, 'list.translate' => false)
		);

		if (is_array($optKey))
		{
			// Set default options and overwrite with anything passed in
			$options = array_merge($options, $optKey);
		}
		else
		{
			// Get options from the parameters
			$options['option.key'] = $optKey;
			$options['option.text'] = $optText;
			$options['list.select'] = $selected;
			$options['list.translate'] = $translate;
		}

		$html = '';
		$baseIndent = str_repeat($options['format.indent'], $options['format.depth']);

		foreach ($arr as $elementKey => &$element)
		{
			$attr = '';
			$extra = '';
			$label = '';
			$id = '';
			if (is_array($element))
			{
				$key = $options['option.key'] === null ? $elementKey : $element[$options['option.key']];
				$text = $element[$options['option.text']];
				if (isset($element[$options['option.attr']]))
				{
					$attr = $element[$options['option.attr']];
				}
				if (isset($element[$options['option.id']]))
				{
					$id = $element[$options['option.id']];
				}
				if (isset($element[$options['option.label']]))
				{
					$label = $element[$options['option.label']];
				}
				if (isset($element[$options['option.disable']]) && $element[$options['option.disable']])
				{
					$extra .= ' disabled="disabled"';
				}
			}
			elseif (is_object($element))
			{
				$key = $options['option.key'] === null ? $elementKey : $element->{$options['option.key']};
				$text = $element->{$options['option.text']};
				if (isset($element->{$options['option.attr']}))
				{
					$attr = $element->{$options['option.attr']};
				}
				if (isset($element->{$options['option.id']}))
				{
					$id = $element->{$options['option.id']};
				}
				if (isset($element->{$options['option.label']}))
				{
					$label = $element->{$options['option.label']};
				}
				if (isset($element->{$options['option.disable']}) && $element->{$options['option.disable']})
				{
					$extra .= ' disabled="disabled"';
				}
			}
			else
			{
				// This is a simple associative array
				$key = $elementKey;
				$text = $element;
			}

			// The use of options that contain optgroup HTML elements was
			// somewhat hacked for J1.5. J1.6 introduces the grouplist() method
			// to handle this better. The old solution is retained through the
			// "groups" option, which defaults true in J1.6, but should be
			// deprecated at some point in the future.

			$key = (string) $key;

			// if no string after hyphen - take hyphen out
			$splitText = explode(' - ', $text, 2);
			$text = $splitText[0];
			if (isset($splitText[1]))
			{
				$text .= ' - ' . $splitText[1];
			}

			if ($options['list.translate'] && !empty($label))
			{
				$label = vmText::_($label);
			}
			if ($options['option.label.toHtml'])
			{
				$label = htmlentities($label);
			}
			if (is_array($attr))
			{
				$attr = JArrayHelper::toString($attr);
			}
			else
			{
				$attr = trim($attr);
			}
			$extra = ($id ? ' id="' . $id . '"' : '') . ($label ? ' label="' . $label . '"' : '') . ($attr ? ' ' . $attr : '') . $extra;
			if (is_array($options['list.select']))
			{
				foreach ($options['list.select'] as $val)
				{
					$key2 = is_object($val) ? $val->{$options['option.key']} : $val;
					if ($key == $key2)
					{
						$extra .= ' selected="selected"';
						break;
					}
				}
			}
			elseif ((string) $key == (string) $options['list.select'])
			{
				$extra .= ' selected="selected"';
			}

			if ($options['list.translate'])
			{
				$text = vmText::_($text);
			}

			// Generate the option, encoding as required
			$html .= $baseIndent . '<option value="' . ($options['option.key.toHtml'] ? htmlspecialchars($key, ENT_COMPAT, 'UTF-8') : $key) . '"'
				. $extra . '>';
			$html .= $options['option.text.toHtml'] ? htmlentities(html_entity_decode($text, ENT_COMPAT, 'UTF-8'), ENT_COMPAT, 'UTF-8') : $text;
			$html .= '</option>' . $options['format.eol'];

		}

		return $html;
	}

	/**
	 * Prints an HTML dropdown box named $name using $arr to
	 * load the drop down.  If $value is in $arr, then $value
	 * will be the selected option in the dropdown.
	 * @author gday
	 * @author soeren
	 *
	 * @param string $name The name of the select element
	 * @param string $value The pre-selected value
	 * @param array $arr The array containing $key and $val
	 * @param int $size The size of the select element
	 * @param string $multiple use "multiple=\"multiple\" to have a multiple choice select list
	 * @param string $extra More attributes when needed
	 * @return string HTML drop-down list
	 */
	static function selectList($name, $value, $arrIn, $size=1, $multiple="", $extra="", $data_placeholder='') {

		$html = '';
		if( empty( $arrIn ) ) {
			$arr = array();
		} else {
			if(!is_array($arrIn)){
	        	 $arr=array($arrIn);
	        } else {
	        	 $arr=$arrIn;
	        }
		}
		if (!empty($data_placeholder)) {
			$data_placeholder='data-placeholder="'.vmText::_($data_placeholder).'"';
		}

		$html = '<select class="inputbox" id="'.$name.'" name="'.$name.'" size="'.$size.'" '.$multiple.' '.$extra.' '.$data_placeholder.' >';

		while (list($key, $val) = each($arr)) {
//		foreach ($arr as $key=>$val){
			$selected = "";
			if( is_array( $value )) {
				if( in_array( $key, $value )) {
					$selected = 'selected="selected"';
				}
			}
			else {
				if(strtolower($value) == strtolower($key) ) {
					$selected = 'selected="selected"';
				}
			}

			$html .= '<option value="'.$key.'" '.$selected.'>'.self::shopMakeHtmlSafe($val);
			$html .= '</option>';

		}

		$html .= '</select>';

		return $html;
	}

	/**
	 * @author Joomla
	 */
	static function color($name, $value) {

		$color = strtolower($value);

		if (!$color || in_array($color, array('none', 'transparent'))) {
			$color = 'none';
		} elseif ($color['0'] != '#') {
			$color = '#' . $color;
		}

		// Including fallback code for HTML5 non supported browsers.
		vmJsApi::jQuery();

		if (JVM_VERSION > 1) {
			$class = ' class="minicolors"';
		} else {
			$class = ' class="input-colorpicker"';
			JHtml::_('script', 'system/html5fallback.js', false, true);
		}

		JHtml::_('behavior.colorpicker');

		return '<input type="text" name="' . $name . '" ' . ' value="'
		. htmlspecialchars($color, ENT_COMPAT, 'UTF-8') . '"' . $class
		. '/>';

	}



	/**
	 * Creates a Radio Input List
	 *
	 * @param string $name
	 * @param string $value default value
	 * @param string $arr
	 * @param string $extra
	 * @return string
	 */
	static function radioList($name, $value, &$arr, $extra="", $separator='<br />') {
		$html = '';
		if( empty( $arr ) ) {
			$arr = array();
		}
		$html = '<div class="controls">';
		$i = 0;
		foreach($arr as $key => $val) {
			$checked = '';
			if( is_array( $value )) {
				if( in_array( $key, $value )) {
					$checked = 'checked="checked"';
				}
			}
			else {
				if(strtolower($value) == strtolower($key) ) {
					$checked = 'checked="checked"';
				}
			}
			$id = $name.$key;
			$html .= "\n\t" . '<label for="' . $id . '" id="' . $id . '-lbl" class="radio">';
			$html .= "\n\t\n\t" . '<input type="radio" name="' . $name . '" id="' . $id . '" value="' . htmlspecialchars($key, ENT_QUOTES) . '" '.$checked.' ' . $extra. ' />' . $val;
			$html .= "\n\t" . "</label>".$separator."\n";

		}

		$html .= "\n";
		$html .= '</div>';
		$html .= "\n";

		return $html;
	}

	/**
	 * Creates radio List
	 * @param array $radios
	 * @param string $name
	 * @param string $default
	 * @return string
	 */
	static function radio( $name, $radios, $default,$key='value',$text='text') {
		return '<fieldset class="radio">'.JHtml::_('select.radiolist', $radios, $name, '', $key, $text, $default).'</fieldset>';
	}
	/**
	 * Creating rows with boolean list
	 *
	 * @author Patrick Kohl
	 * @param string $label
	 * @param string $name
	 * @param string $value
	 *
	 */
	public static function booleanlist (  $name, $value,$class='class="inputbox"'){
		return '<fieldset class="radio">'.JHtml::_( 'select.booleanlist',  $name , $class , $value).'</fieldset>' ;
	}

	/**
	 * Creating rows with input fields
	 *
	 * @param string $text
	 * @param string $name
	 * @param string $value
	 */
	public static function input($name,$value,$class='class="inputbox"',$readonly='',$size='37',$maxlength='255',$more=''){
		return '<input type="text" '.$readonly.' '.$class.' id="'.$name.'" name="'.$name.'" size="'.$size.'" maxlength="'.$maxlength.'" value="'.($value).'" />'.$more;
	}

	/**
	 * Creating rows with input fields
	 *
	 * @author Patrick Kohl
	 * @param string $text
	 * @param string $name
	 * @param string $value
	 */
	public static function textarea($name,$value,$class='class="inputbox"',$cols='100',$rows="4"){
		return '<textarea '.$class.' id="'.$name.'" name="'.$name.'" cols="'.$cols.'" rows="'.$rows.'">'.$value.'</textarea >';
	}
	/**
	 * render editor code
	 *
	 * @author Patrick Kohl
	 * @param string $text
	 * @param string $name
	 * @param string $value
	 */
	public static function editor($name,$value,$size='100%',$height='300',$hide = array('pagebreak', 'readmore')){
		$editor =JFactory::getEditor();
		return $editor->display($name, $value, $size, $height, null, null ,$hide )  ;
	}


	/**
	 * renders the hidden input
	 * @author Max Milbers
	 */
	public static function inputHidden($values){
		$html='';
		foreach($values as $k=>$v){
			$html .= '<input type="hidden" name="'.$k.'" value="'.$v.'" />';
		}
		return $html;
	}

	/**
	* @author Patrick Kohl
	* @var $type type of regular Expression to validate
	* $type can be I integer, F Float, A date, M, time, T text, L link, U url, P phone
	*@bool $required field is required
	*@Int $min minimum of char
	*@Int $max max of char
	*@var $match original ID field to compare with this such as Email, passsword
	*@ Return $html class for validate javascript
	**/
	public static function validate($type='',$required=true, $min=null,$max=null,$match=null) {

		if ($required) $validTxt = 'required';
		else $validTxt = 'optional';
		if (isset($min)) $validTxt .= ',minSize['.$min.']';
		if (isset($max)) $validTxt .= ',maxSize['.$max.']';
		static $validateID=0 ;
		$validateID++;
		if ($type=='S' ) return 'id="validate'.$validateID.'" class="validate[required,minSize[2],maxSize[255]]"';
		$validate = array ( 'I'=>'onlyNumberSp', 'F'=>'number','D'=>'dateTime','A'=>'date','M'=>'time','T'=>'Text','L'=>'link','U'=>'url','P'=>'phone');
		if (isset ($validate[$type])) $validTxt .= ',custom['.$validate[$type].']';
		$html ='id="validate'.$validateID.'" class="validate['.$validTxt.']"';

		return $html ;
	}

}
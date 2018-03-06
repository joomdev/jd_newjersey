<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('rules');

defined('DS') or define('DS', DIRECTORY_SEPARATOR);
if (!class_exists( 'VmConfig' )) require(JPATH_ROOT .'/administrator/components/com_virtuemart/helpers/config.php');

/**
 * This is an overload of the core Rules form field
 * It address the issue where several rules cannot be used in the same configuration file
 */
class JFormFieldVmRules extends JFormFieldRules {
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	var $type = 'VMRules';

	/**
	 * Method to get the field input markup for Access Control Lists.
	 * This is an overload of the core Rules form field
	 * It address the issue where several rules cannot be used in the same configuration file
	 */
	protected function getInput() {

		VmConfig::loadConfig();
		vmLanguage::loadJLang('com_virtuemart_perms');
		JHtml::_('behavior.tooltip');
		if(JVM_VERSION<3){
			return $this->vmRulesJ25();
		} else {
			if (version_compare (JVERSION, '3.5.0', 'lt')) {
				return $this->vmRules();
			} else {
				return parent::getInput();
			}
		}
	}


	private function vmRules(){
		// Initialise some field attributes.
		$section = $this->element['section'] ? (string)$this->element['section'] : '';
		$component = $this->element['component'] ? (string)$this->element['component'] : '';
		$assetField = $this->element['asset_field'] ? (string)$this->element['asset_field'] : 'asset_id';

		// Get the actions for the asset.
		$actions = JAccess::getActionsFromFile(JPATH_ADMINISTRATOR . '/components/' . $component . '/access.xml',
		"/access/section[@name='" . $section . "']/");
		//$actions = JAccess::getActions($component, $section);

		// Iterate over the children and add to the actions.
		foreach ($this->element->children() as $el) {
			if ($el->getName() == 'action') {
				$actions[] = (object)array('name' => (string)$el['name'], 'title' => (string)$el['title'],
					'description' => (string)$el['description']);
			}
		}


		// Get the explicit rules for this asset.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('id'));
		$query->from($db->quoteName('#__assets'));
		$query->where($db->quoteName('name') . ' = ' . $db->quote($component));
		$db->setQuery($query);
		$assetId = (int)$db->loadResult();
		if ($error = $db->getErrorMsg()) {
			vmError($error);
		}

		// Full width format.
		// Get the rules for just this asset (non-recursive).

		$assetRules = JAccess::getAssetRules($assetId);

		// Get the available user groups.
		$groups = $this->getUserGroups();

		// Build the form control.
		$curLevel = 0;

		// Prepare output
		$html = array();
		$html[] = '<p class="rule-desc">' . vmText::_('JLIB_RULES_SETTINGS_DESC') . '</p>';
		$html[] = '<div id="permissions-sliders" class="pane-sliders tabbable tabs-left">';
		$html[] = '<ul id="rules" class="nav nav-tabs">';
		$per = 1 ;
		// Start a row for each user group.
		foreach ($groups as $group) {
			$perlevel = $per++;
			if ($perlevel == 1) {
				$active ="active";
			} else {
				$active ="";
			}

			$html[] = '<li class="'.$active.'">';

			$html[] = '<a data-toggle="tab" href="#permission-'.$section.'-'.$perlevel.'"><span>';
			$html[] = str_repeat('<span class="level">|&ndash;</span> ', $curLevel = $group->level) . $group->text;
			$html[] = '</span></a>';

			$html[] = '</li>';


		}  // End a row for each user group.
		$html[] = '</ul>';
		$html[] = '<div class="tab-content">';
		$per = 1 ;
		foreach ($groups as $group) {
			$perlevel = $per++;
			if ($perlevel == 1) {
				$active ="active";
			} else {
				$active ="";
			}
			$html[] = '<div id="permission-'.$section.'-'.$perlevel.'" class="tab-pane '.$active.'">';
			$html[] = '<table class="group-rules table table-striped">';
			$html[] = '<thead>';
			$html[] = '<tr>';

			$html[] = '<th class="actions" id="actions-th' . $group->value . '">';
			$html[] = '<span class="acl-action">' . vmText::_('JLIB_RULES_ACTION') . '</span>';
			$html[] = '</th>';

			$html[] = '<th class="settings" id="settings-th' . $group->value . '">';
			$html[] = '<span class="acl-action">' . vmText::_('JLIB_RULES_SELECT_SETTING') . '</span>';
			$html[] = '</th>';

			// The calculated setting is not shown for the root group of global configuration.
			$canCalculateSettings = ($group->parent_id || !empty($component));
			if ($canCalculateSettings) {
				$html[] = '<th id="aclactionth' . $group->value . '">';
				$html[] = '<span class="acl-action">' . vmText::_('JLIB_RULES_CALCULATED_SETTING') . '</span>';
				$html[] = '</th>';
			}

			$html[] = '</tr>';
			$html[] = '</thead>';
			$html[] = '<tbody>';

			foreach ($actions as $action) {
				$html[] = '<tr>';
				$html[] = '<td headers="actions-th' . $group->value . '">';
				$html[] = '<label class="hasTip" for="' . $this->id . '_' . $action->name . '_' . $group->value . '" title="'
					. htmlspecialchars(vmText::_($action->title) . '::' . vmText::_($action->description), ENT_COMPAT, 'UTF-8') . '">';
				$html[] = vmText::_($action->title);
				$html[] = '</label>';
				$html[] = '</td>';

				$html[] = '<td headers="settings-th' . $group->value . '">';
				//$html[] = $this->formControl.'-'.$this->fieldname;
				$this->name = $this->formControl . '[rules]';
				$html[] = '<select name="' . $this->name . '[' . $action->name . '][' . $group->value . ']" id="' . $this->id . '_' . $action->name
					. '_' . $group->value . '" title="'
					. vmText::sprintf('JLIB_RULES_SELECT_ALLOW_DENY_GROUP', vmText::_($action->title), trim($group->text)) . '">';

				$inheritedRule = JAccess::checkGroup($group->value, $action->name, $assetId);

				// Get the actual setting for the action for this group.
				$assetRule = $assetRules->allow($action->name, $group->value);

				// Build the dropdowns for the permissions sliders

				// The parent group has "Not Set", all children can rightly "Inherit" from that.
				$html[] = '<option value=""' . ($assetRule === null ? ' selected="selected"' : '') . '>'
					. vmText::_(empty($group->parent_id) && empty($component) ? 'JLIB_RULES_NOT_SET' : 'JLIB_RULES_INHERITED') . '</option>';
				$html[] = '<option value="1"' . ($assetRule === true ? ' selected="selected"' : '') . '>' . vmText::_('JLIB_RULES_ALLOWED')
					. '</option>';
				$html[] = '<option value="0"' . ($assetRule === false ? ' selected="selected"' : '') . '>' . vmText::_('JLIB_RULES_DENIED')
					. '</option>';

				$html[] = '</select>&#160; ';

				// If this asset's rule is allowed, but the inherited rule is deny, we have a conflict.
				if (($assetRule === true) && ($inheritedRule === false)) {
					$html[] = vmText::_('JLIB_RULES_CONFLICT');
				}

				$html[] = '</td>';

				// Build the Calculated Settings column.
				// The inherited settings column is not displayed for the root group in global configuration.
				if ($canCalculateSettings) {
					$html[] = '<td headers="aclactionth' . $group->value . '">';

					// This is where we show the current effective settings considering currrent group, path and cascade.
					// Check whether this is a component or global. Change the text slightly.

					if (JAccess::checkGroup($group->value, 'core.admin', $assetId) !== true) {
						if ($inheritedRule === null) {
							$html[] = '<span class="label label-important">' . vmText::_('JLIB_RULES_NOT_ALLOWED') . '</span>';
						} elseif ($inheritedRule === true) {
							$html[] = '<span class="label label-success">' . vmText::_('JLIB_RULES_ALLOWED') . '</span>';
						} elseif ($inheritedRule === false) {
							if ($assetRule === false) {
								$html[] = '<span class="label label-important">' . vmText::_('JLIB_RULES_NOT_ALLOWED') . '</span>';
							} else {
								$html[] = '<span class="label label-important"><span class="icon-lock">' . vmText::_('JLIB_RULES_NOT_ALLOWED_LOCKED')
									. '</span></span>';
							}
						}
					} elseif (!empty($component)) {
						$html[] = '<span class="label label-success"><span class="icon-lock">' . vmText::_('JLIB_RULES_ALLOWED_ADMIN')
							. '</span></span>';
					} else {
						// Special handling for  groups that have global admin because they can't  be denied.
						// The admin rights can be changed.
						if ($action->name === 'core.admin') {
							$html[] = '<span class="label label-success">' . vmText::_('JLIB_RULES_ALLOWED') . '</span>';
						} elseif ($inheritedRule === false) {
							// Other actions cannot be changed.
							$html[] = '<span class="label label-important"><span class="icon-lock">'
								. vmText::_('JLIB_RULES_NOT_ALLOWED_ADMIN_CONFLICT') . '</span></span>';
						} else {
							$html[] = '<span class="label label-success"><span class="icon-lock">' . vmText::_('JLIB_RULES_ALLOWED_ADMIN')
								. '</span></span>';
						}
					}

					$html[] = '</td>';
				}

				$html[] = '</tr>';
			}

			$html[] = '</tbody>';
			$html[] = '</table></div>';
		}

		$html[] = '</div></div>';
		$html[] = '<div class="alert">';
		if ($section == 'component' || $section == null) {
			$html[] = vmText::_('JLIB_RULES_SETTING_NOTES');
		} else {
			$html[] = vmText::_('JLIB_RULES_SETTING_NOTES_ITEM');
		}

		$html[] = '</div>';

		$css = "
          .form-horizontal .controls {
                 margin-left:0;
           }
        ";
		JFactory::getDocument()->addStyleDeclaration($css);

		return implode("\n", $html);
	}

	public function vmRulesJ25(){

		// Initialise some field attributes.
		$section = $this->element['section'] ? (string)$this->element['section'] : '';
		$component = $this->element['component'] ? (string)$this->element['component'] : '';
		$assetField = $this->element['asset_field'] ? (string)$this->element['asset_field'] : 'asset_id';

		// Get the actions for the asset.
		$actions = JAccess::getActionsFromFile(JPATH_ADMINISTRATOR . '/components/' . $component . '/access.xml',
		"/access/section[@name='" . $section . "']/");
		//$actions = JAccess::getActions($component, $section);

		// Iterate over the children and add to the actions.
		foreach ($this->element->children() as $el) {
			if ($el->getName() == 'action') {
				$actions[] = (object)array('name' => (string)$el['name'], 'title' => (string)$el['title'],
					'description' => (string)$el['description']);
			}
		}


		// Get the explicit rules for this asset.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('id'));
		$query->from($db->quoteName('#__assets'));
		$query->where($db->quoteName('name') . ' = ' . $db->quote($component));
		$db->setQuery($query);
		$assetId = (int)$db->loadResult();
		if ($error = $db->getErrorMsg()) {
			vmError($error);
		}

		//VmConfig::$echoDebug = true;
		//vmdebug('my query ',$query,$assetId);

		//die;
		// Full width format.
		// Get the rules for just this asset (non-recursive).

		$assetRules = JAccess::getAssetRules($assetId);

		// Get the available user groups.
		$groups = $this->getUserGroups();

		// Build the form control.
		$curLevel = 0;

		// Prepare output
		$html = array();
		$html[] = '<div id="permissions-sliders-' . $section . '">';
		$html[] = '<div id="permissions-sliders" class="pane-sliders">';
		$html[] = '<p class="rule-desc">' . vmText::_('JLIB_RULES_SETTINGS_DESC') . '</p>';
		$html[] = '<ul id="rules">';


		// Start a row for each user group.
		foreach ($groups as $group) {
			$difLevel = $group->level - $curLevel;

			if ($difLevel > 0) {
				$html[] = '<li><ul>';
			} elseif ($difLevel < 0) {
				$html[] = str_repeat('</ul></li>', -$difLevel);
			}

			$html[] = '<li>';

			$html[] = '<div class="panel">';
			$html[] = '<h3 class="pane-toggler title"><a href="javascript:void(0);"><span>';
			$html[] = str_repeat('<span class="level">|&ndash;</span> ', $curLevel = $group->level) . $group->text;
			$html[] = '</span></a></h3>';
			$html[] = '<div class="pane-slider content pane-hide">';
			$html[] = '<div class="mypanel">';
			$html[] = '<table class="group-rules">';
			$html[] = '<thead>';
			$html[] = '<tr>';

			$html[] = '<th class="actions" id="actions-th' . $group->value . '">';
			$html[] = '<span class="acl-action">' . vmText::_('JLIB_RULES_ACTION') . '</span>';
			$html[] = '</th>';

			$html[] = '<th class="settings" id="settings-th' . $group->value . '">';
			$html[] = '<span class="acl-action">' . vmText::_('JLIB_RULES_SELECT_SETTING') . '</span>';
			$html[] = '</th>';

			// The calculated setting is not shown for the root group of global configuration.
			$canCalculateSettings = ($group->parent_id || !empty($component));
			if ($canCalculateSettings) {
				$html[] = '<th id="aclactionth' . $group->value . '">';
				$html[] = '<span class="acl-action">' . vmText::_('JLIB_RULES_CALCULATED_SETTING') . '</span>';
				$html[] = '</th>';
			}

			$html[] = '</tr>';
			$html[] = '</thead>';
			$html[] = '<tbody>';

			foreach ($actions as $action) {
				$html[] = '<tr>';
				$html[] = '<td headers="actions-th' . $group->value . '">';
				$html[] = '<label class="hasTip" for="' . $this->id . '_' . $action->name . '_' . $group->value . '" title="'
					. htmlspecialchars(vmText::_($action->title) . '::' . vmText::_($action->description), ENT_COMPAT, 'UTF-8') . '">';
				$html[] = vmText::_($action->title);
				$html[] = '</label>';
				$html[] = '</td>';

				$html[] = '<td headers="settings-th' . $group->value . '">';
				//$html[] = $this->formControl.'-'.$this->fieldname;
				$this->name = $this->formControl . '[rules]';
				$html[] = '<select name="' . $this->name . '[' . $action->name . '][' . $group->value . ']" id="' . $this->id . '_' . $action->name
					. '_' . $group->value . '" title="'
					. vmText::sprintf('JLIB_RULES_SELECT_ALLOW_DENY_GROUP', vmText::_($action->title), trim($group->text)) . '">';

				$inheritedRule = JAccess::checkGroup($group->value, $action->name, $assetId);

				// Get the actual setting for the action for this group.
				$assetRule = $assetRules->allow($action->name, $group->value);

				// Build the dropdowns for the permissions sliders

				// The parent group has "Not Set", all children can rightly "Inherit" from that.
				$html[] = '<option value=""' . ($assetRule === null ? ' selected="selected"' : '') . '>'
					. vmText::_(empty($group->parent_id) && empty($component) ? 'JLIB_RULES_NOT_SET' : 'JLIB_RULES_INHERITED') . '</option>';
				$html[] = '<option value="1"' . ($assetRule === true ? ' selected="selected"' : '') . '>' . vmText::_('JLIB_RULES_ALLOWED')
					. '</option>';
				$html[] = '<option value="0"' . ($assetRule === false ? ' selected="selected"' : '') . '>' . vmText::_('JLIB_RULES_DENIED')
					. '</option>';

				$html[] = '</select>&#160; ';

				// If this asset's rule is allowed, but the inherited rule is deny, we have a conflict.
				if (($assetRule === true) && ($inheritedRule === false)) {
					$html[] = vmText::_('JLIB_RULES_CONFLICT');
				}

				$html[] = '</td>';

				// Build the Calculated Settings column.
				// The inherited settings column is not displayed for the root group in global configuration.
				if ($canCalculateSettings) {
					$html[] = '<td headers="aclactionth' . $group->value . '">';

					// This is where we show the current effective settings considering currrent group, path and cascade.
					// Check whether this is a component or global. Change the text slightly.

					if (JAccess::checkGroup($group->value, 'core.admin', $assetId) !== true) {
						if ($inheritedRule === null) {
							$html[] = '<span class="icon-16-unset">' . vmText::_('JLIB_RULES_NOT_ALLOWED') . '</span>';
						} elseif ($inheritedRule === true) {
							$html[] = '<span class="icon-16-allowed">' . vmText::_('JLIB_RULES_ALLOWED') . '</span>';
						} elseif ($inheritedRule === false) {
							if ($assetRule === false) {
								$html[] = '<span class="icon-16-denied">' . vmText::_('JLIB_RULES_NOT_ALLOWED') . '</span>';
							} else {
								$html[] = '<span class="icon-16-denied"><span class="icon-16-locked">' . vmText::_('JLIB_RULES_NOT_ALLOWED_LOCKED')
									. '</span></span>';
							}
						}
					} elseif (!empty($component)) {
						$html[] = '<span class="icon-16-allowed"><span class="icon-16-locked">' . vmText::_('JLIB_RULES_ALLOWED_ADMIN')
							. '</span></span>';
					} else {
						// Special handling for  groups that have global admin because they can't  be denied.
						// The admin rights can be changed.
						if ($action->name === 'core.admin') {
							$html[] = '<span class="icon-16-allowed">' . vmText::_('JLIB_RULES_ALLOWED') . '</span>';
						} elseif ($inheritedRule === false) {
							// Other actions cannot be changed.
							$html[] = '<span class="icon-16-denied"><span class="icon-16-locked">'
								. vmText::_('JLIB_RULES_NOT_ALLOWED_ADMIN_CONFLICT') . '</span></span>';
						} else {
							$html[] = '<span class="icon-16-allowed"><span class="icon-16-locked">' . vmText::_('JLIB_RULES_ALLOWED_ADMIN')
								. '</span></span>';
						}
					}

					$html[] = '</td>';
				}

				$html[] = '</tr>';
			}

			$html[] = '</tbody>';
			$html[] = '</table></div>';

			$html[] = '</div></div>';
			$html[] = '</li>';

		}

		$html[] = str_repeat('</ul></li>', $curLevel);
		$html[] = '</ul><div class="rule-notes">';
		if ($section == 'component' || $section == null) {
			$html[] = vmText::_('JLIB_RULES_SETTING_NOTES');
		} else {
			$html[] = vmText::_('JLIB_RULES_SETTING_NOTES_ITEM');
		}
		$html[] = '</div></div></div>';

		$js = "window.addEvent('domready', function(){ new Fx.Accordion($$('div#permissions-sliders-" . $section . " div#permissions-sliders.pane-sliders .panel h3.pane-toggler'),"
			. "$$('div#permissions-sliders-" . $section . " div#permissions-sliders.pane-sliders .panel div.pane-slider'), {onActive: function(toggler, i) {toggler.addClass('pane-toggler-down');"
			. "toggler.removeClass('pane-toggler');i.addClass('pane-down');i.removeClass('pane-hide');Cookie.write('jpanesliders_permissions-sliders-" . $section
			. $component
			. "',$$('div#permissions-sliders-" . $section . " div#permissions-sliders.pane-sliders .panel h3').indexOf(toggler));},"
			. "onBackground: function(toggler, i) {toggler.addClass('pane-toggler');toggler.removeClass('pane-toggler-down');i.addClass('pane-hide');"
			. "i.removeClass('pane-down');}, duration: 300, display: "
			. (int)vRequest::getInt('jpanesliders_permissions-sliders' . $component, '0', 'cookie') . ", show: "
			. (int)vRequest::getInt('jpanesliders_permissions-sliders' . $component, '0', 'cookie') . ", alwaysHide:true, opacity: false}); });";

		JFactory::getDocument()->addScriptDeclaration($js);

		return implode("\n", $html);
	}
}

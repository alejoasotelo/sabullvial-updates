<?php

/**
 * @package     Sabullvial.Administrator
 * @subpackage  com_sabullvial
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Table\Table;

FormHelper::loadFieldClass('text');

/**
 * Cliente Form Field class for the Sabullvial component
 *
 * @since  0.0.1
 */
class JFormFieldXXXConfig extends JFormFieldText
{
    /**
     * The field type.
     *
     * @var         string
     */
    protected $type = 'XXXConfig';

    /**
     * The mode of input associated with the field.
     *
     * @var    mixed
     * @since  3.2
     */
    protected $inputtype;

    /**
     * The addon in the input text.
     *
     * @var    mixed
     * @since  3.2
     */
    protected $addon;

    /**
     * The position (left|right) of the addon in the input text.
     *
     * @var    mixed
     * @since  3.2
     */
    protected $addonposition;

    protected $descriptiontooltip = '';

    /**
     * Name of the layout being used to render the field
     *
     * @var    string
     * @since  3.7
     */
    protected $layout = 'joomla.form.field.customtext';

    /**
     * Method to get certain otherwise inaccessible properties from the form field object.
     *
     * @param   string  $name  The property name for which to get the value.
     *
     * @return  mixed  The property value or null.
     *
     * @since   3.2
     */
    public function __get($name)
    {
        switch ($name) {
            case 'inputtype':
            case 'addon':
            case 'addonposition':
            case 'descriptiontooltip':
                return $this->$name;
        }

        return parent::__get($name);
    }

    /**
     * Method to attach a JForm object to the field.
     *
     * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param   mixed             $value    The form field value to validate.
     * @param   string            $group    The field name group control value. This acts as an array container for the field.
     *                                      For example if the field has name="foo" and the group value is set to "bar" then the
     *                                      full field name would end up being "bar[foo]".
     *
     * @return  boolean  True on success.
     *
     * @see     JFormField::setup()
     * @since   3.2
     */
    public function setup(SimpleXMLElement $element, $value, $group = null)
    {
        $result = parent::setup($element, $value, $group);

        if ($result == true) {
            $this->inputtype = (string) $this->element['inputtype'];
            $this->addon = (string) $this->element['addon'];
            $this->addonposition = (string) $this->element['addonposition'];
        }

        return $result;
    }

    /**
     * Method to get the data to be passed to the layout for rendering.
     *
     * @return  array
     *
     * @since 3.7
     */
    protected function getLayoutData()
    {
        $data = parent::getLayoutData();

        $key = isset($this->element['data-name']) ? (string)$this->element['data-name'] : $this->fieldname;

        Table::addIncludePath(JPATH_COMPONENT . '/tables');
        $config = Table::getInstance('XXXConfig', 'SabullvialTable');
        $config->load($key);

        if (!empty($config->id)) {
            $data['value'] = $config->valor;
        }

        // Initialize some field attributes.
        $maxLength    = !empty($this->maxLength) ? ' maxlength="' . $this->maxLength . '"' : '';
        $inputmode    = !empty($this->inputmode) ? ' inputmode="' . $this->inputmode . '"' : '';
        $dirname      = !empty($this->dirname) ? ' dirname="' . $this->dirname . '"' : '';
        $inputtype    = !empty($this->inputtype) ? ' type="' . $this->inputtype . '"' : ' type="text"';
        $addon    = !empty($this->addon) ? $this->addon : '';
        $addonposition = in_array($this->addonposition, ['left', 'right']) ? $this->addonposition : 'right';
        $descriptiontooltip = in_array($this->descriptiontooltip, ['true', 'false', 0, 1]) ? (bool)$this->descriptiontooltip : false;

        $options  = (array) $this->getOptions();

        $extraData = [
            'maxLength' => $maxLength,
            'pattern'   => $this->pattern,
            'inputmode' => $inputmode,
            'dirname'   => $dirname,
            'options'   => $options,
            'inputtype' => $inputtype,
            'addon'		=> $addon,
            'addonposition' => $addonposition,
            'descriptiontooltip' => $descriptiontooltip,
        ];

        return array_merge($data, $extraData);
    }
}

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

use Joomla\CMS\Language\Text;

/**
 * Cliente Form Field class for the Sabullvial component
 *
 * @since  0.0.1
 */
class JFormFieldImage extends JFormFieldText
{
    /**
     * The field type.
     *
     * @var         string
     */
    protected $type = 'Image';

    /**
     * Layout to render
     *
     * @var   string
     * @since 3.5
     */
    protected $layout = 'joomla.form.field.image';

    protected $preview = true;

    protected $width = '64';

    protected $height = '64';

    protected $button = false;

    protected $buttonText = '';

    protected $delete = false;

    /**
     * The javascript ondelete of the form field.
     *
     * @var    string
     */
    protected $ondelete;

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
            case 'preview':
            case 'delete':
            case 'width':
            case 'height':
            case 'button':
            case 'buttonText':
            case 'ondelete':
                return $this->$name;
        }

        return parent::__get($name);
    }

    /**
     * Method to set certain otherwise inaccessible properties of the form field object.
     *
     * @param   string  $name   The property name for which to set the value.
     * @param   mixed   $value  The value of the property.
     *
     * @return  void
     *
     * @since   3.2
     */
    public function __set($name, $value)
    {
        switch ($name) {
            case 'preview':
                $this->preview = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                break;

            case 'delete':
                $this->delete = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                break;

            case 'width':
            case 'height':
            case 'buttonText':
            case 'ondelete':
                $this->$name = (string) $value;
                break;

            case 'button':
                $this->button = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                break;

            default:
                parent::__set($name, $value);
        }
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

        if (!$result) {
            return $result;
        }

        $attributes = [
            'preview', 'delete', 'width', 'height', 'button', 'buttonText', 'ondelete'
        ];

        foreach ($attributes as $attributeName) {
            $this->__set($attributeName, $element[$attributeName]);
        }

        // $this->preview = filter_var($this->element['preview'], FILTER_VALIDATE_BOOLEAN);
        // $this->delete = filter_var($this->element['delete'], FILTER_VALIDATE_BOOLEAN);
        // $this->width = $this->element['width'];
        // $this->height = $this->element['height'];
        // $this->button = filter_var($this->element['button'], FILTER_VALIDATE_BOOLEAN);;
        // $this->buttonText = $this->element['buttonText'];


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

        $width = !empty($this->width) ? $this->width : '';
        $height = !empty($this->height) ? $this->height : '';
        $buttonText = !empty($this->buttonText) ? $this->buttonText : Text::_('COM_SABULLVIAL_FIELD_IMAGE_BUTTON_TEXT');

        $extraData = [
            'preview' => $this->preview,
            'delete' => $this->delete,
            'width' => $width,
            'height' => $height,
            'button' => $this->button,
            'buttonText' => $buttonText,
            'ondelete' => $this->ondelete,
        ];

        return array_merge($data, $extraData);
    }
}

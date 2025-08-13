<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormHelper;

FormHelper::loadFieldClass('subform');
jimport('joomla.filesystem.path');

/**
 * Supports a modal article picker.
 *
 * @since  1.6
 */
class JFormFieldModal_CotizacionDetalle extends JFormFieldSubform
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  1.6
     */
    protected $type = 'Modal_CotizacionDetalle';

    /**
     * Which buttonsRow to show in miltiple mode
     * @var array $buttonsRow
     */
    protected $buttonsRow = ['add' => true, 'remove' => true, 'move' => true];

    /**
     * Which buttonsRow to show in miltiple mode
     * @var array $buttonsRow
     */
    protected $dolar = false;

    /**
     * Which buttonsRow to show in miltiple mode
     * @var array $buttonsRow
     */
    protected $iva = true;

    /**
     * Method to get certain otherwise inaccessible properties from the form field object.
     *
     * @param   string  $name  The property name for which to get the value.
     *
     * @return  mixed  The property value or null.
     *
     * @since   3.6
     */
    public function __get($name)
    {
        switch ($name) {
            case 'buttonsRow':
            case 'dolar':
            case 'iva':
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
     * @since   3.6
     */
    public function __set($name, $value)
    {
        switch ($name) {
            case 'buttonsRow':

                if (!$this->multiple) {
                    $this->buttonsRow = [];
                    break;
                }

                if ($value && !is_array($value)) {
                    $value = explode(',', (string) $value);
                    $value = array_fill_keys(array_filter($value), true);
                }

                if ($value) {
                    $value = array_merge(['add' => false, 'remove' => false, 'move' => false], $value);
                    $this->buttonsRow = $value;
                }

                break;

            case 'iva':
                $this->iva = (bool)$value;
                break;

            case 'dolar':
                $this->dolar = (bool)$value;
                break;

            default:
                parent::__set($name, $value);
        }
    }

    /**
     * Method to attach a JForm object to the field.
     *
     * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
     * @param   mixed             $value    The form field value to validate.
     * @param   string            $group    The field name group control value.
     *
     * @return  boolean  True on success.
     *
     * @since   3.6
     */
    public function setup(SimpleXMLElement $element, $value, $group = null)
    {
        if (!parent::setup($element, $value, $group)) {
            return false;
        }

        if (!$element->layout) {
            $this->__set('layout', 'sabullvial.form.field.subform.repeatable-table');
        }

        if (!$element->buttons) {
            $this->__set('buttons', 'add,remove');
        }

        if (!$element->buttonsRow) {
            $this->__set('buttonsRow', 'remove');
        }

        if (!$element->dolar) {
            $this->__set('dolar', false);
        }

        if (!$element->iva) {
            $this->__set('iva', true);
        }

        return true;
    }

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   3.6
     */
    protected function getInput()
    {
        // Prepare data for renderer
        $data    = parent::getLayoutData();
        $tmpl    = null;
        $control = $this->name;

        try {
            $tmpl  = $this->loadSubForm();
            $forms = $this->loadSubFormData($tmpl);
        } catch (Exception $e) {
            return $e->getMessage();
        }

        $data['tmpl']      = $tmpl;
        $data['forms']     = $forms;
        $data['min']       = $this->min;
        $data['max']       = $this->max;
        $data['control']   = $control;
        $data['buttons']   = $this->buttons;
        $data['buttonsRow']   = $this->buttonsRow;
        $data['fieldname'] = $this->fieldname;
        $data['groupByFieldset'] = $this->groupByFieldset;

        /**
         * For each rendering process of a subform element, we want to have a
         * separate unique subform id present to could distinguish the eventhandlers
         * regarding adding/moving/removing rows from nested subforms from their parents.
         */
        static $unique_subform_id = 0;
        $data['unique_subform_id'] = ('sr-' . ($unique_subform_id++));

        // Prepare renderer
        $renderer = $this->getRenderer($this->layout);

        // Allow to define some JLayout options as attribute of the element
        if ($this->element['component']) {
            $renderer->setComponent((string) $this->element['component']);
        }

        if ($this->element['client']) {
            $renderer->setClient((string) $this->element['client']);
        }

        // Render
        $html = $renderer->render($data);

        // Add hidden input on front of the subform inputs, in multiple mode
        // for allow to submit an empty value
        if ($this->multiple) {
            $html = '<input name="' . $this->name . '" type="hidden" value="" />' . $html;
        }

        return $html;
    }

    /**
     * Binds given data to the subform and its elements.
     *
     * @param   Form  &$subForm  Form instance of the subform.
     *
     * @return  Form[]  Array of Form instances for the rows.
     *
     * @since   3.9.7
     */
    protected function loadSubFormData(Form &$subForm)
    {
        $value = $this->value ? (array) $this->value : [];

        // Simple form, just bind the data and return one row.
        if (!$this->multiple) {
            $subForm->bind($value);
            return [$subForm];
        }

        // Multiple rows possible: Construct array and bind values to their respective forms.
        $forms = [];
        $value = array_values($value);

        // Show as many rows as we have values, but at least min and at most max.
        $c = max($this->min, min(count($value), $this->max));

        for ($i = 0; $i < $c; $i++) {
            $control  = $this->name . '[' . $this->fieldname . $i . ']';
            $itemForm = Form::getInstance($subForm->getName() . $i, $this->formsource, ['control' => $control]);

            if (!empty($value[$i])) {
                $itemForm->bind($value[$i]);
            }

            $forms[] = $itemForm;
        }

        return $forms;
    }
}

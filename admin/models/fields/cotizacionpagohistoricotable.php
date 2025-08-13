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

JFormHelper::loadFieldClass('list');

/**
 * CotizacionPagoHistorico Form Field class for the Sabullvial component
 *
 * @since  0.0.1
 */
class JFormFieldCotizacionPagoHistoricoTable extends JFormFieldList
{
    /**
     * The field type.
     *
     * @var         string
     */
    protected $type = 'CotizacionPagoHistoricoTable';

    /**
     * Layout to render
     *
     * @var   string
     * @since 3.5
     */
    protected $layout = 'sabullvial.form.field.cotizacionhistoricotable';

    /**
     * The id_cotizacion.
     *
     * @var integer
     */
    protected $id_cotizacion = 0;


    /**
     * Method to attach a JForm object to the field.
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value. This acts as as an array container for the field.
     *                                       For example if the field has name="foo" and the group value is set to "bar" then the
     *                                       full field name would end up being "bar[foo]".
     *
     * @return  boolean  True on success.
     *
     * @since   1.7.0
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $result  = parent::setup($element, $value, $group);

        if ($result === false) {
            return false;
        }

        $attributes = ['id_cotizacion'];

        foreach ($attributes as $attributeName) {
            $this->__set($attributeName, $element[$attributeName]);
        }

        return true;
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
            case 'id_cotizacion':
                $this->$name = (int) $value;
                $this->element[$name] = (int) $value;
                break;

            default:
                parent::__set($name, $value);
        }
    }

    /**
     * Method to get a list of options for a list input.
     *
     * @return  array  An array of JHtml options.
     */
    protected function getOptions()
    {
        $idCotizacion = (int)$this->getAttribute('id_cotizacion', 0);
        if (!($idCotizacion > 0)) {
            return [];
        }

        $db    = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('cph.id, ecp.nombre estadocotizacion, ecp.color estadocotizacion_bg_color')
            ->select('ecp.color_texto estadocotizacion_color, cph.created, cph.created_by_alias')
            ->from('#__sabullvial_cotizacionpagohistorico cph')
            ->leftJoin('#__sabullvial_estadocotizacionpago ecp ON (ecp.id = cph.id_estadocotizacionpago)')
            ->where('cph.id_cotizacion = ' . $idCotizacion)
            ->order('cph.created DESC');

        $db->setQuery((string) $query);
        return $db->loadObjectList();
    }

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   1.7.0
     */
    protected function getInput()
    {
        return $this->getRenderer($this->layout)->render($this->getLayoutData());
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

        // Initialize some field attributes.
        $maxLength    = !empty($this->maxLength) ? ' maxlength="' . $this->maxLength . '"' : '';
        $inputmode    = !empty($this->inputmode) ? ' inputmode="' . $this->inputmode . '"' : '';
        $dirname      = !empty($this->dirname) ? ' dirname="' . $this->dirname . '"' : '';
        $style		  = !empty((string)$this->element['style']) ? ' style="'.(string)$this->element['style'].'"' : '';

        /* Get the field options for the datalist.
            Note: getSuggestions() is deprecated and will be changed to getOptions() with 4.0. */
        $options  = (array) $this->getOptions();

        $extraData = [
            'maxLength' => $maxLength,
            'pattern'   => $this->pattern,
            'inputmode' => $inputmode,
            'dirname'   => $dirname,
            'options'   => $options,
            'style'		=> $style
        ];

        return array_merge($data, $extraData);
    }
}

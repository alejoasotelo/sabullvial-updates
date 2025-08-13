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
 * CotizacionHistorico Form Field class for the Sabullvial component
 *
 * @since  0.0.1
 */
class JFormFieldCotizacionTangoHistoricoTable extends JFormFieldList
{
    /**
     * The field type.
     *
     * @var         string
     */
    protected $type = 'CotizacionTangoHistoricoTable';

    /**
     * Layout to render
     *
     * @var   string
     * @since 3.5
     */
    protected $layout = 'sabullvial.form.field.cotizacionhistoricotable';

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
        $query->select('ch.*');
        $query->from('#__sabullvial_cotizaciontangohistorico ch')
            ->where('ch.id_cotizacion = ' . $idCotizacion)
            ->order('ch.created DESC');
        $db->setQuery($query);
        $items = $db->loadObjectList();

        foreach ($items as &$item) {
            $estado = JFormFieldEstadoCotizacionTango::findById($item->id_estado_tango);
            $item->estadocotizacion = $estado['text'];
            $item->estadocotizacion_bg_color = $estado['background_color'];
            $item->estadocotizacion_color = $estado['color'];
        }

        return $items;
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

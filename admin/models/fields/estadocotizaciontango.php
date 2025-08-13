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

require_once JPATH_COMPONENT_ADMINISTRATOR . '/tables/cotizacion.php';

JFormHelper::loadFieldClass('list');

/**
 * EstadoCotizacion Form Field class for the Sabullvial component
 *
 * @since  0.0.1
 */
class JFormFieldEstadoCotizacionTango extends JFormFieldList
{
    /**
     * The field type.
     *
     * @var         string
     */
    protected $type = 'EstadoCotizacionTango';

    /**
     * Undocumented variable
     *
     * @var array
     */
    protected $exclude = [];

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

        if ($result != true) {
            return $result;
        }

        $exclude = (string) $this->element['exclude'];

        if (!empty($exclude)) {
            $exclude = explode(',', trim($exclude));
            $this->exclude = $exclude;
        }

        return $result;
    }

    public static function getItems()
    {
        return [
            [
                'id' => SabullvialTableCotizacion::ESTADO_TANGO_SIN_ESTADO,
                'text' => Text::_('COM_SABULLVIAL_ESTADO_COTIZACION_TANGO_SIN_ESTADO'),
                'color' => '#ffffff',
                'background_color' => '#474747'
            ],
            [
                'id' => SabullvialTableCotizacion::ESTADO_TANGO_SRL,
                'text' => Text::_('COM_SABULLVIAL_ESTADO_COTIZACION_TANGO_SRL'),
                'color' => '#ffffff',
                'background_color' => '#ff9900'
            ],
            [
                'id' => SabullvialTableCotizacion::ESTADO_TANGO_AUTOMATICA,
                'text' => Text::_('COM_SABULLVIAL_ESTADO_COTIZACION_TANGO_FACTURACION_AUTOMATICA'),
                'color' => '#ffffff',
                'background_color' => '#e60ef5'
            ],
            [
                'id' => SabullvialTableCotizacion::ESTADO_TANGO_PRUEBA,
                'text' => Text::_('COM_SABULLVIAL_ESTADO_COTIZACION_TANGO_PRUEBA'),
                'color' => '#ffffff',
                'background_color' => '#31708f'
            ],
            [
                'id' => SabullvialTableCotizacion::ESTADO_TANGO_SINCRONIZADO,
                'text' => Text::_('COM_SABULLVIAL_ESTADO_COTIZACION_TANGO_SINCRONIZADO'),
                'color' => '#ffffff',
                'background_color' => '#46a546'
            ],
            [
                'id' => SabullvialTableCotizacion::ESTADO_TANGO_ORDEN_DE_TRABAJO_FACTURADA,
                'text' => Text::_('COM_SABULLVIAL_ESTADO_COTIZACION_TANGO_ORDEN_DE_TRABAJO_FACTURADA'),
                'color' => '#ffffff',
                'background_color' => '#f89406'
            ],
        ];
    }

    public static function getAll()
    {
        return self::getItems();
    }

    /**
     * Method to get a list of options for a list input.
     *
     * @return  array  An array of JHtml options.
     */
    protected function getOptions()
    {
        $items  = self::getItems();

        usort($items, function ($a, $b) {
            return strcmp($a['text'], $b['text']);
        });

        foreach ($items as $item) {
            $continue = false;
            foreach ($this->exclude as $idExcluded) {
                if ($item['id'] == $idExcluded) {
                    $continue = true;
                    break;
                }
            }

            if ($continue) {
                continue;
            }

            $options[] = JHtml::_('select.option', $item['id'], $item['text']);
        }

        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }

    public static function findById($id)
    {
        $items = self::getItems();

        foreach ($items as $item) {
            if ($item['id'] == $id) {
                return $item;
            }
        }

        return false;
    }
}

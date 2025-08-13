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

if (!class_exists('SabullvialHelper')) {
    JLoader::register('SabullvialHelper', JPATH_ADMINISTRATOR . '/components/com_sabullvial/helpers/sabullvial.php');
}

JFormHelper::loadFieldClass('list');

/**
 * Cotizacion Form Field class for the Sabullvial component
 *
 * @since  0.0.1
 */
class JFormFieldCondicionVenta extends JFormFieldList
{
    /**
     * The field type.
     *
     * @var         string
     */
    protected $type = 'CondicionVenta';

    protected function getOptions()
    {
        $include = !empty($this->element['include']) ? explode(',', $this->element['include']) : [];

        $db    = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('COND_VTA id, DESC_COND name')
            ->from($db->quoteName('SIT_CONDICIONES_VENTA', 'ap'))
            ->order('ID_SIT_CONDICIONES_VENTA, dias');

        $vendedor = SabullvialHelper::getVendedor();
        if ($vendedor->get('tipo') != 'A') {
            $condicionesVenta = $vendedor->get('condicionesDeVenta');
            if (!empty($condicionesVenta) && !SabullvialHelper::hasAllCondiciones($condicionesVenta)) {
                $condicionesVenta .= !empty($include) ? ',' . implode(',', $include) : '';
                $query->where('COND_VTA IN (' . $condicionesVenta . ')');
            }
        }

        $db->setQuery((string) $query);
        $items = $db->loadObjectList();
        $options  = [];

        if ($items) {
            foreach ($items as $item) {
                $options[] = JHtml::_('select.option', $item->id, $item->name);
            }
        }

        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }
}

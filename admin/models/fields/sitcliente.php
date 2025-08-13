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
 * Cotizacion Form Field class for the Sabullvial component
 *
 * @since  0.0.1
 */
class JFormFieldSitcliente extends JFormFieldList
{
    /**
     * The field type.
     *
     * @var         string
     */
    protected $type = 'SitCliente';

    /**
     * Method to get a list of options for a list input.
     *
     * @return  array  An array of JHtml options.
     */
    protected function getOptions()
    {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('DISTINCT c.COD_CLIENT id, c.RAZON_SOCI name')
            ->from($db->qn('SIT_CLIENTES', 'c'))
            ->order('name');

        $onlyClientsInRemitos =  (isset($this->element['in_remitos']) && (string) $this->element['in_remitos'] == 'true');
        if ($onlyClientsInRemitos) {
            $query->innerJoin($db->qn('SIT_PEDIDOS_REMITOS', 'r') . ' ON (r.COD_CLIENT = c.COD_CLIENT)');
        }

        $onlyClientesDelVendedor =  (isset($this->element['clientes_vendedor']) && (string) $this->element['clientes_vendedor'] == 'true');
        $vendedor = SabullvialHelper::getVendedor();
        if ($onlyClientesDelVendedor && $vendedor->get('tipo', 'V') == 'V' && !empty($vendedor->get('codigo', ''))) {
            // Si el usuario es de tipo Vendedor y tiene un código, filtro por código.
            $query->where('c.COD_VENDED = ' . $db->q($vendedor->get('codigo')));
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

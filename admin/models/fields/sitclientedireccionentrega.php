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
 * Cliente Form Field class for the Sabullvial component
 *
 * @since  0.0.1
 */
class JFormFieldSitClienteDireccionEntrega extends JFormFieldList
{
    /**
     * The field type.
     *
     * @var         string
     */
    protected $type = 'SitClienteDireccionEntrega';

    /**
     * Method to get a list of options for a list input.
     *
     * @return  array  An array of JHtml options.
     */
    protected function getOptions()
    {
        $codigoCliente = $this->getAttribute('id_cliente', '');
        if (empty($codigoCliente)) {
            return [];
        }

        $db    = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('ID_DIRECCION_ENTREGA id, DIR_ENTREGA name')
            ->from($db->quoteName('SIT_CLIENTES_DIRECCION_ENTREGA'))
            ->where('COD_CLIENT = ' . $db->q($codigoCliente))
            ->order('HABITUAL DESC, DIR_ENTREGA ASC');

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

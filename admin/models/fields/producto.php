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
 * Producto Form Field class for the Sabullvial component
 *
 * @since  0.0.1
 */
class JFormFieldProducto extends JFormFieldList
{
    /**
     * The field type.
     *
     * @var         string
     */
    protected $type = 'Producto';

    /**
     * Method to get a list of options for a list input.
     *
     * @return  array  An array of JHtml options.
     */
    protected function getOptions()
    {
        $params = JComponentHelper::getParams('com_sabullvial');
        $dbName = $params->get('database_name_tango');

        // Initialize variables.
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        // Create the base select statement.
        $query->select('pm.*')
            ->from($db->qn($dbName . '.producto_medidas', 'pm'));

        $query->select('p.id_categoria, p.id_tipo, p.id_marca, p.nombre AS modelo, p.descripcion, p.velocidades, p.beneficios, p.caracteristicas, p.ver_precio')
            ->leftJoin($db->qn($dbName . '.productos', 'p') . ' ON (p.id = pm.id_producto)');

        $query->select('c1.nombre AS categoria, c1.id_subcate')
            ->leftJoin($db->qn($dbName . '.categorias', 'c1') . ' ON (p.id_categoria = c1.id)');

        $query->select('c2.nombre AS subcate')
            ->leftJoin($db->qn($dbName . '.categorias', 'c2') . ' ON (c1.id_subcate = c2.id)');

        $query->select('pt.nombre AS tipo')
            ->leftJoin($db->qn($dbName . '.producto_tipos', 'pt') . ' ON (pt.id = p.id_tipo)');

        $query->leftJoin($db->qn($dbName . '.marcas', 'm') . ' ON (p.id_marca = m.id)');

        $db->setQuery((string) $query);
        $items = $db->loadObjectList();
        $options  = [];

        if ($items) {
            foreach ($items as $item) {
                $options[] = JHtml::_('select.option', $item->id, $item->codigo_sap . ' | ' . $item->nombre);
            }
        }

        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }
}

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

JFormHelper::loadFieldClass('groupedlist');

/**
 * Cotizacion Form Field class for the Sabullvial component
 *
 * @since  0.0.1
 */
class JFormFieldCondicionVentaGrupo extends JFormFieldGroupedList
{
    /**
     * The field type.
     *
     * @var         string
     */
    protected $type = 'CondicionVentaGrupo';

    protected function getGroups()
    {
        /*
        SELECT
            *, @curGroup := IF(DIAS > @prevDias, @curGroup, @curGroup+1) AS value_grupo,
            IF(@curGroup < 2, CONCAT(@curGroup+1, 'A'), CHR(63+@curGroup)) grupo,
             @prevDias := DIAS
        FROM SIT_CONDICIONES_VENTA, (SELECT @curGroup := 0, @prevDias := -1) AS vars ORDER BY ID_SIT_CONDICIONES_VENTA ASC

        */

        /*$query->select('COND_VTA id, DESC_COND nombre, DIAS dias')
            ->select('@curGroup := IF(DIAS > @prevDias, @curGroup, @curGroup+1)')
            ->select('IF(@curGroup < 2, CONCAT(@curGroup+1, 'A'), CHR(63+@curGroup)) grupo')
            ->select('@prevDias := DIAS')
            ->from('SIT_CONDICIONES_VENTA, (SELECT @curGroup := 0, @prevDias := -1) AS vars ')
            ->order('grupo, dias');*/

        //$params = JComponentHelper::getParams('com_sabullvial');
        //$dbName = $params->get('database_name_tango');

        $vendedor = SabullvialHelper::getVendedor();

        $db    = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('id, nombre, dias, grupo');
        $query->from($dbName . '.condiciones_venta')
            ->where('borrado = 0 AND visible = 1')
            ->order('grupo, dias');

        if ($vendedor->get('tipo') != 'A') {
            if ($vendedor->get('condicionesDeVenta')) {
                $query->where('COND_VTA IN (' . $vendedor->get('condicionesDeVenta') . ')');
            }
        }

        $db->setQuery($query);
        $items = $db->loadObjectList();
        $grupos = [];

        if ($items) {
            foreach ($items as $item) {
                if (!isset($grupos[$item->grupo])) {
                    $grupos[$item->grupo] = [];
                }
                $grupos[$item->grupo][] = JHtml::_('select.option', $item->id, $item->nombre);
            }
        }

        $grupos = array_merge(parent::getGroups(), $grupos);

        return $grupos;
    }
}

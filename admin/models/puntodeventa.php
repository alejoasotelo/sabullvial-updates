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

/**
 * PuntoDeVenta Model
 *
 * @since  0.0.1
 */
class SabullvialModelPuntoDeVenta extends JModelAdmin
{
    /**
     * Method to get a table object, load it if necessary.
     *
     * @param   string  $type    The table name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  JTable  A JTable object
     *
     * @since   1.6
     */
    public function getTable($type = 'PuntoDeVenta', $prefix = 'SabullvialTable', $config = [])
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to get the record form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  mixed    A JForm object on success, false on failure
     *
     * @since   1.6
     */
    public function getForm($data = [], $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm(
            'com_sabullvial.puntodeventa',
            'puntodeventa',
            [
                'control' => 'jform',
                'load_data' => $loadData
            ]
        );

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed  The data for the form.
     *
     * @since   1.6
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState(
            'com_sabullvial.edit.puntodeventa.data',
            []
        );

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    /**
     * Undocumented function
     *
     * @param [type] $codCliente
     * @return object
     */
    public function findCliente($codCliente)
    {
        // Initialize variables.
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('c.cod_client id, c.cod_client codcli, c.cod_client, c.razon_soci razon_social')
            ->select('c.cuit, c.saldo_cc saldo, c.PROMEDIO_ULT_REC, c.INHABILITADO, IF(c.INHABILITADO = "N", 1, 0) habilitado')
            ->select('c.COD_VENDED codigo_vendedor')
            ->from($db->quoteName('SIT_CLIENTES', 'c'));

        $query->select('cv.DESC_COND condicion_venta, cv.COND_VTA id_condicion_venta')
            ->leftJoin($db->quoteName('SIT_CONDICIONES_VENTA', 'cv') . ' ON (c.cond_vta = cv.COND_VTA)');

        $query->where('c.cod_client = ' . $db->q($codCliente));


        $db->setQuery($query);

        return $db->loadObject();
    }
}

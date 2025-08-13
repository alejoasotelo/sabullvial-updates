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
 * Producto Table class
 *
 * @since  0.0.1
 */
class SabullvialTableSitClientes extends JTable
{
    /**
     * Constructor
     *
     * @param   JDatabaseDriver  &$db  A database connector object
     */
    public function __construct(&$db)
    {
        //$params = JComponentHelper::getParams('com_sabullvial');
        //$dbName = $params->get('database_name_tango');
        parent::__construct('SIT_CLIENTES', 'ID_SIT_CLIENTES', $db);

        $this->setColumnAlias('RAZON_SOCI', 'razon_social');
        $this->setColumnAlias('SALDO_CC', 'saldo');
        $this->setColumnAlias('COND_VTA', 'id_cond_venta');
        $this->setColumnAlias('COND_VTA', 'cond_vta');
        $this->setColumnAlias('COD_CLIENT', 'cod_client');
        $this->setColumnAlias('COD_CLIENT', 'codcli');
    }

    /**
     * Carga un cliente por el código del cliente.
     *
     * @param string $codCliente
     * @return SabullvialTableSitClientes
     */
    public function loadByCodCliente($codCliente)
    {
        return $this->load(['COD_CLIENT' => $codCliente]);
    }

    /**
     * Devvuelve true si el cliente tiene la condición de venta indicada
     *
     * @param integer $idCondVenta
     * @return boolean
     */
    public function hasCondicionDeVenta($idCondVenta)
    {
        return (int)$this->COND_VTA == $idCondVenta;
    }
}

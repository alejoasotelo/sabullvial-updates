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

use Joomla\CMS\Table\Table;

/**
 * Producto Table class
 *
 * @since  0.0.1
 */
class SabullvialTableSitClientesDireccionEntrega extends Table
{
    /**
     * Constructor
     *
     * @param   JDatabaseDriver  &$db  A database connector object
     */
    public function __construct(&$db)
    {
        parent::__construct('SIT_CLIENTES_DIRECCION_ENTREGA', 'ID_SIT_CLIENTES_DIRECCION_ENTREGA', $db);
    }

    /**
     * Carga un cliente por el código del cliente.
     *
     * @param string $codCliente
     * @return Array
     */
    public function loadByIdCliente($idSitCliente)
    {
        // Initialise the query.
        $query = $this->_db->getQuery(true)
            ->select('d.*')
            ->from($this->_tbl . ' d')
            ->leftJoin('SIT_CLIENTES c ON (c.COD_CLIENT = d.COD_CLIENT)')
            ->where($this->_db->quoteName('c.ID_SIT_CLIENTES') . ' = ' . (int)$idSitCliente);

        $this->_db->setQuery($query);

        return $this->_db->loadAssocList();
    }

    /**
     * Carga un cliente por el código del cliente.
     *
     * @param string $codCliente
     * @return Array
     */
    public function listByCodCliente($codigoCliente)
    {
        // Initialise the query.
        $query = $this->_db->getQuery(true)
            ->select('d.*')
            ->from($this->_tbl . ' d')
            ->leftJoin('SIT_CLIENTES c ON (c.COD_CLIENT = d.COD_CLIENT)')
            ->where($this->_db->quoteName('c.COD_CLIENT') . ' = ' . $this->_db->q($codigoCliente))
            ->order('HABITUAL DESC, DIR_ENTREGA ASC');

        $this->_db->setQuery($query);

        return $this->_db->loadAssocList();
    }

    /**
     * Carga un cliente por el código del cliente.
     *
     * @param string $codCliente
     * @return Array
     */
    public function findByIdDireccionEntrega($idDireccion)
    {
        // Initialise the query.
        $query = $this->_db->getQuery(true)
            ->select('d.*')
            ->from($this->_tbl . ' d')
            ->where($this->_db->quoteName('d.ID_DIRECCION_ENTREGA') . ' = ' . $this->_db->q($idDireccion))
            ->order('HABITUAL DESC, DIR_ENTREGA ASC');

        $this->_db->setQuery($query);

        return $this->_db->loadObject();
    }
}

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
class SabullvialTableBullvialSitClientes extends JTable
{
    /**
     * Constructor
     *
     * @param   JDatabaseDriver  &$db  A database connector object
     */
    public function __construct(&$db)
    {
        $params = JComponentHelper::getParams('com_sabullvial');
        $dbName = $params->get('database_name_tango');
        parent::__construct($dbName  . '.SIT_CLIENTES', 'ID_SIT_CLIENTES', $db);
    }

    public function loadByCodigoCliente($codigoCliente)
    {
        //$params = JComponentHelper::getParams('com_sabullvial');
        //$dbName = $params->get('database_name_tango');

        // Initialise the query.
        $query = $this->_db->getQuery(true)
            ->select('c.*')
            ->from($this->_tbl . ' c')
            ->where($this->_db->quoteName('c.COD_CLIENT') . ' = ' . $this->_db->q($codigoCliente));

        $this->_db->setQuery($query);

        return $this->_db->loadAssoc();
    }
}

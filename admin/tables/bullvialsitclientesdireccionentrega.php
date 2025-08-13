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
class SabullvialTableBullvialSitClientesDireccionEntrega extends JTable
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
        parent::__construct($dbName  . '.SIT_CLIENTES_direccion_entrega', 'id_sit_clientes_direccion_entrega', $db);
    }

    public function loadByCodigoCliente($codigoCliente, $params = [])
    {
        //$params = JComponentHelper::getParams('com_sabullvial');
        //$dbName = $params->get('database_name_tango');

        // Initialise the query.
        $query = $this->_db->getQuery(true)
            ->select('cd.*')
            ->from($this->_tbl . ' cd')
            ->where($this->_db->quoteName('cd.cod_client') . ' = ' . $codigoCliente)
            //->order('HABITUAL DESC, DIR_ENTREGA ASC');
            ->order('cd.id_sit_clientes_direccion_entrega ASC');


        if (count($params) > 0) {
            $fields = array_keys($this->getProperties());

            foreach ($params as $field => $value) {
                // Check that $field is in the table.
                if (!in_array($field, $fields)) {
                    throw new \UnexpectedValueException(sprintf('Missing field in database: %s &#160; %s.', get_class($this), $field));
                }

                // Add the search tuple to the query.
                $query->where($this->_db->quoteName($field) . ' = ' . $this->_db->quote($value));
            }
        }

        $this->_db->setQuery($query);

        return $this->_db->loadAssocList();
    }
}

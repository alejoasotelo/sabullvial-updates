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
class SabullvialTableBullvialTransportes extends JTable
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
        parent::__construct($dbName  . '.SIT_TRANSPORTES', 'ID_SIT_TRANSPORTES', $db);
    }

    public function getAll()
    {
        // Initialise the query.
        $query = $this->_db->getQuery(true)
            ->select('*, COD_TRANSP id, concat("(", COD_TRANSP, ")", " ", DOM_TRANS, " - ", NOMBRE_TRA) name')
            ->from($this->_tbl);

        $this->_db->setQuery($query);

        return $this->_db->loadObjectList();
    }
}

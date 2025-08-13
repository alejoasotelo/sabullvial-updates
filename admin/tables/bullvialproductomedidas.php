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
class SabullvialTableBullvialProductoMedidas extends JTable
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
        parent::__construct($dbName  . '.producto_medidas', 'id', $db);
    }

    public function getPrecio()
    {
    }
}

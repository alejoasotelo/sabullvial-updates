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
class SabullvialTableXXXConfig extends JTable
{
    /**
     * Constructor
     *
     * @param   JDatabaseDriver  &$db  A database connector object
     */
    public function __construct(&$db)
    {
        parent::__construct('xxx_conf', 'id', $db);
    }

    public static function getValue($key, $defValue = null)
    {
        $db = JFactory::getDbo();
        // Initialise the query.
        $query = $db->getQuery(true)
            ->select('a.valor')
            ->from('xxx_conf a')
            ->where($db->qn('id') . ' = ' . $db->q($key));

        $db->setQuery($query);

        $result = $db->loadResult();

        return $result ? $result : $defValue;
    }

    public static function getValues($keys, $defValues = null)
    {
        if (count($keys) !== count($defValues)) {
            $return = array_fill(0, count($keys), null);
            return $return;
        }

        $db = JFactory::getDbo();
        // Initialise the query.
        $query = $db->getQuery(true)
            ->select('a.id, a.valor')
            ->from('xxx_conf a')
            ->where($db->qn('id') . ' IN (' . implode(',', $db->q($keys)) . ')');

        $db->setQuery($query);

        $result = $db->loadObjectList('id');

        if ($result) {
            $values = [];

            foreach ($keys as $i => $key) {
                $values[$key] = isset($result[$key]) ? $result[$key]->valor : $defValues[$i];
            }

            return $values;
        }

        return $defValues;
    }
}

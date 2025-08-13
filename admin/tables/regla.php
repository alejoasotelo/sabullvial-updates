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
 * Regla Table class
 *
 * @since  0.0.1
 */
class SabullvialTableRegla extends JTable
{
    /**
     * Constructor
     *
     * @param   JDatabaseDriver  &$db  A database connector object
     */
    public function __construct(&$db)
    {
        parent::__construct('#__sabullvial_regla', 'id', $db);
    }
    /**
     * Stores a contact.
     *
     * @param   boolean  $updateNulls  True to update fields even if they are null.
     *
     * @return  boolean  True on success, false on failure.
     *
     * @since   1.6
     */
    public function store($updateNulls = true)
    {
        $date   = JFactory::getDate()->toSql();
        $userId = JFactory::getUser()->id;

        // Set created date if not set.
        if (!(int) $this->created) {
            $this->created = $date;
        }

        if ($this->id) {
            // Existing item
            $this->modified_by = $userId;
            $this->modified    = $date;
        } else {
            // Field created_by field can be set by the user, so we don't touch it if it's set.
            if (empty($this->created_by)) {
                $this->created_by = $userId;
            }

            if (empty($this->created_by_alias)) {
                $this->created_by_alias = JFactory::getUser()->username;
            }

            if (!(int) $this->modified) {
                $this->modified = $date;
            }

            if (empty($this->modified_by)) {
                $this->modified_by = $userId;
            }
        }

        return parent::store($updateNulls);
    }

    public function find($keys)
    {
        if (empty($keys)) {
            $empty = true;
            $keys  = [];

            // If empty, use the value of the current key
            foreach ($this->_tbl_keys as $key) {
                $empty      = $empty && empty($this->$key);
                $keys[$key] = $this->$key;
            }

            // If empty primary key there's is no need to load anything
            if ($empty) {
                return true;
            }
        } elseif (!is_array($keys)) {
            // Load by primary key.
            $keyCount = count($this->_tbl_keys);

            if ($keyCount) {
                if ($keyCount > 1) {
                    throw new \InvalidArgumentException('Table has multiple primary keys specified, only one primary key value provided.');
                }

                $keys = [$this->getKeyName() => $keys];
            } else {
                throw new \RuntimeException('No table keys defined.');
            }
        }

        // Initialise the query.
        $query = $this->_db->getQuery(true)
            ->select('*')
            ->from($this->_tbl);
        $fields = array_keys($this->getProperties());

        foreach ($keys as $field => $value) {
            // Check that $field is in the table.
            if (!in_array($field, $fields)) {
                throw new \UnexpectedValueException(sprintf('Missing field in database: %s &#160; %s.', get_class($this), $field));
            }

            // Add the search tuple to the query.
            $query->where($this->_db->quoteName($field) . ' = ' . $this->_db->quote($value));
        }

        $this->_db->setQuery($query);

        try {
            $rows = $this->_db->loadObjectList();
        } catch (Exception $e) {
            $rows = false;
        }

        return $rows;
    }
}

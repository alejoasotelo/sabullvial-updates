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
 * TareaUsuario Table class
 *
 * @since  0.0.1
 */
class SabullvialTableTareaUsuario extends JTable
{
    /**
     * Constructor
     *
     * @param   JDatabaseDriver  &$db  A database connector object
     */
    public function __construct(&$db)
    {
        parent::__construct('#__sabullvial_tareausuario', 'id', $db);
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

    public function loadByIdTarea($idTarea)
    {
        // Initialise the query.
        $query = $this->_db->getQuery(true)
            ->select('*')
            ->from($this->_tbl)
            ->where($this->_db->quoteName('id_tarea') . ' = ' . (int)$idTarea);

        $this->_db->setQuery($query);

        return $this->_db->loadAssocList();
    }

    public function deleteByIdTareaAndUserId($idTarea, $userId)
    {
        // Initialise the query.
        $query = $this->_db->getQuery(true)
            ->delete($this->_tbl)
            ->where($this->_db->quoteName('id_tarea') . ' = ' . (int)$idTarea)
            ->where($this->_db->quoteName('user_id') . ' = ' . (int)$userId);

        $this->_db->setQuery($query);

        return $this->_db->execute();
    }

    public function deleteByIdTarea($idTarea)
    {
        // Initialise the query.
        $query = $this->_db->getQuery(true)
            ->delete($this->_tbl)
            ->where($this->_db->quoteName('id_tarea') . ' = ' . (int)$idTarea);

        $this->_db->setQuery($query);

        return $this->_db->execute();
    }
}

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
 * EstadoRemito Table class
 *
 * @since  0.0.1
 */
class SabullvialTableEstadoRemito extends JTable
{
    /**
     * Constructor
     *
     * @param   JDatabaseDriver  &$db  A database connector object
     */
    public function __construct(&$db)
    {
        parent::__construct('#__sabullvial_estadoremito', 'id', $db);
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

    public function findByName($name)
    {
        $result = $this->load(['nombre' => $name]);
        return $result ? $this : false;
    }

    public function getEntregadoMostrador()
    {
        // Initialise the query.
        $query = $this->_db->getQuery(true)
            ->select('*')
            ->from($this->_tbl)
            ->where('entregado_mostrador = 1');

        $this->_db->setQuery($query);

        return $this->_db->loadObject();
    }

    public function getEnTransito()
    {
        // Initialise the query.
        $query = $this->_db->getQuery(true)
            ->select('*')
            ->from($this->_tbl)
            ->where('transito = 1');

        $this->_db->setQuery($query);

        return $this->_db->loadObject();
    }

    public function getEnPreparacion()
    {
        // Initialise the query.
        $query = $this->_db->getQuery(true)
            ->select('*')
            ->from($this->_tbl)
            ->where('preparacion = 1');

        $this->_db->setQuery($query);

        return $this->_db->loadObject();
    }

    /**
     * Devuelve el estado en proceso
     *
     * @return object
     */
    public function getEnProceso()
    {
        // Initialise the query.
        $query = $this->_db->getQuery(true)
            ->select('*')
            ->from($this->_tbl)
            ->where('proceso = 1');

        $this->_db->setQuery($query);

        return $this->_db->loadObject();
    }

    public function getEntregado()
    {
        // Initialise the query.
        $query = $this->_db->getQuery(true)
            ->select('*')
            ->from($this->_tbl)
            ->where('entregado = 1');

        $this->_db->setQuery($query);

        return $this->_db->loadObject();
    }

    public function getEntregadoPorMostrador()
    {
        // Initialise the query.
        $query = $this->_db->getQuery(true)
            ->select('*')
            ->from($this->_tbl)
            ->where('entregado_mostrador = 1');

        $this->_db->setQuery($query);

        return $this->_db->loadObject();
    }

    public function getAll()
    {
        // Initialise the query.
        $query = $this->_db->getQuery(true)
            ->select('id, nombre, color, color_texto')
            ->from($this->_tbl);

        $this->_db->setQuery($query);

        return $this->_db->loadObjectList();
    }
}

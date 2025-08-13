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
 * Deposito Table class
 *
 * @since  0.0.1
 */
class SabullvialTableDeposito extends JTable
{
    /**
     * Constructor
     *
     * @param   JDatabaseDriver  &$db  A database connector object
     */
    public function __construct(&$db)
    {
        parent::__construct('#__sabullvial_deposito', 'id', $db);
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

    /**
     * Elimina depósitos solo si no tienen cotizaciones asociadas
     *
     * @param  mixed  $pk  An optional primary key value to delete. If not set the instance property value is used.
     *
     * @return  boolean  True on success, false on failure.
     */
    public function delete($pk = null)
    {
        $id = (!empty($pk)) ? $pk : $this->id;

        $query = $this->_db->getQuery(true)
            ->select('COUNT(*)')
            ->from($this->_db->qn('#__sabullvial_cotizacion'))
            ->where($this->_db->qn('id_deposito') . ' = ' . (int) $id);

        $this->_db->setQuery($query);

        if ($this->_db->loadResult() > 0) {
            $this->setError(JText::sprintf('COM_SABULLVIAL_DEPOSITO_ERROR_HAS_COTIZACIONES', $id));

            return false;
        }

        $result = parent::delete($pk);

        return $result;
    }

    public function getAll($published = true)
    {
        $query = $this->_db->getQuery(true)
            ->select('d.*')
            ->from($this->_db->qn($this->_tbl, 'd'))
            ->order('d.name ASC');

        if ($published) {
            $query->where('d.published = 1');
        }

        $this->_db->setQuery($query);

        return $this->_db->loadObjectList();
    }
}

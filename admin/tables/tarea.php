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
 * Tarea Table class
 *
 * @since  0.0.1
 */
class SabullvialTableTarea extends JTable
{
    public const TASK_TYPE_LLAMADA = 'llamada';
    public const TASK_TYPE_NOTIFICAR_POR_EMAIL = 'email';
    public const TASK_TYPE_APROBAR_CLIENTE = 'aprobar_cliente';
    public const TASK_TYPE_APROBAR_COTIZACION = 'aprobar_cotizacion';
    public const TASK_TYPE_ACTION = 'action';

    public const TASK_VALUE_ACTION_SELECT = 'select';
    public const TASK_VALUE_ACTION_APROBAR_COTIZACION = 'aprobar_cotizacion';
    public const TASK_VALUE_ACTION_APROBAR_CLIENTE = 'aprobar_cliente';

    /**
     * Constructor
     *
     * @param   JDatabaseDriver  &$db  A database connector object
     */
    public function __construct(&$db)
    {
        parent::__construct('#__sabullvial_tarea', 'id', $db);
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

    public function getUsuarios()
    {
        $table = JTable::getInstance('TareaUsuario', 'SabullvialTable');
        return $table->loadByIdTarea($this->id);
    }
}

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
 * EstadoCotizacion Table class
 *
 * @since  0.0.1
 */
class SabullvialTableEstadoCotizacion extends JTable
{
    public const ESTADO_REVISION_PENDIENTE = 0;
    public const ESTADO_REVISION_COMPLETA = 1;
    public const ESTADO_REVISION_COMPLETA_CON_FALTANTES = 2;
    public const ESTADO_REVISION_AUTOMATICA = 3;

    public const ESTADO_RECHAZADO = 1;
    public const ESTADO_RECHAZADO_POR_FORMA_DE_PAGO = 2;

    public const ESTADO_CANCELADO = 1;
    public const ESTADO_CANCELADO_POR_PRECIO = 2;
    public const ESTADO_CANCELADO_POR_STOCK = 3;

    protected $cache = [];

    /**
     * Constructor
     *
     * @param   JDatabaseDriver  &$db  A database connector object
     */
    public function __construct(&$db)
    {
        parent::__construct('#__sabullvial_estadocotizacion', 'id', $db);
    }

    /**
     * Overloaded bind function
     *
     * @param       array           named array
     * @return      null|string     null is operation was satisfactory, otherwise returns an error
     * @see JTable:bind
     * @since 1.5
     */
    public function bind($array, $ignore = '')
    {
        if (isset($array['params']) && is_array($array['params'])) {
            // Convert the params field to a string.
            $parameter = new JRegistry();
            $parameter->loadArray($array['params']);
            $array['params'] = (string)$parameter;
        }

        // Bind the rules.
        if (isset($array['rules']) && is_array($array['rules'])) {
            $rules = new JAccessRules($array['rules']);
            $this->setRules($rules);
        }

        return parent::bind($array, $ignore);
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
    public function store($updateNulls = false)
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

    public function getAll()
    {
        // Initialise the query.
        $query = $this->_db->getQuery(true)
            ->select('id, nombre, color')
            ->from($this->_tbl);

        $this->_db->setQuery($query);

        return $this->_db->loadObjectList();
    }

    public function getPendiente()
    {
        // Initialise the query.
        $query = $this->_db->getQuery(true)
            ->select('*')
            ->from($this->_tbl)
            ->where('pendiente = 1');

        $this->_db->setQuery($query);

        return $this->_db->loadObject();
    }

    public function getEstadoPendienteId()
    {
        return $this->getPendiente()->id;
    }

    /**
     * Devuelve los ids de los estados aprobados
     *
     * @return array
     */
    public function getEstadosAprobadosIds()
    {
        // Initialise the query.
        $query = $this->_db->getQuery(true)
            ->select('id')
            ->from($this->_tbl)
            ->where('aprobado = 1');

        $this->_db->setQuery($query);

        return $this->_db->loadColumn();
    }

    public function getEstadoAprobadoCompletoId()
    {
        // Initialise the query.
        $query = $this->_db->getQuery(true)
            ->select('id')
            ->from($this->_tbl)
            ->where('aprobado = 1 AND revisado = ' . self::ESTADO_REVISION_COMPLETA);

        $this->_db->setQuery($query);

        return $this->_db->loadResult();
    }

    public function getEstadoAprobadoConFaltantesId()
    {
        // Initialise the query.
        $query = $this->_db->getQuery(true)
            ->select('id')
            ->from($this->_tbl)
            ->where('aprobado = 1 AND revisado = ' . self::ESTADO_REVISION_COMPLETA_CON_FALTANTES);

        $this->_db->setQuery($query);

        return $this->_db->loadResult();
    }

    public function getEstadoAprobadoAutomaticoId()
    {
        // Initialise the query.
        $query = $this->_db->getQuery(true)
            ->select('id')
            ->from($this->_tbl)
            ->where('aprobado = 1 AND revisado = ' . self::ESTADO_REVISION_AUTOMATICA);

        $this->_db->setQuery($query);

        return $this->_db->loadResult();
    }

    public function getEstadoRechazadoPorFormaDePagoId()
    {
        // Initialise the query.
        $query = $this->_db->getQuery(true)
            ->select('id')
            ->from($this->_tbl)
            ->where('rechazado = ' . self::ESTADO_RECHAZADO_POR_FORMA_DE_PAGO);

        $this->_db->setQuery($query);

        return $this->_db->loadResult();
    }

    /**
     * Devuelve los ids de los estados rechazados
     *
     * @return array
     */
    public function getEstadosRechazadosIds()
    {
        $query = $this->_db->getQuery(true)
            ->select('id')
            ->from($this->_tbl)
            ->where('rechazado IN (' . self::ESTADO_RECHAZADO . ',' . self::ESTADO_RECHAZADO_POR_FORMA_DE_PAGO . ')');

        $this->_db->setQuery($query);

        return $this->_db->loadColumn();
    }

    /**
     * Devuelve los ids de los estados aprobados
     *
     * @return array
     */
    public function getEstadosCancelado()
    {
        static $cache = [];
        $storeId = 'estadosCancelado';
        if (isset($cache[$storeId])) {
            return $cache[$storeId];
        }

        // Initialise the query.
        $query = $this->_db->getQuery(true)
            ->select('*')
            ->from($this->_tbl)
            ->where('cancelado = 1')
            ->order('nombre');

        $user = JFactory::getUser();
        if (!$user->authorise('core.admin')) {
            $userAccessLevels = implode(',', $user->getAuthorisedViewLevels());
            $query->where('access IN (' . $userAccessLevels . ')');
        }

        $this->_db->setQuery($query);

        $cache[$storeId] = $this->_db->loadObjectList();

        return $cache[$storeId];
    }

    /**
     * Method to compute the default name of the asset.
     * The default name is in the form `table_name.id`
     * where id is the value of the primary key of the table.
     *
     * @return	string
     * @since	2.5
     */
    protected function _getAssetName()
    {
        $k = $this->_tbl_key;
        return 'com_sabullvial.estadocotizacion.' . (int) $this->$k;
    }
    /**
     * Method to return the title to use for the asset table.
     *
     * @return	string
     * @since	2.5
     */
    protected function _getAssetTitle()
    {
        return $this->nombre;
    }
    /**
     * Method to get the asset-parent-id of the item
     *
     * @return	int
     */
    protected function _getAssetParentId(?JTable $table = null, $id = null)
    {
        // We will retrieve the parent-asset from the Asset-table
        $assetParent = JTable::getInstance('Asset');
        // Default: if no asset-parent can be found we take the global asset
        $assetParentId = $assetParent->getRootId();

        // The item has the component as asset-parent
        $assetParent->loadByName('com_sabullvial');

        // Return the found asset-parent-id
        if ($assetParent->id) {
            $assetParentId = $assetParent->id;
        }

        return $assetParentId;
    }
}

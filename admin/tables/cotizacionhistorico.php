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

use Joomla\CMS\Table\Table;

/**
 * CotizacionHistorico Table class
 *
 * @since  0.0.1
 */
class SabullvialTableCotizacionHistorico extends Table
{
    /**
     * ID de la cotización
     *
     * @var int
     */
    public $id_cotizacion;

    /**
     * ID del estado de la cotización
     *
     * @var int
     */
    public $id_estadocotizacion;

    protected static $cache = [];

    /**
     * Constructor
     *
     * @param   JDatabaseDriver  &$db  A database connector object
     */
    public function __construct(&$db)
    {
        parent::__construct('#__sabullvial_cotizacionhistorico', 'id', $db);
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

    public function insertEstado($idCotizacion, $idEstadoCotizacion)
    {
        $data = [
            'id_cotizacion' => (int)$idCotizacion,
            'id_estadocotizacion' => (int)$idEstadoCotizacion
        ];

        $currentIdEstadoCotizacion = $this->getCurrentIdEstadoCotizacion($idCotizacion);

        if ($currentIdEstadoCotizacion == (int)$idEstadoCotizacion) {
            return false;
        }

        return $this->save($data);
    }

    /**
     * Devuelve el último estado de cotización del histórico.
     *
     * @param int $idCotizacion
     * @return int|boolean
     */
    public function getCurrentIdEstadoCotizacion($idCotizacion = null)
    {
        $idCotizacion = (int)(is_null($idCotizacion) ? $this->id_cotizacion : $idCotizacion);

        // Initialise the query.
        $query = $this->_db->getQuery(true)
            ->select('id_estadocotizacion')
            ->from($this->_tbl)
            ->where($this->_db->quoteName('id_cotizacion') . ' = ' . (int)$idCotizacion)
            ->order('created DESC');

        $this->_db->setQuery($query);

        $currentIdEstadoCotizacion = (int)$this->_db->loadResult();

        return $currentIdEstadoCotizacion;
    }

    /**
     * Devuelve el histórico de estados de cotización de una cotización.
     *
     * @param int $idCotizacion
     * @return array
     */
    public function getHistoricos($idCotizacion)
    {
        // Get a storage key.
        $store = 'getHistoricos-' . $idCotizacion;

        if (!isset(self::$cache[$store])) {
            $idCotizacion = (int)$idCotizacion;

            $db = $this->_db;

            // Initialise the query.
            $query = $db->getQuery(true)
                ->select('ch.*, ec.nombre AS nombre_estado')
                ->from($this->_tbl . ' AS ch')
                ->join('LEFT', '#__sabullvial_estadocotizacion AS ec ON ec.id = ch.id_estadocotizacion')
                ->where($db->quoteName('id_cotizacion') . ' = ' . (int)$idCotizacion)
                ->order('created DESC');

            $db->setQuery($query);

            self::$cache[$store] = $db->loadObjectList();
        }

        return self::$cache[$store];
    }
}

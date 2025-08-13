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
 * CotizacionTangoHistorico Table class
 *
 * @since  0.0.1
 */
class SabullvialTableCotizacionTangoHistorico extends JTable
{
    /**
     * Constructor
     *
     * @param   JDatabaseDriver  &$db  A database connector object
     */
    public function __construct(&$db)
    {
        parent::__construct('#__sabullvial_cotizaciontangohistorico', 'id', $db);
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

    public function insertEstado($idCotizacion, $idEstadoTangoCotizacion)
    {
        $data = [
            'id_cotizacion' => (int)$idCotizacion,
            'id_estado_tango' => (int)$idEstadoTangoCotizacion
        ];

        $currentIdEstadoTango = $this->getCurrentIdEstadoTango($idCotizacion);

        if ($currentIdEstadoTango == (int)$idEstadoTangoCotizacion) {
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
    public function getCurrentIdEstadoTango($idCotizacion = null)
    {
        $idCotizacion = (int)(is_null($idCotizacion) ? $this->id_cotizacion : $idCotizacion);

        // Initialise the query.
        $query = $this->_db->getQuery(true)
            ->select('id_estado_tango')
            ->from($this->_tbl)
            ->where($this->_db->quoteName('id_cotizacion') . ' = ' . (int)$idCotizacion)
            ->order('created DESC');

        $this->_db->setQuery($query);

        $currentIdEstadoTango = (int)$this->_db->loadResult();

        return $currentIdEstadoTango;
    }
}

<?php

/**
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or exit('Restricted access');

use Joomla\CMS\Table\Observer\ContentHistory as ContentHistoryObserver;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;

/**
 * Cotizacion Table class.
 *
 * @since  0.0.1
 */
class SabullvialTableCotizacion extends Table
{
    public const ESTADO_TANGO_SIN_ESTADO = 0;
    public const ESTADO_TANGO_SRL = 1;
    public const ESTADO_TANGO_AUTOMATICA = 5;
    public const ESTADO_TANGO_PRUEBA = 6;
    public const ESTADO_TANGO_SINCRONIZADO = 100;
    public const ESTADO_TANGO_ORDEN_DE_TRABAJO_FACTURADA = 101;

    public const ESTADOS_TANGO = [
        self::ESTADO_TANGO_SIN_ESTADO,
        self::ESTADO_TANGO_SRL,
        self::ESTADO_TANGO_AUTOMATICA,
        self::ESTADO_TANGO_PRUEBA,
        self::ESTADO_TANGO_SINCRONIZADO,
        self::ESTADO_TANGO_ORDEN_DE_TRABAJO_FACTURADA,
    ];

    /**
     * ID de la cotización.
     *
     * @var int
     */
    public $id;

    /**
     * ID del estado de la cotización.
     *
     * @var int
     */
    public $id_estadocotizacion;

    protected static $cache = [];

    /**
     * Constructor.
     *
     * @param JDatabaseDriver &$db A database connector object
     */
    public function __construct(&$db)
    {
        parent::__construct('#__sabullvial_cotizacion', 'id', $db);

        // SabullvialTableCotizacionDetalle::createObserver($this, array('typeAlias' => 'com_sabullvial.cotizacion'));
        JObserverMapper::addObserverClassToClass('JTableObserverContenthistory', 'SabullvialTableCotizacionDetalle', ['typeAlias' => 'com_sabullvial.cotizacion']);
        ContentHistoryObserver::createObserver($this, ['typeAlias' => 'com_sabullvial.cotizacion']);

        // JObserverMapper::addObserverClassToClass('JTableObserverContenthistory', 'SabullvialTableCotizacion', array('typeAlias' => 'com_sabullvial.cotizacion'));
    }

    /**
     * Stores a contact.
     *
     * @param bool $updateNulls true to update fields even if they are null
     *
     * @return bool true on success, false on failure
     *
     * @since   1.6
     */
    public function store($updateNulls = false)
    {
        $date = JFactory::getDate()->toSql();
        $userId = JFactory::getUser()->id;

        // Set created date if not set.
        if (!(int) $this->created) {
            $this->created = $date;
        }

        if ($this->id) {
            // Existing item
            $this->modified_by = $userId;
            $this->modified = $date;
        } else {
            if (empty($this->reference)) {
                $this->reference = self::generateReference();
            }

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

        $isStored = parent::store($updateNulls);

        if (!$isStored) {
            return false;
        }

        /** @var SabullvialTableCotizacionHistorico $cotizacionHistorico */
        $cotizacionHistorico = Table::getInstance('CotizacionHistorico', 'SabullvialTable');
        $cotizacionHistorico->insertEstado($this->id, $this->id_estadocotizacion);

        /** @var SabullvialTableCotizacionTangoHistorico $cotizacionHistorico */
        $cotizacionHistorico = Table::getInstance('CotizacionTangoHistorico', 'SabullvialTable');
        $cotizacionHistorico->insertEstado($this->id, $this->id_estado_tango);

        /** @var SabullvialTableCotizacionPagoHistorico $cotizacionHistorico */
        $cotizacionHistorico = Table::getInstance('CotizacionPagoHistorico', 'SabullvialTable');
        $cotizacionHistorico->insertEstado($this->id, $this->id_estadocotizacionpago);

        return $isStored;
    }

    public function getCotizacionDetalles()
    {
        /** @var SabullvialTableCotizacionDetalle $table */
        $table = JTable::getInstance('CotizacionDetalle', 'SabullvialTable');

        return $table->loadByIdCotizacion($this->id);
    }

    /**
     * Generate a unique reference for orders generated with the same cart id
     * This references, is useful for check payment.
     *
     * @return string
     */
    public static function generateReference()
    {
        return strtoupper(self::passwdGen(9));
    }

    public static function passwdGen($length = 8)
    {
        $length = (int) $length;

        if ($length <= 0) {
            return false;
        }

        $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $bytes = self::getBytes($length);
        $position = 0;
        $result = '';

        for ($i = 0; $i < $length; ++$i) {
            $position = ($position + ord($bytes[$i])) % strlen($str);
            $result .= $str[$position];
        }

        return $result;
    }

    /**
     * Random bytes generator.
     *
     * Limited to OpenSSL since 1.7.0.0
     *
     * @param int $length Desired length of random bytes
     *
     * @return bool|string Random bytes
     */
    public static function getBytes($length)
    {
        $length = (int) $length;

        if ($length <= 0) {
            return false;
        }

        $bytes = openssl_random_pseudo_bytes($length, $cryptoStrong);

        if (true === $cryptoStrong) {
            return $bytes;
        }

        return false;
    }

    /**
     * Method to compute the default name of the asset.
     * The default name is in the form `table_name.id`
     * where id is the value of the primary key of the table.
     *
     * @return string
     *
     * @since	2.5
     */
    protected function _getAssetName()
    {
        $k = $this->_tbl_key;

        return 'com_sabullvial.cotizacion.'.(int) $this->$k;
    }

    /**
     * Method to return the title to use for the asset table.
     *
     * @return string
     *
     * @since	2.5
     */
    protected function _getAssetTitle()
    {
        return $this->reference;
    }

    /**
     * Method to get the asset-parent-id of the item.
     *
     * @return int
     */
    protected function _getAssetParentId(?JTable $table = null, $id = null)
    {
        // return null;
        // We will retrieve the parent-asset from the Asset-table
        $assetParent = JTable::getInstance('Asset');
        // Default: if no asset-parent can be found we take the global asset
        $assetParentId = $assetParent->getRootId();

        // Find the parent-asset
        $assetParent->loadByName('com_sabullvial');

        // Return the found asset-parent-id
        if ($assetParent->id) {
            $assetParentId = $assetParent->id;
        }

        return $assetParentId;
    }

    public function changeEstadoCotizacion($pks = null, $state = 1)
    {
        return $this->changeColumnValue('id_estadocotizacion', $pks, $state);
    }

    public function changeEstadoTangoCotizacion($pks = null, $state = SabullvialTableCotizacion::ESTADO_TANGO_SIN_ESTADO)
    {
        return $this->changeColumnValue('id_estado_tango', $pks, $state);
    }

    public function changeEstadoCotizacionPago($pks = null, $state = null)
    {
        return $this->changeColumnValue('id_estadocotizacionpago', $pks, $state);
    }

    /**
     * Method to set the publishing state for a row or list of rows in the database table.
     *
     * The method respects checked out rows by other users and will attempt to checkin rows that it can after adjustments are made.
     *
     * @param string $columnName the name of the column to change the value for
     * @param mixed  $pks        An optional array of primary key values to update. If not set the instance property value is used.
     * @param int    $state      The publishing state. eg. [0 = unpublished, 1 = published]
     *
     * @return bool true on success; false if $pks is empty
     *
     * @since   1.7.0
     */
    protected function changeColumnValue($columnName, $pks, $state)
    {
        // Sanitize input
        $state = (int) $state;

        $pks = $this->sanitizeKeys($pks);

        // We don't have a full primary key - return false
        if (false === $pks) {
            $this->setError(JText::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));

            return false;
        }

        $columnField = $this->getColumnAlias($columnName);

        JPluginHelper::importPlugin('system');
        $dispatcher = JEventDispatcher::getInstance();

        $date = JFactory::getDate()->toSql();
        $modifiedBy = JFactory::getUser()->id;

        foreach ($pks as $pk) {
            // Update the publishing state for rows with the given primary keys.
            $query = $this->_db->getQuery(true)
                ->update($this->_tbl)
                ->set($this->_db->qn($columnField).' = '.(int) $state)
                ->set($this->_db->qn('modified').' = '.$this->_db->q($date))
                ->set($this->_db->qn('modified_by').' = '.(int) $modifiedBy);

            // Build the WHERE clause for the primary keys.
            $this->appendPrimaryKeys($query, $pk);

            $this->_db->setQuery($query);

            try {
                $this->_db->execute();
            } catch (RuntimeException $e) {
                $this->setError($e->getMessage());

                return false;
            }

            if ('id_estadocotizacion' == $columnField) {
                $myTable = clone $this;
                $myTable->reset();
                $myTable->load($pk);
                /** @var SabullvialTableCotizacionHistorico $cotizacionHistorico */
                $cotizacionHistorico = Table::getInstance('CotizacionHistorico', 'SabullvialTable');
                $id = $pk[$this->_tbl_key];
                $inserted = $cotizacionHistorico->insertEstado((int) $id, $state);

                if ($inserted) {
                    $table = clone $this;
                    $table->reset();
                    $table->load($pk);
                    $dispatcher->trigger('onContentChangeEstadoCotizacion', ['com_sabullvial.cotizacion', &$table, $state]);
                }
            } elseif ('id_estado_tango' == $columnField) {
                /** @var SabullvialTableCotizacionTangoHistorico $cotizacionHistorico */
                $cotizacionHistorico = Table::getInstance('CotizacionTangoHistorico', 'SabullvialTable');
                $id = $pk[$this->_tbl_key];
                $inserted = $cotizacionHistorico->insertEstado((int) $id, $state);

                if ($inserted) {
                    $table = clone $this;
                    $table->reset();
                    $table->load($pk);
                    $dispatcher->trigger('onContentChangeEstadoTango', ['com_sabullvial.cotizacion', &$table, $state]);
                }
            } elseif ('id_estadocotizacionpago' == $columnField) {
                /** @var SabullvialTableCotizacionPagoHistorico $cotizacionHistorico */
                $cotizacionHistorico = Table::getInstance('CotizacionPagoHistorico', 'SabullvialTable');
                $id = $pk[$this->_tbl_key];
                $inserted = $cotizacionHistorico->insertEstado((int) $id, $state);

                if ($inserted) {
                    $table = clone $this;
                    $table->reset();
                    $table->load($pk);
                    $dispatcher->trigger('onContentChangeEstadoCotizacionPago', ['com_sabullvial.cotizacion', &$table, $state]);
                }
            }

            // If the Table instance value is in the list of primary keys that were set, set the instance.
            $ours = true;

            foreach ($this->_tbl_keys as $key) {
                if ($this->$key != $pk[$key]) {
                    $ours = false;
                }
            }

            if ($ours) {
                $this->$columnField = $state;
            }
        }

        $this->setError('');

        return true;
    }

    protected function sanitizeKeys($pks)
    {
        if (!is_null($pks)) {
            if (!is_array($pks)) {
                $pks = [$pks];
            }

            foreach ($pks as $key => $pk) {
                if (!is_array($pk)) {
                    $pks[$key] = [$this->_tbl_key => $pk];
                }
            }
        }

        // If there are no primary keys set check to see if the instance key is set.
        if (empty($pks)) {
            $pk = [];

            foreach ($this->_tbl_keys as $key) {
                if ($this->$key) {
                    $pk[$key] = $this->$key;
                } else {
                    return false;
                }
            }

            $pks = [$pk];
        }

        return $pks;
    }

    /**
     * Revisa si la cotización fue aprobada.
     * Se revisa el historico de la cotización ordenado por fecha de creación descendente.
     * Se itera sobre el historico:
     * - Si el estado es aprobado, se devuelve true
     * - Si el estado es rechazado, se devuelve false
     * - Si el estado es otro, se continua iterando.
     *
     * @return bool
     */
    public function isAprobado($id = null)
    {
        $tableHistorico = JTable::getInstance('CotizacionHistorico', 'SabullvialTable');
        $historicos = $tableHistorico->getHistoricos(is_null($id) ? $this->id : $id);

        foreach ($historicos as $historico) {
            $tableEstado = $this->getEstadoCotizacion($historico->id_estadocotizacion);

            if ($tableEstado->aprobado) {
                return true;
            } elseif ($tableEstado->rechazado) {
                return false;
            }
        }

        return false;
    }

    public function hasRechazado($id = null)
    {
        $tableHistorico = JTable::getInstance('CotizacionHistorico', 'SabullvialTable');
        $historicos = $tableHistorico->getHistoricos(is_null($id) ? $this->id : $id);

        foreach ($historicos as $historico) {
            $tableEstado = $this->getEstadoCotizacion($historico->id_estadocotizacion);

            if ($tableEstado->rechazado) {
                return true;
            }
        }

        return false;
    }

    /**
     * Revisa si la cotización fue pagada.
     * Busca los historicos de los esperar pagos de la cotización y si encuentra un historico con el estado pagado,
     * devuelve true, en caso contrario devuelve false.
     *
     * @param int|null $id
     * @return boolean
     */
    public function hasEsperarPagosPagado($id = null)
    {
        $tableHistorico = JTable::getInstance('CotizacionPagoHistorico', 'SabullvialTable');
        $historicos = $tableHistorico->getHistoricos(is_null($id) ? $this->id : $id);

        $cmpParams = SabullvialHelper::getComponentParams();
        $idEstadoCotizacionPago = (int) $cmpParams->get('cotizacion_estado_esperar_pagos_pagado');

        if (!$idEstadoCotizacionPago) {
            return false;
        }

        foreach ($historicos as $historico) {
            if ($historico->id_estadocotizacionpago == $idEstadoCotizacionPago) {
                return true;
            }
        }

        return false;
    }

    public function getEstadoCotizacionPago($idCotizacion = null)
    {
        $idCotizacion = is_null($idCotizacion) ? $this->id : $idCotizacion;

        $tableHistorico = Table::getInstance('CotizacionPagoHistorico', 'SabullvialTable');
        $estadoCotizacionPago = $tableHistorico->getCurrentEstadoCotizacionPago($idCotizacion);

        if (!$estadoCotizacionPago) {
            return (object) Table::getInstance('EstadoCotizacionPago', 'SabullvialTable')->getEnEspera();
        }

        $table = $this->getEstadoCotizacionPagoById($estadoCotizacionPago->id_estadocotizacionpago);
        return (object) $table;
    }

    protected function getEstadoCotizacionPagoById($idEstadoCotizacionPago)
    {
        $store = 'getEstadoCotizacionPagoById-'.$idEstadoCotizacionPago;

        if (!isset(self::$cache[$store])) {
            $table = Table::getInstance('EstadoCotizacionPago', 'SabullvialTable');
            $table->load($idEstadoCotizacionPago);

            self::$cache[$store] = $table;
        }

        return self::$cache[$store];
    }

    /**
     * Si la cotización tiene en su historico el estado orden de trabajo
     * devolverá true, en caso contrario devolverá false.
     *
     * @param int|null $id
     * @return boolean
     */
    public function isOrdenDeTrabajo($id = null)
    {
        $id = is_null($id) ? $this->id : $id;

        if (!$id) {
            return false;
        }

        $idEstadoOrdenDeTrabajo = SabullvialHelper::getComponentParams()->get('orden_de_trabajo_estado_creado');

        $db = Factory::getDbo();

        $query = $db->getQuery(true);
        $query->select('count(*)')
            ->from($db->qn('#__sabullvial_cotizacionhistorico'))
            ->where($db->qn('id_cotizacion').' = '.(int) $id)
            ->where($db->qn('id_estadocotizacion').' = '.(int) $idEstadoOrdenDeTrabajo);

        $db->setQuery($query);

        $count = $db->loadResult();

        return $count > 0;
    }

    public function getNumerosFacturas($id = null)
    {
        $id = is_null($id) ? $this->id : $id;

        $db = $this->getDbo();

        $query = $db->getQuery(true);

        $query->select('pf.NCOMP_FAC numero_factura')
            ->from($db->qn('SIT_PEDIDOS_FACTURAS', 'pf'))
            ->where('pf.ID_PEDIDO_WEB = '.(int) $id)
            ->where('pf.N_RENG_PED = 1');

        $db->setQuery($query);

        return $db->loadColumn();
    }

    public function getRemitos($id)
    {
        $id = is_null($id) ? $this->id : $id;

        $db = $this->getDbo();

        $query = $db->getQuery(true);

        $query->select('pr.N_REMITO numero_remito')
            ->from($db->qn('SIT_PEDIDOS_REMITOS', 'pr'))
            ->where('pr.ID_PEDIDO_WEB = '.(int) $id)
            ->where('pr.N_RENG_PED = 1');

        $cmpParams = SabullvialHelper::getComponentParams();
        $idEstadoDefault = $cmpParams->get('remitos_estados_default_sin_estado', 0);

        $query->select('re.delivery_date, IFNULL(re.id_estadoremito, '.$idEstadoDefault.') id_estadoremito, re.image')
            ->leftJoin($db->qn('#__sabullvial_remitoestado', 're').' ON (re.numero_remito = pr.N_REMITO)');

        $query->select('er.nombre estadoremito, er.color estadoremito_bg_color, er.color_texto estadoremito_color')
            ->select('er.proceso estadoremito_proceso, er.preparacion estadoremito_preparacion')
            ->select('er.transito estadoremito_transito, er.entregado estadoremito_entregado')
            ->select('er.entregado_mostrador estadoremito_entregado_mostrador')
            ->leftJoin($db->qn('#__sabullvial_estadoremito', 'er').' ON (er.id = IFNULL(re.id_estadoremito, '.$idEstadoDefault.'))');

        $db->setQuery($query);

        return $db->loadObjectList();
    }

    public function getEstadoCotizacion($idEstadoCotizacion)
    {
        $store = 'getEstadoCotizacion-'.$idEstadoCotizacion;

        if (!isset(self::$cache[$store])) {
            $tableEstado = JTable::getInstance('EstadoCotizacion', 'SabullvialTable');
            $tableEstado->load($idEstadoCotizacion);

            self::$cache[$store] = $tableEstado;
        }

        return self::$cache[$store];
    }

    /**
     * Revisa si la cotización tiene productos personalizados/custom.
     *
     * @param int|null $id
     *
     * @return bool
     */
    public function hasCustomProducts($id = null)
    {
        $idCotizacion = (int) (is_null($id) ? $this->id : $id);

        if (0 == $idCotizacion) {
            return false;
        }

        $db = JFactory::getDbo();

        $where = $db->qn('id_producto').' LIKE '.$db->q('CUSTOM%').
            ' OR '.$db->qn('codigo_sap').' LIKE '.$db->q('CUSTOM%');

        $query = $db->getQuery(true);
        $query->select('count(*)')
            ->from($db->qn('#__sabullvial_cotizaciondetalle'))
            ->where($db->qn('id_cotizacion').' = '.$idCotizacion)
            ->andWhere($where);
        $db->setQuery($query);
        $count = (int) $db->loadResult();

        return $count > 0;
    }

    /**
     * Devuelve un true si la cotización fue revisada.
     *
     * @return bool
     */
    public function isRevisado($id = null)
    {
        $idCotizacion = (int) (is_null($id) ? $this->id : $id);

        if (0 == $idCotizacion) {
            return false;
        }

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('count(*)')
            ->from($db->qn('#__sabullvial_revisiondetalle'))
            ->innerJoin($db->qn('#__sabullvial_cotizaciondetalle', 'cd').' ON (cd.id = id_cotizacion_detalle)')
            ->where('cd.id_cotizacion = '.$idCotizacion);
        $db->setQuery($query);
        $count = $db->loadResult();

        return $count > 0;
    }

    public function getLastEstadoCotizacionHistorico($idCotizacion)
    {
        $idCotizacion = (int) $idCotizacion;

        $db = $this->_db;

        // Initialise the query.
        $query = $db->getQuery(true)
            ->select('ch.*, ec.nombre estado, ec.color bg_color, ec.color_texto color')
            ->from('#__sabullvial_cotizacionhistorico AS ch')
            ->join('LEFT', '#__sabullvial_estadocotizacion AS ec ON ec.id = ch.id_estadocotizacion')
            ->where($db->quoteName('id_cotizacion').' = '.(int) $idCotizacion)
            ->order('created DESC');

        $db->setQuery($query);

        return $db->loadObject();
    }
}

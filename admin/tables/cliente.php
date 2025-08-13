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
 * Cliente Table class
 *
 * @since  0.0.1
 */
class SabullvialTableCliente extends JTable
{
    public const CODIGO_VENDEDOR = [
        '25' => 'Pagina Web',
        'A2' => 'Andrea Almiron',
        'AC' => 'Alejandro Corral',
        'AF' => 'Albano Ferino',
        'AG' => 'Andrea Garcia',
        'AM' => 'Adrian Monserrat',
        'BV' => 'Bull-Vial',
        'CB' => 'Christian Broemser',
        'DB' => 'Bullentini D',
        'DO' => 'Damian Osuna',
        'FC' => 'Facundo Chamorro',
        'FN' => 'Federico Novelli',
        'GB' => 'Bullentini G',
        'GG' => 'Gaston Gimenez',
        'LS' => 'Leonel Segovia',
        'ML' => 'Mercadolibre',
        'NB' => 'Bullentini N',
        'NV' => 'Nicolas Velazquez',
        'PC' => 'Pablo Chingolani',
        'PT' => 'Pablo Tello',
        'RC' => 'Ricardo Colombo',
        'TA' => 'Taller',
        'VM' => 'Venta por Mostrador',
        'VTA2' => 'VTA 2',
        'VTA3' => 'VTA 3',
        'Z1' => 'Zona 1',
    ];

    public const CODIGO_RUBRO = [
        2 => 'Transporte',
        3 => 'Construcción',
        4 => 'Minería',
        5 => 'Industrial',
        6 => 'Agrícola',
        7 => 'Reventa',
        8 => 'ESTADO',
        9 => 'GRUAS',
        10 => 'PETROLERA',
        11 => 'CONSUMIDOR FINAL',
        12 => 'PUERTOS',
    ];

    public const CODIGO_CATEGORIA_IVA = [
        'CF' => 'Consumidor final',
        'EX' => 'Exento',
        'EXE' => 'Iva exento operación de exportación',
        'INR' => 'No responsable',
        'PCE' => 'Pequeño contribuyente eventual',
        'PCS' => 'Pequeño contribuyente eventual social',
        'RI' => 'Responsable inscripto',
        'RS' => 'Responsable monotributista',
        'RSS' => 'Monotributista social',
        'SNC' => 'Sujeto no categorizado',
    ];

    public const CODIGO_ZONA = [
        'B' => 'Gran Buenos Aires',
        'C' => 'Zona Centro',
        'CF' => 'Capital Federal',
        'E' => 'Zona Este',
        'I' => 'Interior del Pais',
        'N' => 'Zona Norte',
        'O' => 'Zona Oeste',
        'S' => 'Zona Sur',
    ];

    public const DOCUMENTO_TIPO = [
        80 => 'CUIT',
        86 => 'CUIL',
        96 => 'DNI',
    ];

    /**
     * Constructor
     *
     * @param   JDatabaseDriver  &$db  A database connector object
     */
    public function __construct(&$db)
    {
        parent::__construct('#__sabullvial_cliente', 'id', $db);
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

            if (!empty($this->created_by)) {
                $this->created_by_alias = JFactory::getUser($this->created_by)->username;
            }
        } else {
            // Field created_by field can be set by the user, so we don't touch it if it's set.
            if (empty($this->created_by)) {
                $this->created_by = $userId;
            }

            if (empty($this->created_by_alias)) {
                $this->created_by_alias = JFactory::getUser($userId)->username;
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

    public function getFormasDePago()
    {
        /** @var SabullvialTableClienteFormaPago $table */
        $table = JTable::getInstance('ClienteFormaPago', 'SabullvialTable');
        return $table->loadByIdCliente($this->id);
    }

    public function changeEstado($pks, $state, $plazo = null, $monto = null)
    {
        // Sanitize input
        $state  = (int) $state;

        $pks = $this->sanitizeKeys($pks);

        // We don't have a full primary key - return false
        if ($pks === false) {
            $this->setError(\JText::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
            return false;
        }

        $columnField = $this->getColumnAlias('id_estadocliente');

        JPluginHelper::importPlugin('system');
        $dispatcher = JEventDispatcher::getInstance();

        foreach ($pks as $pk) {
            // Update the publishing state for rows with the given primary keys.
            $query = $this->_db->getQuery(true)
                ->update($this->_tbl)
                ->set($this->_db->quoteName($columnField) . ' = ' . (int) $state);

            if (!is_null($plazo) && !is_null($monto)) {
                $query->set($this->_db->qn('plazo') . ' = ' . $this->_db->q($plazo));
                $query->set($this->_db->qn('monto') . ' = ' . (float)$monto);
            }

            // Build the WHERE clause for the primary keys.
            $this->appendPrimaryKeys($query, $pk);

            $this->_db->setQuery($query);

            try {
                $this->_db->execute();
            } catch (\RuntimeException $e) {
                $this->setError($e->getMessage());

                return false;
            }

            $table = clone $this;
            $table->reset();
            $table->load($pk);
            $dispatcher->trigger('onContentChangeEstadoCliente', ['com_sabullvial.cliente', &$table, $state]);

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
}

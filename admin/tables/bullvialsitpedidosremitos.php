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
 * Remito Table class
 *
 * @since  0.0.1
 */
class SabullvialTableBullvialSitPedidosRemitos extends JTable
{
    protected $data = [];

    /**
     * Constructor
     *
     * @param   JDatabaseDriver  &$db  A database connector object
     */
    public function __construct(&$db)
    {
        parent::__construct('SIT_PEDIDOS_REMITOS', 'N_REMITO', $db);
    }

    public function __get($name)
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }

        if ($name == 'productos') {
            //$this->data[$name] = $this->getProductos();
        } else {
            $this->data[$name] = parent::__get($name);
        }

        return $this->data[$name];
    }

    public function loadEstado()
    {
        if (!$this->N_REMITO) {
            return false;
        }

        $this->id = $this->N_REMITO;

        if (!$this->hasHojaDeRuta()) {
            $this->entregado = $this->wasEntregado();
            return true;
        }

        $db = $this->_db;
        $query = $db->getQuery(true);

        $query
            ->select('re.delivery_date, IF(er.id > 0, IF(er.entregado > 0 OR er.entregado_mostrador > 0, 1, 0), 0) entregado')
            ->from($db->qn('#__sabullvial_remitoestado', 're'))
            ->where($db->qn('re.numero_remito') . ' = ' . $db->q($this->N_REMITO));

        $query->select('hr.id_hojaderuta, h.delivery_date hojaderuta_delivery_date, h.chofer hojaderuta_chofer, h.patente hojaderuta_patente')
            ->leftJoin($db->qn('#__sabullvial_hojaderutaremito', 'hr') . ' ON (hr.numero_remito = re.numero_remito)')
            ->leftJoin($db->qn('#__sabullvial_hojaderuta', 'h') . ' ON (h.id = hr.id_hojaderuta)');

        $cmpParams = SabullvialHelper::getComponentParams();
        $idEstadoDefault = $cmpParams->get('remitos_estados_default_sin_estado', 0);

        $sqlIfHasHojaDeRuta = 'IF(re.id_estadoremito > 0, re.id_estadoremito, IF(hr.id_hojaderuta > 0, 
            (SELECT id FROM '.$db->qn('#__sabullvial_estadoremito').' WHERE transito = 1 LIMIT 1), 
            '.$idEstadoDefault.'
            ))';

        $query->select($sqlIfHasHojaDeRuta . ' id_estadoremito, er.nombre estadoremito, er.color estadoremito_bg_color, er.color_texto estadoremito_color')
            ->select('er.proceso estadoremito_proceso')
            ->select('er.preparacion estadoremito_preparacion')
            ->select('er.transito estadoremito_transito, er.entregado estadoremito_entregado, er.entregado_mostrador estadoremito_entregado_mostrador')
            ->leftJoin($db->qn('#__sabullvial_estadoremito', 'er') . ' ON (er.id = '.$sqlIfHasHojaDeRuta.')');

        $row = $db->setQuery($query)->loadAssoc();

        foreach ($row as $key => $value) {
            $this->$key = $value;
        }

        return true;
    }

    public function hasHojaDeRuta()
    {
        $db = $this->_db;

        $query = $db->getQuery(true);
        $query->select('COUNT(*)')
            ->from($db->qn('#__sabullvial_hojaderutaremito', 'hr'))
            ->where($db->qn('hr.numero_remito') . ' = ' . $db->q($this->N_REMITO));

        return $db->setQuery($query)->loadResult();
    }

    public function wasEntregado()
    {
        $db = $this->_db;

        $query = $db->getQuery(true);
        $query->select('id_estadoremito')
            ->from($db->qn('#__sabullvial_remitoestado', 're'))
            ->where($db->qn('re.numero_remito') . ' = ' . $db->q($this->N_REMITO))
            ->order('re.id DESC');

        $idCurrentEstado = (int) $db->setQuery($query)->loadResult();

        /** @var SabullvialTableRemito $table */
        Table::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');
        $estadoEntregado = Table::getInstance('EstadoRemito', 'SabullvialTable')->getEntregado();

        if ($idCurrentEstado == $estadoEntregado->id) {
            return true;
        }

        $estadoEntregadoPorMostrador = Table::getInstance('EstadoRemito', 'SabullvialTable')->getEntregadoPorMostrador();

        if ($idCurrentEstado == $estadoEntregadoPorMostrador->id) {
            return true;
        }

        return false;
    }

    public function getCliente()
    {
        $db = $this->_db;

        $query = $db->getQuery(true);
        $query->select('c.RAZON_SOCI cliente')
            ->from($db->qn('SIT_CLIENTES', 'c'))
            ->where('c.COD_CLIENT = ' . $this->COD_CLIENT);

        return $db->setQuery($query)->loadResult();
    }
}

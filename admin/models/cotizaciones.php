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

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Table\Table;

/**
 * SabullvialList Model
 *
 * @since  0.0.1
 */
class SabullvialModelCotizaciones extends ListModel
{
    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @see     JController
     * @since   1.6
     */
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id', 'a.id',
                'reference', 'a.reference',
                'cliente', 'razon_social',
                'a.id_estadocotizacion', 'id_estadocotizacion',
                'a.id_estado_tango', 'id_estado_tango',
                'a.ordendecompra_file_name', 'a.ordendecompra_file_hash', 'a.ordendecompra_file_ext',
                'a.esperar_pagos', 'esperar_pagos',
                'iva', 'total',
                'total',
                'author',
                'author_id',
                'created', 'a.created',
                'created_from', 'created_to',
                'modified', 'a.modified',
                'created_by', 'a.created_by',
                'created_by_alias', 'a.created_by_alias',
                'published', 'a.published',
                'ec.nombre', 'estadocotizacion',
                'codigo_vendedor', 'c.COD_VENDED',
            ];
        }

        parent::__construct($config);
    }

    public function getItems()
    {
        $items = parent::getItems();

        /** @var SabullvialTableCotizacion $tableCotizacion */
        $tableCotizacion = Table::getInstance('Cotizacion', 'SabullvialTable');

        $cmpParams = SabullvialHelper::getComponentParams();
        $idEstadoCotizacionPagoEnEspera = (int) $cmpParams->get('cotizacion_estado_esperar_pagos_en_espera');

        foreach ($items as &$item) {
            $item->is_orden_de_trabajo = $tableCotizacion->isOrdenDeTrabajo($item->id);
            $item->was_aprobado = $tableCotizacion->isAprobado($item->id);
            $item->has_rechazado = $tableCotizacion->hasRechazado($item->id);
            $item->has_custom_products = $tableCotizacion->hasCustomProducts($item->id);
            $item->remitos = [];
            $item->numeros_facturas = [];

            if ($item->tango_enviar && !SabullvialHelper::isTangoFechaSincronizacionNull($item->tango_fecha_sincronizacion)) {
                $item->remitos = $tableCotizacion->getRemitos($item->id);
                $item->numeros_facturas = $tableCotizacion->getNumerosFacturas($item->id);
            }

            $item->estadocotizacionpago = null;
            $item->is_esperar_pagos_pagado = false;
            if ((bool)$item->esperar_pagos) {
                $estadoCotizacionPago = $tableCotizacion->getEstadoCotizacionPago($item->id);
                $item->estadocotizacionpago = $estadoCotizacionPago->nombre;
                $item->estadocotizacionpago_bg_color = $estadoCotizacionPago->color;
                $item->estadocotizacionpago_color = $estadoCotizacionPago->color_texto;

                $item->is_esperar_pagos_pagado = (int) $estadoCotizacionPago->id == $idEstadoCotizacionPagoEnEspera;
            }
        }

        return $items;
    }

    /**
     * Method to build an SQL query to load the list data.
     *
     * @return      string  An SQL query
     */
    protected function getListQuery()
    {
        $user = JFactory::getUser();

        // Initialize variables.
        // $canDo = JHelperContent::getActions('com_sabullvial', 'cotizacion');
        $vendedor = SabullvialHelper::getVendedor();
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        // Create the base select statement.
        $query->select('DISTINCT a.id, a.parent_id, a.reference, a.id_cliente, a.cliente, a.id_estadocotizacion, a.id_estado_tango')
            ->select('a.iva, a.dolar, a.total, a.published, a.created, a.documento_tipo, a.documento_numero, a.esperar_pagos')
            ->select('a.ordendecompra_file_name, a.ordendecompra_file_hash, a.ordendecompra_file_ext')
            ->select('a.tango_enviar, a.tango_fecha_sincronizacion')
            ->select('a.id_condicionventa, a.id_condicionventa_fake')
            ->select('a.created_by')
            ->from($db->quoteName('#__sabullvial_cotizacion', 'a'));

        $query->select('ec.nombre estadocotizacion, ec.color estadocotizacion_bg_color, ec.color_texto estadocotizacion_color')
            ->select('ec.aprobado estadocotizacion_aprobado, ec.rechazado estadocotizacion_rechazado, ec.revisado estadocotizacion_revisado')
            ->select('ec.cancelado estadocotizacion_cancelado, ec.pendiente estadocotizacion_pendiente')
            ->join('LEFT', $db->quoteName('#__sabullvial_estadocotizacion', 'ec') . ' ON ec.id = a.id_estadocotizacion');

        // Optimización: Convertir subconsultas a LEFT JOINs para mejor rendimiento
        $query->select('COALESCE(rev.revision_count, 0) as is_reviewed')
            ->leftJoin('(SELECT cd.id_cotizacion, COUNT(*) as revision_count FROM ' . $db->qn('#__sabullvial_revisiondetalle') . ' rd INNER JOIN ' . $db->qn('#__sabullvial_cotizaciondetalle') . ' cd ON (cd.id = rd.id_cotizacion_detalle) GROUP BY cd.id_cotizacion) AS rev ON rev.id_cotizacion = a.id');

        $query->select('COALESCE(det.detalle_count, 0) as has_cotizaciondetalle')
            ->leftJoin('(SELECT id_cotizacion, COUNT(*) as detalle_count FROM ' . $db->qn('#__sabullvial_cotizaciondetalle') . ' GROUP BY id_cotizacion) AS det ON det.id_cotizacion = a.id');

        $sqlEstadoTango = $this->getQueryEstadosTango();
        $sqlEstadoTangoBgColor = $this->getQueryEstadosTango('background_color', '#474747');
        $sqlEstadoTangoColor = $this->getQueryEstadosTango('color', '#ffffff');

        $query
            ->select($sqlEstadoTango . ' estadotango, ' . $sqlEstadoTangoBgColor . ' estadotango_bg_color')
            ->select($sqlEstadoTangoColor . ' estadotango_color');

        $query->select('c.RAZON_SOCI razon_social, c.COD_CLIENT codcli, c.cuit, c.COD_VENDED codigo_vendedor')
            ->leftJoin($db->qn('SIT_CLIENTES', 'c') . ' ON (c.COD_CLIENT = a.id_cliente)');

        // Join with users table to get the username of the author
        $query->select('u.username as author')
            ->leftJoin($db->qn('#__users', 'u') . ' ON u.id = a.created_by');

        $query->select('a.id_deposito, d.name deposito')
            ->leftJoin($db->qn('#__sabullvial_deposito', 'd') . ' ON d.id = a.id_deposito');

        // Filter by id_estadocotizacion
        $idEstadocotizacion = $this->getState('filter.id_estadocotizacion');

        if (is_numeric($idEstadocotizacion)) {
            $type = $this->getState('filter.id_estadocotizacion.include', true) ? '= ' : '<>';
            $query->where('a.id_estadocotizacion ' . $type . (int) $idEstadocotizacion);
        } elseif (is_array($idEstadocotizacion)) {
            $idEstadocotizacion = ArrayHelper::toInteger($idEstadocotizacion);
            $idEstadocotizacion = implode(',', $idEstadocotizacion);
            $query->where('a.id_estadocotizacion IN (' . $idEstadocotizacion . ')');
        }

        // Filter by id_estado_tango
        $idEstadoTango = $this->getState('filter.id_estado_tango');

        if (is_numeric($idEstadoTango)) {
            $type = $this->getState('filter.id_estado_tango.include', true) ? '= ' : '<>';
            $query->where('a.id_estado_tango ' . $type . (int) $idEstadoTango);
        } elseif (is_array($idEstadoTango)) {
            $idEstadoTango = ArrayHelper::toInteger($idEstadoTango);

            if (count($idEstadoTango) === 1 && $idEstadoTango[0] == SabullvialTableCotizacion::ESTADO_TANGO_ORDEN_DE_TRABAJO_FACTURADA) {
                $ids = [
                    SabullvialTableCotizacion::ESTADO_TANGO_AUTOMATICA,
                    SabullvialTableCotizacion::ESTADO_TANGO_PRUEBA,
                    SabullvialTableCotizacion::ESTADO_TANGO_SRL
                ];

                $idEstadoOrdenDeTrabajo = SabullvialHelper::getComponentParams()->get('orden_de_trabajo_estado_creado');

                $query
                    ->leftJoin($db->qn('#__sabullvial_cotizacionhistorico', 'ch') . ' ON ch.id_cotizacion = a.id')
                    ->where('ch.id_estadocotizacion = ' . (int) $idEstadoOrdenDeTrabajo)
                    ->where('a.id_estado_tango IN (' . implode(',', $ids) . ')');

            } else {
                $idEstadoTango = implode(',', $idEstadoTango);
                $query->where('a.id_estado_tango IN (' . $idEstadoTango . ')');
            }
        }

        $isClienteRevendedor = $vendedor->get('esRevendedor', false);
        $showOnlyOwn = $isClienteRevendedor || !$vendedor->get('ver.presupuestos', false);

        // Filter by author
        $authorId = $this->getState('filter.author_id');

        // Entra:
        // 1. Si es cliente revendedor
        // 2. Si tiene el campo "Ver todos los presupuestos" en si
        if ($showOnlyOwn) {
            $user = JFactory::getUser();
            $query->where('a.created_by = ' . (int)$user->id);
        } elseif (is_numeric($authorId)) {
            $type = $this->getState('filter.author_id.include', true) ? '= ' : '<>';
            $query->where('a.created_by ' . $type . (int) $authorId);
        } elseif (is_array($authorId)) {
            $authorId = ArrayHelper::toInteger($authorId);
            $authorId = implode(',', $authorId);
            $query->where('a.created_by IN (' . $authorId . ')');
        }

        // Filter: like / search
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int) substr($search, 3));
            } else {
                $query->leftJoin($db->qn('SIT_PEDIDOS_REMITOS', 'pr') . ' ON (pr.ID_PEDIDO_WEB = a.id AND pr.N_RENG_PED = 1)');
                $query->leftJoin($db->qn('SIT_PEDIDOS_FACTURAS', 'pf') . ' ON (pf.ID_PEDIDO_WEB = a.id AND pf.N_RENG_PED = 1)');

                $this->filter_fields[] = 'pr.N_REMITO';
                $this->filter_fields[] = 'pf.NCOMP_FAC';

                $sqlSearchQuery = 'a.cliente LIKE #query# 
                    OR REPLACE(a.documento_numero, "-", "") LIKE #query# 
                    OR a.id_cliente LIKE #query#
                    OR a.id LIKE #query#
                    OR pr.N_REMITO LIKE #query#
                    OR pf.NCOMP_FAC LIKE #query#';
                $searchQuery = SabullvialHelper::unorderedSearch($search, $sqlSearchQuery, $db);
                $query->where($searchQuery);
            }
        }

        $createdFrom = $this->getState('filter.created_from');
        if (!empty($createdFrom)) {
            $query->where('a.created >= ' . $db->quote($createdFrom . ' 00:00:00'));
        }

        $createdTo = $this->getState('filter.created_to');
        if (!empty($createdTo)) {
            $query->where('a.created <= ' . $db->quote($createdTo . ' 23:59:59'));
        }

        $codigoVendedor = $this->getState('filter.codigo_vendedor');
        if (!empty($codigoVendedor)) {
            $query->where('c.COD_VENDED = ' . $db->quote($codigoVendedor));
        }

        $idDeposito = $this->getState('filter.id_deposito');
        if (!empty($idDeposito)) {
            $query->where('a.id_deposito = ' . (int) $idDeposito);
        }

        $esperarPagos = $this->getState('filter.esperar_pagos');
        if (is_numeric($esperarPagos)) {
            $query->where('a.esperar_pagos = ' . (int) $esperarPagos);
        }

        // Add the list ordering clause.
        $orderCol	= $this->state->get('list.ordering', 'a.id');
        $orderDirn 	= $this->state->get('list.direction', 'desc');

        if ($orderCol == 'total') {
            $cotizacionDolar = SabullvialHelper::getConfig('COTI_DOL');
            $orderCol = 'IF(dolar = 1, total * '.(float)$cotizacionDolar.', total)';
        }

        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

        return $query;
    }

    /**
     * Función optimizada de getListcount para que eliminé los JOIN
     * y se ejecute más rápido
     *
     * @param   \JDatabaseQuery|string  $query  The query.
     *
     * @return  integer  Number of rows for query.
     *
     * @since   3.0
     */
    protected function _getListCount($query)
    {
        // Use fast COUNT(*) on \JDatabaseQuery objects if there is no GROUP BY or HAVING clause:
        if ($query instanceof \JDatabaseQuery
            && $query->type == 'select'
            && $query->group === null
            && $query->union === null
            && $query->unionAll === null
            && $query->having === null) {
            $query = clone $query;
            $query->clear('select')->clear('order')->clear('limit')->clear('offset')->clear('join')->select('COUNT(DISTINCT a.id)');

            // Agrego los left join básicos necesarios
            $db = JFactory::getDbo();
            $query
                ->leftJoin($db->qn('SIT_CLIENTES', 'c') . ' ON (c.COD_CLIENT = a.id_cliente)');

            $search = $this->getState('filter.search');
            if (!empty($search) && stripos($search, 'id:') !== 0) {
                $query
                    ->leftJoin($db->qn('SIT_PEDIDOS_REMITOS', 'pr') . ' ON (pr.ID_PEDIDO_WEB = a.id AND pr.N_RENG_PED = 1)')
                    ->leftJoin($db->qn('SIT_PEDIDOS_FACTURAS', 'pf') . ' ON (pf.ID_PEDIDO_WEB = a.id AND pf.N_RENG_PED = 1)');
            }

            $idEstadoTango = $this->getState('filter.id_estado_tango');
            $idEstadoTango = is_array($idEstadoTango) ? ArrayHelper::toInteger($idEstadoTango) : [$idEstadoTango];

            if (count($idEstadoTango) === 1 && $idEstadoTango[0] == SabullvialTableCotizacion::ESTADO_TANGO_ORDEN_DE_TRABAJO_FACTURADA) {
                $ids = [
                    SabullvialTableCotizacion::ESTADO_TANGO_AUTOMATICA,
                    SabullvialTableCotizacion::ESTADO_TANGO_PRUEBA,
                    SabullvialTableCotizacion::ESTADO_TANGO_SRL
                ];

                $idEstadoOrdenDeTrabajo = SabullvialHelper::getComponentParams()->get('orden_de_trabajo_estado_creado');

                $query
                    ->leftJoin($db->qn('#__sabullvial_cotizacionhistorico', 'ch') . ' ON (ch.id_cotizacion = a.id)')
                    ->where('ch.id_estadocotizacion = ' . (int) $idEstadoOrdenDeTrabajo)
                    ->where('a.id_estado_tango IN (' . implode(',', $ids) . ')');
            }

            $this->getDbo()->setQuery($query);

            return (int) $this->getDbo()->loadResult();
        }

        // Otherwise fall back to inefficient way of counting all results.

        // Remove the limit, offset and order parts if it's a \JDatabaseQuery object
        if ($query instanceof \JDatabaseQuery) {
            $query = clone $query;
            $query->clear('limit')->clear('offset')->clear('order');
        }

        $this->getDbo()->setQuery($query);
        $this->getDbo()->execute();

        return (int) $this->getDbo()->getNumRows();
    }

    protected function getQueryEstadosTango($fieldValue = 'text', $defaultValue = 'Sin estado')
    {
        require_once JPATH_COMPONENT . '/models/fields/estadocotizaciontango.php';
        $estados = JFormFieldEstadoCotizacionTango::getItems();

        $caseSql = 'CASE ';
        foreach ($estados as $estado) {
            $caseSql .= ' WHEN a.id_estado_tango = ' . $estado['id'] . ' THEN ' . $this->_db->q($estado[$fieldValue]);
        }

        $caseSql .= ' ELSE ' . $this->_db->q($defaultValue);
        $caseSql .= ' END ';

        return $caseSql;
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function populateState($ordering = 'a.id', $direction = 'desc')
    {
        $app = JFactory::getApplication();

        // Adjust the context to support modal layouts.
        if ($layout = $app->input->get('layout')) {
            $this->context .= '.' . $layout;
        }

        // List state information.
        parent::populateState($ordering, $direction);

        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        $formSubmited = $app->input->post->get('form_submited');

        $authorId = $this->getUserStateFromRequest($this->context . '.filter.author_id', 'filter_author_id');

        $idEstadocotizacion = $this->getUserStateFromRequest($this->context . '.filter.id_estadocotizacion', 'filter_id_estadocotizacion');
        $this->setState('filter.id_estadocotizacion', $idEstadocotizacion);

        $codigoVendedor = $this->getUserStateFromRequest($this->context . '.filter.codigo_vendedor', 'filter_codigo_vendedor');
        $this->setState('filter.codigo_vendedor', $codigoVendedor);

        $createdFrom = $this->getUserStateFromRequest($this->context . '.filter.created_from', 'filter_created_from');
        $this->setState('filter.created_from', $createdFrom);

        $createdTo = $this->getUserStateFromRequest($this->context . '.filter.created_to', 'filter_created_to');
        $this->setState('filter.created_to', $createdTo);

        $idDeposito = $this->getUserStateFromRequest($this->context . '.filter.id_deposito', 'filter_id_deposito');
        $this->setState('filter.id_deposito', $idDeposito);

        $esperarPagos = $this->getUserStateFromRequest($this->context . '.filter.esperar_pagos', 'filter_esperar_pagos');
        $this->setState('filter.esperar_pagos', $esperarPagos);

        if ($formSubmited) {
            $authorId = $app->input->post->get('author_id');
            $this->setState('filter.author_id', $authorId);

            $idEstadoTango   = $this->getUserStateFromRequest($this->context . '.filter.id_estado_tango', 'filter_id_estado_tango');
            $this->setState('filter.id_estado_tango', $idEstadoTango);
        }

        // Load the parameters.
        $params = JComponentHelper::getParams('com_sabullvial');
        $this->setState('params', $params);
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param   string  $id  A prefix for the store id.
     *
     * @return  string  A store id.
     *
     * @since   1.6
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . serialize($this->getState('filter.author_id'));
        $id .= ':' . serialize($this->getState('filter.id_estadocotizacion'));
        $id .= ':' . serialize($this->getState('filter.id_estado_tango'));
        $id .= ':' . $this->getState('filter.codigo_vendedor');
        $id .= ':' . $this->getState('filter.created_from');
        $id .= ':' . $this->getState('filter.created_to');
        $id .= ':' . $this->getState('filter.id_deposito');
        $id .= ':' . $this->getState('filter.esperar_pagos');

        return parent::getStoreId($id);
    }

    /**
     * Get the filter form
     *
     * @param   array    $data      data
     * @param   boolean  $loadData  load current data
     *
     * @return  \JForm|boolean  The \JForm object or false on error
     *
     * @since   3.2
     */
    public function getFilterForm($data = [], $loadData = true)
    {
        $form = parent::getFilterForm($data, $loadData);

        if (empty($form)) {
            return $form;
        }

        $vendedor = SabullvialHelper::getVendedor();
        $isClienteRevendedor = $vendedor->get('esRevendedor', false);
        $showOnlyOwn = $isClienteRevendedor || !$vendedor->get('ver.presupuestos', false);
        // Si solo se permite editar los items propios (vendedores)
        if ($showOnlyOwn) {
            $form->removeField('author_id', 'filter');
            $form->removeField('codigo_vendedor', 'filter');
        }

        return $form;
    }

    public function setSuffixContext($context)
    {
        $this->context .= '.' . $context;
    }
}

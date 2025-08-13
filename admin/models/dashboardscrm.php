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

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;

/**
 * SabullvialList Model
 *
 * @since  0.0.1
 */
class SabullvialModelDashboardsCrm extends JModelList
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
                'name', 'a.name',
                'author',
                'author_id',
                'created', 'a.created',
                'modified', 'a.modified',
                'created_by', 'a.created_by',
                'created_by_alias', 'a.created_by_alias',
                'published', 'a.published',
                'codigo_vendedor',
                'c.created', 'total'
            ];
        }

        parent::__construct($config);
    }

    /**
     * Method to allow derived classes to preprocess the form.
     *
     * @param   \JForm  $form   A \JForm object.
     * @param   mixed   $data   The data expected for the form.
     * @param   string  $group  The name of the plugin group to import (defaults to "content").
     *
     * @return  void
     *
     * @since   3.2
     * @throws  \Exception if there is an error in the form event.
     */
    protected function preprocessForm($form, $data, $group = 'content')
    {
        parent::preprocessForm($form, $data, $group);

        $vendedor = SabullvialHelper::getVendedor();
        $verCRM = $vendedor->get('ver.crm', 0);

        if ($verCRM != SabullvialHelper::VER_PROPIAS) {
            parent::preprocessForm($form, $data, $group);
            return;
        }

        if (!empty($data)) {
            $data->filter['author_id'] = Factory::getUser()->id;
            $this->setState('filter.author_id', $data->filter['author_id']);

            $data->filter['codigo_vendedor'] = $vendedor->get('codigo', '');
            $this->setState('filter.codigo_vendedor', $data->filter['codigo_vendedor']);
        }

        if (!empty($form)) {
            $form->setFieldAttribute('author_id', 'class', 'hidden no-chosen', 'filter');
            $form->setFieldAttribute('author_id', 'value', $data->filter['author_id'], 'filter');

            $form->setFieldAttribute('codigo_vendedor', 'class', 'hidden no-chosen', 'filter');
            $form->setFieldAttribute('codigo_vendedor', 'value', $data->filter['codigo_vendedor'], 'filter');
        }
    }

    public function getTareas()
    {
        /** @var SabullvialModelTareas $model */
        $model = ListModel::getInstance('Tareas', 'SabullvialModel', ['ignore_request' => true]);
        // limit 5
        $model->setState('list.limit', 5);

        $dateFrom = $this->getState('filter.date_from');
        $dateTo = $this->getState('filter.date_to');

        if (!empty($dateFrom)) {
            $model->setState('filter.start_date_from', $dateFrom);
        }

        if (!empty($dateTo)) {
            $model->setState('filter.start_date_to', $dateTo);
        }

        $codigoCliente = $this->getState('filter.codigo_cliente');
        if (!empty($codigoCliente)) {
            $model->setState('filter.codigo_cliente', $codigoCliente);
        }

        $codigoVendedor = $this->getState('filter.codigo_vendedor');
        if (!empty($codigoVendedor)) {
            $model->setState('filter.codigo_vendedor', $codigoVendedor);
        }

        // $authorId = $this->getState('filter.author_id');
        // if (!empty($authorId)) {
        //     $model->setState('filter.author_id', $authorId);
        // }

        return $model->getItems();
    }

    public function getProductosMasVendidos()
    {
        /** @var SabullvialModelProductos */
        $model = ListModel::getInstance('Productos', 'SabullvialModel', ['ignore_request' => true]);

        $dateFrom = $this->getState('filter.date_from');
        $dateTo = $this->getState('filter.date_to');

        if (!empty($dateFrom)) {
            $model->setState('filter.date_from', $dateFrom);
        }

        if (!empty($dateTo)) {
            $model->setState('filter.date_to', $dateTo);
        }

        // $authorId = $this->getState('filter.author_id');
        // if (!empty($authorId)) {
        //     $model->setState('filter.author_id', $authorId);
        // }

        $codigoVendedor = $this->getState('filter.codigo_vendedor');
        if (!empty($codigoVendedor)) {
            $model->setState('filter.codigo_vendedor', $codigoVendedor);
        }

        $codigoCliente = $this->getState('filter.codigo_cliente');
        if (!empty($codigoCliente)) {
            $model->setState('filter.cliente_id', $codigoCliente);
        }

        $totalFrom = $this->getState('filter.total_from');
        if (!empty($totalFrom)) {
            $model->setState('filter.total_from', $totalFrom);
        }

        $totalTo = $this->getState('filter.total_to');
        if (!empty($totalTo)) {
            $model->setState('filter.total_to', $totalTo);
        }

        return $model->getListProductosMasVendidos();
    }

    public function getRankingVendedores()
    {
        /** @var SabullvialModelVendedores $model */
        $model  = ListModel::getInstance('Vendedores', 'SabullvialModel', ['ignore_request' => true]);
        $model->setState('list.ordering', 'cotizaciones_efectividad');
        $model->setState('list.direction', 'DESC');
        $model->setState('list.limit', 10);

        $dateFrom = $this->getState('filter.date_from');
        $dateTo = $this->getState('filter.date_to');

        if (!empty($dateFrom)) {
            $model->setState('filter.date_from', $dateFrom);
        }

        if (!empty($dateTo)) {
            $model->setState('filter.date_to', $dateTo);
        }

        $codigoVendedor = $this->getState('filter.codigo_vendedor');
        if (!empty($codigoVendedor)) {
            $authorId = $this->findAuthorIdByCodigoVendedor($codigoVendedor);
        } else {
            $authorId = $this->getState('filter.author_id');
        }

        if (!empty($authorId)) {
            $model->setState('filter.author_id', $authorId);
        }


        $codigoCliente = $this->getState('filter.codigo_cliente');
        if (!empty($codigoCliente)) {
            $model->setState('filter.cliente_id', $codigoCliente);
        }

        $totalFrom = $this->getState('filter.total_from');
        if (!empty($totalFrom)) {
            $model->setState('filter.total_from', $totalFrom);
        }

        $totalTo = $this->getState('filter.total_to');
        if (!empty($totalTo)) {
            $model->setState('filter.total_to', $totalTo);
        }

        $items = $model->getItems();

        return $items;
    }

    /**
     * Devuelve la suma total de las ventas realizadas y su cantidad.
     * Una venta realizada es cuando un pedido tiene un remito, una factura,
     * tango_enviar = 1, tango_fecha_sincronizacion > 1800-01-01 00:00:00 y el estado de la
     * cotización es uno de los configurados (aprobado, aprobado automatico,
     * aprobado completo y aprobado con faltantes).
     *
     * @return object
     */
    public function getCantidadYTotalVentasRealizadas()
    {
        $db = Factory::getDbo();
        $subQuery = $this->getVentasRealizadasQuery();
        $subQuery->clear('select')
            ->select('c.id, c.total')
            ->group('c.id');

        $query = $db->getQuery(true);
        $query->select('sum(t.total) total')
            ->select('count(t.id) cantidad')
            ->from('(' . $subQuery . ') as t');

        $db->setQuery($query);

        return $db->loadObject();
    }

    public function getVentasRealizadas()
    {
        $db = Factory::getDbo();
        $query = $this->getVentasRealizadasQuery()
            ->select('IF(c.id_cliente = "000000", "000000", cli.cod_client) codcli')
            ->select('IF(c.id_cliente = "000000", "Consumidor final", cli.razon_soci) razon_social')
            ->select('cli.cuit, cli.COD_VENDED codigo_vendedor')
            ->select('IF(c.documento_numero = "" OR c.documento_numero IS NULL, cli.CUIT, c.documento_numero) documento_numero')
            ->select('u.username as author')
            ->join('LEFT', $db->quoteName('#__users', 'u') . ' ON u.id = c.created_by')
            ->group('c.id')
            ->setLimit(10);

        $ordering = $this->getState('list.ordering', 'c.id');
        $direction = $this->getState('list.direction', 'DESC');

        if ($ordering == 'total') {
            $ordering = 'IF(c.total_revision > 0, c.total_revision, c.total)';
        }

        $query->order($db->escape($ordering) . ' ' . $db->escape($direction));

        $db->setQuery($query);

        return $db->loadObjectList();
    }

    public function getVentasRealizadasQuery()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('c.*')
            ->from('#__sabullvial_cotizacion c')
            ->leftJoin('SIT_CLIENTES cli ON (cli.COD_CLIENT = c.id_cliente)')
            ->innerJoin('SIT_PEDIDOS_REMITOS r ON (r.id_pedido_web = c.id)')
            ->innerJoin('SIT_PEDIDOS_FACTURAS f ON (f.id_pedido_web = c.id)');
        $query = $this->getQueryCompraValida('c', $query);

        $query = $this->applyGeneralFilters($query, 'c');

        $codigoVendedor = $this->getState('filter.codigo_vendedor');
        if (!empty($codigoVendedor)) {
            $query->where('cli.COD_VENDED = ' . $db->q($codigoVendedor));
        }

        // Autor (distinto al codigo_vendedor)
        // $authorId = $this->getState('filter.author_id');
        // if ($authorId > 0) {
        //     $vendedor = SabullvialHelper::getVendedor($authorId);
        //     $codigoVendedor = $vendedor->get('codigo', '');
        //     if (!empty($codigoVendedor)) {
        //         $query->where('cli.COD_VENDED = ' . $db->q($codigoVendedor));
        //     } else {
        //         $query->where('c.created_by = ' . (int)$authorId);
        //     }
        // }

        return $query;
    }

    public function getClientesConUltimaCompra()
    {
        $cmpParams = SabullvialHelper::getComponentParams();
        $dias = (int)$cmpParams->get('dashboard_crm_dias_ultima_compra', 90);

        $db = Factory::getDbo();

        $query = $db->getQuery(true);

        $query
            ->select('sc.cod_client id, sc.cod_client codcli, sc.cod_client, sc.razon_soci razon_social')
            ->select('sc.cuit, sc.saldo_cc saldo, sc.PROMEDIO_ULT_REC, sc.INHABILITADO, IF(sc.INHABILITADO = "N", 1, 0) habilitado')
            ->select('sc.COD_VENDED codigo_vendedor')
            ->select('p.created AS ultima_fecha_cotizacion, DATEDIFF(CURDATE(), p.created) AS dias_ultima_compra')
            ->select('p.id id_ultima_cotizacion, p.created_by cotizacion_created_by')
            ->from('SIT_CLIENTES sc')
            ->innerJoin(
                '(
					SELECT id_cliente, MAX(created) AS created
					FROM #__sabullvial_cotizacion
					GROUP BY id_cliente
				) AS ultimos_pedidos ON (ultimos_pedidos.id_cliente = sc.COD_CLIENT)'
            )
            ->innerJoin('#__sabullvial_cotizacion p ON (p.id_cliente = ultimos_pedidos.id_cliente AND ultimos_pedidos.created = p.created)')
            ->where('DATEDIFF(CURDATE(), p.created) >= ' . $dias)
            ->where('sc.INHABILITADO = "N"')
            ->setLimit(10);

        $vendedor = SabullvialHelper::getVendedor();

        $clienteRevendedor = $vendedor->get('clienteRevendedor', '');
        $isRevendedor = $vendedor->get('esRevendedor', false);
        $verTodosLosClientes = $vendedor->get('ver.todosLosClientes', false);
        $codigoVendedor = $vendedor->get('codigo', '');

        if ($isRevendedor) {
            $query->where('c.cod_client = ' . $db->q($clienteRevendedor));
        } elseif (!empty($codigoVendedor) && !$verTodosLosClientes) {
            $query->where('sc.COD_VENDED = ' . $db->q($codigoVendedor));
        } else {
            $codigoVendedor = $this->getState('filter.codigo_vendedor');
            if (!empty($codigoVendedor)) {
                $query->where('sc.COD_VENDED = ' . $db->q($codigoVendedor));
            }
        }

        $query = $this->applyGeneralFilters($query, 'c');

        $db->setQuery($query);

        return $db->loadObjectList();
    }

    /**
     * Obtiene los clientes de Tango con más compras
     *
     * @return array
     */
    public function getClientesQueMasCompran()
    {
        $query = $this->getClientesComprasQuery()
            ->order('total DESC')
            ->setLimit(10);

        $db = Factory::getDbo();
        $db->setQuery($query);

        return $db->loadObjectList();
    }

    /**
     * Obtiene los clientes de Tango con menos compras
     *
     * @return array
     */
    public function getClientesQueMenosCompran()
    {
        $query = $this->getClientesComprasQuery()
            ->order('total ASC')
            ->setLimit(10);

        $db = Factory::getDbo();
        $db->setQuery($query);

        return $db->loadObjectList();
    }

    /**
     * Obtiene los clientes con sus compras
     *
     * @return JDatabaseQuery
     */
    public function getClientesComprasQuery()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $query
            ->select('c.id_cliente, count(*) cantidad_compras')
            ->select('sum(c.total) total, sum(c.total_revision) total_revision')
            ->select('sc.cod_client id, sc.cod_client codcli, IF(c.id_cliente = "000000", "000000", sc.cod_client), IF(c.id_cliente = "000000", "Consumidor final", sc.razon_soci) razon_social')
            ->select('sc.cuit, sc.saldo_cc saldo, sc.PROMEDIO_ULT_REC, sc.INHABILITADO, IF(sc.INHABILITADO = "N", 1, 0) habilitado')
            ->select('sc.COD_VENDED codigo_vendedor')
            ->from($db->qn('#__sabullvial_cotizacion', 'c'))
            ->leftJoin('SIT_CLIENTES sc ON (sc.COD_CLIENT = c.id_cliente)');

        $query = $this->getQueryCompraValida('c', $query);
        $query->group('c.id_cliente');

        $query = $this->applyGeneralFilters($query, 'c');

        // Autor (distinto al codigo_vendedor)
        // $authorId = $this->getState('filter.author_id');
        // if ($authorId > 0) {
        //     $vendedor = SabullvialHelper::getVendedor($authorId);
        //     $codigoVendedor = $vendedor->get('codigo', '');
        //     if (!empty($codigoVendedor)) {
        //         $query->where('sc.COD_VENDED = ' . $db->q($codigoVendedor));
        //     } else {
        //         $query->where('c.created_by = ' . (int)$authorId);
        //     }
        // }

        $codigoVendedor = $this->getState('filter.codigo_vendedor');
        if (!empty($codigoVendedor)) {
            $query->where('sc.COD_VENDED = ' . $db->q($codigoVendedor));
        }

        return $query;
    }

    protected function getQueryCompraValida($aliasTableCotizacion, $query)
    {
        $cmpParams = SabullvialHelper::getComponentParams();
        $idEstados = $cmpParams->get('dashboard_crm_estados_cotizacion_productos_mas_vendidos', []);

        $where = $aliasTableCotizacion. '.tango_enviar = 1 AND ' .
            $aliasTableCotizacion . '.tango_fecha_sincronizacion > "1800-01-01 00:00:00" AND ' .
            $aliasTableCotizacion . '.id_estadocotizacion IN (' . implode(',', $idEstados) . ')';

        $query->where($where);

        return $query;
    }

    public function getCotizacionesRealizadas()
    {
        $query = $this->getQueryCotizacionesRealizadas();

        $db = Factory::getDbo();
        $db->setQuery($query);

        try {
            $result = $db->loadObject();
            $result->total = (float) $result->total;
            $result->cantidad = (int) $result->cantidad;

            return $result;
        } catch (\Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    protected function getQueryCotizacionesRealizadas()
    {
        $cmpParams = SabullvialHelper::getComponentParams();
        $idEstados = $cmpParams->get('dashboard_crm_estados_cotizacion_realizadas', []);

        if (empty($idEstados)) {
            throw new \Exception('No hay estados de cotización configurados para las cotizaciones realizadas.');
        }

        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select('sum(IF(c.total_revision > 0, c.total_revision, c.total)) as total, count(c.id) as cantidad')
            ->from($db->qn('#__sabullvial_cotizacion', 'c'))
            ->where('c.id_estadocotizacion IN (' . implode(',', $idEstados) . ')');

        $query = $this->applyGeneralFilters($query, 'c');

        $codigoVendedor = $this->getState('filter.codigo_vendedor');
        if (!empty($codigoVendedor)) {
            $query->innerJoin('SIT_CLIENTES sc ON (sc.COD_CLIENT = c.id_cliente)');
            $query->where('sc.COD_VENDED = ' . $db->q($codigoVendedor));
        }

        return $query;
    }

    public function getCotizacionesVendidas()
    {
        $query = $this->getQueryCotizacionesVendidas();

        $db = Factory::getDbo();
        $db->setQuery($query);

        try {
            $result = $db->loadObject();
            $result->total = (float) $result->total;
            $result->cantidad = (int) $result->cantidad;

            return $result;
        } catch (\Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    protected function getQueryCotizacionesVendidas()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select('sum(IF(c.total_revision > 0, c.total_revision, c.total)) as total')
            ->select('count(c.id) as cantidad')
            ->from($db->qn('#__sabullvial_cotizacion', 'c'))
            ->where('c.id IN (SELECT ID_PEDIDO_WEB FROM `SIT_PEDIDOS_FACTURAS`)');

        $query = $this->applyGeneralFilters($query, 'c');

        $codigoVendedor = $this->getState('filter.codigo_vendedor');
        if (!empty($codigoVendedor)) {
            $query->innerJoin('SIT_CLIENTES sc ON (sc.COD_CLIENT = c.id_cliente)');
            $query->where('sc.COD_VENDED = ' . $db->q($codigoVendedor));
        }

        return $query;

    }

    public function getCotizacionesRechazadas()
    {
        $query = $this->getQueryCotizacionesRechazadas();

        $db = Factory::getDbo();
        $db->setQuery($query);

        try {
            $result = $db->loadObject();
            $result->total = (float) $result->total;
            $result->cantidad = (int) $result->cantidad;

            return $result;
        } catch (\Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    protected function getQueryCotizacionesRechazadas()
    {
        $cmpParams = SabullvialHelper::getComponentParams();
        $idEstados = $cmpParams->get('dashboard_crm_estados_cotizacion_rechazadas', []);

        if (empty($idEstados)) {
            throw new \Exception('No hay estados de cotización configurados para las cotizaciones rechazadas.');
        }

        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select('sum(IF(c.total_revision > 0, c.total_revision, c.total)) as total, count(c.id) as cantidad')
            ->from($db->qn('#__sabullvial_cotizacion', 'c'))
            ->where('c.id_estadocotizacion IN (' . implode(',', $idEstados) . ')');

        $query = $this->applyGeneralFilters($query, 'c');

        $codigoVendedor = $this->getState('filter.codigo_vendedor');
        if (!empty($codigoVendedor)) {
            $query->innerJoin('SIT_CLIENTES sc ON (sc.COD_CLIENT = c.id_cliente)');
            $query->where('sc.COD_VENDED = ' . $db->q($codigoVendedor));
        }

        return $query;
    }

    /**
     * Obtiene las cotizaciones agrupadas por estado.
     * Tiene la suma total de las cotizaciones y la cantidad de cotizaciones por estado.
     * Los estados los devuelve con nombre de estado, color (bg_color) y color_texto (color).
     *
     * @return array
     */
    public function getCotizaciones()
    {
        $query = $this->getQueryCotizaciones();

        $db = Factory::getDbo();
        $db->setQuery($query);

        try {
            return $db->loadObjectList();
        } catch (\Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    /**
     * Obtiene todas las cotizaciones agrupadas por estado.
     * Tiene la suma total de las cotizaciones y la cantidad de cotizaciones por estado.
     * Los estados los devuelve con nombre de estado, color (bg_color) y color_texto (color).
     *
     * @return JDatabaseQuery
     */
    protected function getQueryCotizaciones()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select('c.id_estadocotizacion, e.nombre estado, e.color bg_color, e.color_texto color')
            ->select('sum(IF(c.total_revision > 0, c.total_revision, c.total)) total, count(c.id) cantidad')
            ->from($db->qn('#__sabullvial_cotizacion', 'c'))
            ->innerJoin($db->qn('#__sabullvial_estadocotizacion', 'e') . ' ON (e.id = c.id_estadocotizacion)')
            ->group('c.id_estadocotizacion')
            ->order('e.nombre ASC');

        $query = $this->applyGeneralFilters($query, 'c');

        $codigoVendedor = $this->getState('filter.codigo_vendedor');
        if (!empty($codigoVendedor)) {
            $query->innerJoin('SIT_CLIENTES sc ON (sc.COD_CLIENT = c.id_cliente)');
            $query->where('sc.COD_VENDED = ' . $db->q($codigoVendedor));
        }

        return $query;
    }

    protected function applyGeneralFilters($query, $aliasCotizaciones = 'c')
    {
        $db = Factory::getDbo();

        $dateFrom = $this->getState('filter.date_from');
        if (!empty($dateFrom)) {
            $query->where($aliasCotizaciones . '.created >= ' . $db->quote($dateFrom . ' 00:00:00'));
        }

        $dateTo = $this->getState('filter.date_to');
        if (!empty($dateTo)) {
            $query->where($aliasCotizaciones . '.created <= ' . $db->quote($dateTo . ' 23:59:59'));
        }

        $codigoCliente = $this->getState('filter.codigo_cliente');
        if (!empty($codigoCliente)) {
            $query->where($aliasCotizaciones . '.id_cliente = ' . $db->q($codigoCliente));
        }

        $totalFrom = $this->getState('filter.total_from');
        if (!empty($totalFrom)) {
            $query->where('IF(' . $aliasCotizaciones . '.total_revision > 0, ' . $aliasCotizaciones . '.total_revision, ' . $aliasCotizaciones . '.total) >= ' . (float) $totalFrom);
        }

        $totalTo = $this->getState('filter.total_to');
        if (!empty($totalTo)) {
            $query->where('IF(' . $aliasCotizaciones . '.total_revision > 0, ' . $aliasCotizaciones . '.total_revision, ' . $aliasCotizaciones . '.total) <= ' . (float) $totalTo);
        }

        return $query;
    }

    /**
     * Busca los id de los autores por el codigo del vendedor
     *
     * @param string $codigoVendedor
     *
     * @return array<int>
     */
    protected function findAuthorIdByCodigoVendedor($codigoVendedor)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('item_id')
            ->from($db->qn('#__fields_values', 'fv'))
            ->innerJoin($db->qn('#__fields', 'f') . ' ON (f.id = fv.field_id)')
            ->where('f.name = ' . $db->q('codigo-vendedor'))
            ->where('fv.value = ' . $db->q($codigoVendedor))
            ->where('context = ' . $db->q('com_users.user'));

        $db->setQuery($query);

        return $db->loadColumn();
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
    protected function populateState($ordering = 'c.id', $direction = 'desc')
    {
        $app = JFactory::getApplication();

        // Adjust the context to support modal layouts.
        if ($layout = $app->input->get('layout')) {
            $this->context .= '.' . $layout;
        }

        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        $dateFrom = $this->getUserStateFromRequest($this->context . '.filter.date_from', 'filter_date_from');
        $this->setState('filter.date_from', $dateFrom);

        $dateTo = $this->getUserStateFromRequest($this->context . '.filter.date_to', 'filter_date_to');
        $this->setState('filter.date_to', $dateTo);

        $authorId   = $this->getUserStateFromRequest($this->context . '.filter.author_id', 'filter_author_id');
        $this->setState('filter.author_id', $authorId);

        $codigoCliente = $this->getUserStateFromRequest($this->context . '.filter.codigo_cliente', 'filter_codigo_cliente');
        $this->setState('filter.codigo_cliente', $codigoCliente);

        $totalFrom = $this->getUserStateFromRequest($this->context . '.filter.total_from', 'filter_total_from');
        $this->setState('filter.total_from', $totalFrom);

        $totalTo = $this->getUserStateFromRequest($this->context . '.filter.total_to', 'filter_total_to');
        $this->setState('filter.total_to', $totalTo);

        // $formSubmited = $app->input->post->get('form_submited');
        // if ($formSubmited) {

        //     $authorId = $app->input->post->get('author_id');
        //     $this->setState('filter.author_id', $authorId);
        // }

        // List state information.
        parent::populateState($ordering, $direction);
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
        $id .= ':' . $this->getState('filter.date_to');
        $id .= ':' . $this->getState('filter.date_from');
        $id .= ':' . $this->getState('filter.codigo_cliente');
        $id .= ':' . $this->getState('filter.total_from');
        $id .= ':' . $this->getState('filter.total_to');

        return parent::getStoreId($id);
    }
}

<?php

/**
 * @package     Sabullvial.Administrator
 * @subpackage  com_sabullvial
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * DashboardCrm Model
 *
 * @since  0.0.1
 */
class SabullvialModelDashboardCrm extends JModelAdmin
{
    /**
     * Method to get a table object, load it if necessary.
     *
     * @param   string  $type    The table name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  JTable  A JTable object
     *
     * @since   1.6
     */
    public function getTable($type = 'DashboardCrm', $prefix = 'SabullvialTable', $config = [])
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to get the record form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  mixed    A JForm object on success, false on failure
     *
     * @since   1.6
     */
    public function getForm($data = [], $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm(
            'com_sabullvial.dashboardcrm',
            'dashboardcrm',
            [
                'control' => 'jform',
                'load_data' => $loadData
            ]
        );

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed  The data for the form.
     *
     * @since   1.6
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = Factory::getApplication()->getUserState(
            'com_sabullvial.edit.dashboardcrm.data',
            []
        );

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    public function getTareas()
    {
        $model = ListModel::getInstance('Tareas', 'SabullvialModel', ['ignore_request' => true]);
        // limit 5
        $model->setState('list.limit', 5);

        return $model->getItems();
    }

    public function getProductosMasVendidos()
    {
        /** @var SabullvialModelProductos */
        $model = ListModel::getInstance('Productos', 'SabullvialModel', ['ignore_request' => true]);

        return $model->getListProductosMasVendidos();
    }

    public function getRankingVendedores()
    {
        $model  = ListModel::getInstance('Vendedores', 'SabullvialModel', ['ignore_request' => true]);
        $model->setState('list.ordering', 'cotizaciones_efectividad');
        $model->setState('list.direction', 'DESC');
        $model->setState('list.limit', 10);
        $items = $model->getItems();

        return $items;
    }

    public function getTotalVentasRealizadas()
    {
        $db = Factory::getDbo();
        $query = $this->getVentasRealizadasQuery();
        $query->clear('select')
            ->select('sum(c.total) ventas_totales');

        $db->setQuery($query);

        return (float)$db->loadResult();
    }

    public function getVentasRealizadas()
    {
        $db = Factory::getDbo();
        $query = $this->getVentasRealizadasQuery()
            ->select('sum(c.total) ventas_totales')
            ->select('IF(c.id_cliente = "000000", "000000", sc.cod_client) codcli')
            ->select('IF(c.id_cliente = "000000", "Consumidor final", sc.razon_soci) razon_social')
            ->select('sc.cuit, sc.COD_VENDED codigo_vendedor')
            ->leftJoin($db->qn('SIT_CLIENTES', 'sc') . ' ON (sc.COD_CLIENT = c.id_cliente)')
            ->select('u.username as author')
            ->join('LEFT', $db->quoteName('#__users', 'u') . ' ON u.id = c.created_by')
            ->group('c.id')
            ->setLimit(10)
            ->order('c.id DESC');

        $db->setQuery($query);

        return $db->loadObjectList();
    }

    public function getCantidadVentasRealizadas()
    {
        $db = Factory::getDbo();
        $query = $this->getVentasRealizadasQuery();

        $query->clear('select')->select('COUNT(*)');

        $db->setQuery($query);

        return (int)$db->loadResult();
    }

    public function getVentasRealizadasQuery()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('c.*')
            ->from('#__sabullvial_cotizacion c')
            ->innerJoin('SIT_PEDIDOS_REMITOS r ON (r.id_pedido_web = c.id)')
            ->innerJoin('SIT_PEDIDOS_FACTURAS f ON (f.id_pedido_web = c.id)');
        $query = $this->getQueryCompraValida('c', $query);

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
            $query->where('c.COD_VENDED = ' . $db->q($codigoVendedor));
        }

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
}

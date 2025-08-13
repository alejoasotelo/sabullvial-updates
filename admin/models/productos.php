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
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * SabullvialList Model
 *
 * @since  0.0.1
 */
class SabullvialModelProductos extends ListModel
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
                'id',
                'p.DESCRIPCIO', 'nombre',
                'id_condicionventa',
                'dolar',
                'iva',
                'codigo_sap',
                'selected_id',
                'stock', 'p.STOCKDEP1',
                'p.MARCA', 'marca',
                'pp.PRECIO', 'precio',
                'codigo_lista', 'pp.COD_LISTA'
            ];
        }

        parent::__construct($config);
    }

    /**
     * Method to build an SQL query to load the list data.
     *
     * @return      string  An SQL query
     */
    protected function getListQuery()
    {
        // Initialize variables.
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('p.*, p.COD_ARTICU id')
            ->select('p.COD_ARTICU codigo_sap, p.DESCRIPCIO nombre, p.MARCA marca')
            ->select('p.STOCKDEP1 stock_deposito_1, p.STOCKDEP2 stock_deposito_2, p.STOCKDEP3 stock_deposito_3')
            ->select('(p.STOCKDEP1 + p.STOCKDEP2 + p.STOCKDEP3) stock')
            ->from($db->qn('SIT_ARTICULOS', 'p'));

        $query->select('IFNULL(pp.PRECIO, 0) precio, pp.COD_LISTA codigo_lista')
            ->leftJoin($db->qn('SIT_ARTICULOS_PRECIOS', 'pp') . ' ON (pp.COD_ARTICU = p.COD_ARTICU AND pp.COD_LISTA = ' . (int) SabullvialHelper::LISTA_CONSUMIDOR_FINAL . ')');

        $query->select('pi.id id_productoimagen, pi.images')
        ->leftJoin($db->qn('#__sabullvial_productoimagen', 'pi') . ' ON (pi.id_producto = p.COD_ARTICU)');

        // Filter: like / search
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $sqlSearchQuery = 'p.DESCRIPCIO LIKE #query# OR p.SINONIMO LIKE #query# OR p.COD_ARTICU LIKE #query# OR p.MARCA LIKE #query#';
            $searchQuery = SabullvialHelper::unorderedSearch($search, $sqlSearchQuery, $db);
            $query->where($searchQuery);
        }

        $vendedor = SabullvialHelper::getVendedor();
        $clasificacionesProductos = $vendedor->get('ver.productos');

        $hasTodos = in_array(SabullvialHelper::CLASIFICACION_PRODUCTOS_TODOS, $clasificacionesProductos);

        if (!$hasTodos && count($clasificacionesProductos)) {
            // Verifico si es la clasificacion de productos anterior (legacy)
            $isLegacy = true;
            foreach ($clasificacionesProductos as &$clasificacion) {

                // Verifico si tiene el caracter ( o ) para saber si es una clasificacion de productos anterior
                if (strpos($clasificacion, '(') !== false) {
                    $isLegacy = false;
                    break;
                }
            }

            // Viejo field: ver-productos
            if ($isLegacy) {
                foreach ($clasificacionesProductos as &$clasificacion) {
                    $clasificacion = $db->q($clasificacion);
                }
                $query->where('p.CLASIF_2 IN ('.implode(',', $clasificacionesProductos).')');
            } else {
                $query->where('(' . implode(' OR ', $clasificacionesProductos) . ')');
            }
        }

        // Add the list ordering clause.
        $orderCol	= $this->state->get('list.ordering', 'codigo_sap');
        $orderDirn 	= $this->state->get('list.direction', 'asc');

        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

        return $query;
    }

    /**
     * Method to get an array of data items.
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   1.6
     */
    public function getItems()
    {
        $items = parent::getItems();

        foreach ($items as &$item) {
            // Convert the images field to an array.
            $registry = new Registry($item->images);
            $item->images = $registry->toArray();
        }

        return $items;
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
    protected function populateState($ordering = 'codigo_sap', $direction = 'asc')
    {
        $app = JFactory::getApplication();

        // Adjust the context to support modal layouts.
        if ($layout = $app->input->get('layout')) {
            $this->context .= '.' . $layout;
        }

        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $iva = $this->getUserStateFromRequest($this->context . '.filter.iva', 'filter_iva', true);
        $this->setState('filter.iva', $iva);

        $dolar = $this->getUserStateFromRequest($this->context . '.filter.dolar', 'filter_dolar', false);
        $this->setState('filter.dolar', $dolar);

        $condicionVenta = $this->getUserStateFromRequest($this->context . '.filter.id_condicionventa', 'filter_id_condicionventa', 49);
        $this->setState('filter.id_condicionventa', $condicionVenta);

        $formSubmited = $app->input->post->get('form_submited');

        $selectedId = $this->getUserStateFromRequest($this->context . '.filter.selected_id', 'filter_selected_id', $app->input->getVar('selected_id'));
        $this->setState('filter.selected_id', $selectedId);

        if ($formSubmited) {
        }

        // Load the parameters.
        $params = JComponentHelper::getParams('com_sabullvial');
        $this->setState('params', $params);

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

        if (!empty($form)) {
            $app = JFactory::getApplication();

            // Adjust the context to support modal layouts.
            if ($app->input->get('layout') == 'modal') {
                $form->setFieldAttribute('iva', 'readonly', 'true', 'filter');
                $form->setFieldAttribute('dolar', 'readonly', 'true', 'filter');
                $form->setFieldAttribute('id_condicionventa', 'readonly', 'true', 'filter');
            }
        }

        return $form;
    }

    public function exportImagesToCSV($ids = [])
    {
        // if $ids == [] export all productos to csv from SIT_ARTICULOS and lefjoin productoimagen
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('p.COD_ARTICU codigo_articulo')
            ->from($db->qn('SIT_ARTICULOS', 'p'));

        $query->select('pi.images')
        ->leftJoin($db->qn('#__sabullvial_productoimagen', 'pi') . ' ON (pi.id_producto = p.COD_ARTICU)');

        if (!empty($ids)) {
            $query->where('p.COD_ARTICU IN ('.implode(',', $ids).')');
        }

        $db->setQuery($query);
        $productos = $db->loadObjectList();

        $csv = fopen('php://memory', 'w');
        $separator = ';';

        $header = [
            'codigo_articulo',
            'imagen0',
            'imagen1',
            'imagen2',
            'imagen3',
            'imagen4',
            'imagen5',
            'imagen6',
            'imagen7',
            'imagen8',
            'imagen9',
            'imagen10',
        ];

        fputcsv($csv, $header, $separator);

        foreach ($productos as $producto) {
            $row = [
                $producto->codigo_articulo,
            ];

            $images = json_decode($producto->images);
            for ($i = 0; $i <= 10; $i++) {
                $key = 'images' . $i;
                $row[] = isset($images->{$key}) ? $images->{$key}->path : '';
            }

            fputcsv($csv, $row, $separator);
        }

        rewind($csv);
        $csv = stream_get_contents($csv);

        return $csv;
    }

    public function importImagesFromCSV($fileCsv)
    {
        $csv = fopen($fileCsv, 'r');
        $separator = ';';

        $headers = fgetcsv($csv, 0, $separator);

        if ($headers[0] != 'codigo_articulo' || !in_array($headers[1], ['imagen0', 'imagen1'])) {
            $this->setError('Los encabezados del archivo no son correctos. Los encabezados del CSV tienen que ser: codigo_articulo | imagen1 | imagen2 | imagen3 | imagen4 | imagen5');
            return false;
        }

        $lenHeaders = count($headers);
        while (($row = fgetcsv($csv, 0, $separator)) !== false) {
            $id_producto = $row[0];
            $images = new stdClass();

            $j = 0;
            for ($i = 1; $i < $lenHeaders; $i++) {
                if (!empty($row[$i])) {
                    $key = 'images' . $j;
                    $images->{$key} = new stdClass();
                    $images->{$key}->path = $row[$i];
                    $j++;
                }
            }

            $productoImagen = JTable::getInstance('ProductoImagen', 'SabullvialTable');
            $productoImagen->load(['id_producto' => $id_producto]);
            $productoImagen->images = json_encode($images);

            if (!$productoImagen->id) {
                $productoImagen->id_producto = $id_producto;
            }

            if (!$productoImagen->store()) {
                $this->setError('No se pudo importar la imagen del producto "'.$id_producto.'". Error: ' . $productoImagen->getError());
            }
        }

        return true;
    }

    public function getListProductosMasVendidos($limit = 10)
    {
        $cmpParams = SabullvialHelper::getComponentParams();
        $idEstados = $cmpParams->get('dashboard_crm_estados_cotizacion_productos_mas_vendidos', []);

        // Initialize variables.
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('p.*, p.COD_ARTICU id')
            ->select('p.COD_ARTICU codigo_sap, p.DESCRIPCIO nombre, p.MARCA marca')
            ->select('p.STOCKDEP1 stock_deposito_1, p.STOCKDEP2 stock_deposito_2, p.STOCKDEP3 stock_deposito_3')
            ->select('(p.STOCKDEP1 + p.STOCKDEP2 + p.STOCKDEP3) stock')
            ->from($db->qn('SIT_ARTICULOS', 'p'));

        $query->select('IFNULL(pp.PRECIO, 0) precio, pp.COD_LISTA codigo_lista')
            ->leftJoin($db->qn('SIT_ARTICULOS_PRECIOS', 'pp') . ' ON (pp.COD_ARTICU = p.COD_ARTICU AND pp.COD_LISTA = ' . (int) SabullvialHelper::LISTA_CONSUMIDOR_FINAL . ')');

        $query->select('pi.id id_productoimagen, pi.images')
        ->leftJoin($db->qn('#__sabullvial_productoimagen', 'pi') . ' ON (pi.id_producto = p.COD_ARTICU)');

        $subQuery = $db->getQuery(true);
        $subQuery->select('id')
            ->from($db->qn('#__sabullvial_cotizacion'))
            ->where('tango_enviar = 1 AND tango_fecha_sincronizacion > "1800-01-01 00:00:00" AND id_estadocotizacion IN ('.implode(',', $idEstados).')');

        $dateFrom = $this->getState('filter.date_from');
        $dateTo = $this->getState('filter.date_to');
        if ($dateFrom && $dateTo) {
            $subQuery->where('tango_fecha_sincronizacion BETWEEN ' . $db->q($dateFrom) . ' AND ' . $db->q($dateTo));
        } elseif ($dateFrom) {
            $subQuery->where('DATE(tango_fecha_sincronizacion) >= ' . $db->q($dateFrom));
        } elseif ($dateTo) {
            $subQuery->where('DATE(tango_fecha_sincronizacion) <= ' . $db->q($dateTo));
        }

        $authorId = $this->getState('filter.author_id');
        if ($authorId) {
            $subQuery->where('created_by = ' . (int) $authorId);
        }

        $clienteId = $this->getState('filter.cliente_id');
        if (!empty($clienteId)) {
            $subQuery->where('id_cliente = ' . $db->q($clienteId));
        }

        $totalFrom = $this->getState('filter.total_from');
        if ($totalFrom) {
            $subQuery->where('IF(total_revision > 0, total_revision, total) >= ' . (float) $totalFrom);
        }

        $totalTo = $this->getState('filter.total_to');
        if ($totalTo) {
            $subQuery->where('IF(total_revision > 0, total_revision, total) <= ' . (float) $totalTo);
        }

        $codigoVendedor = $this->getState('filter.codigo_vendedor');
        if (!empty($codigoVendedor)) {
            $subQuery
                ->leftJoin($db->qn('SIT_CLIENTES', 'c') . ' ON (c.COD_CLIENT = id_cliente)')
                ->where('c.COD_VENDED = ' . $db->q($codigoVendedor));
        }

        $query
            ->select('sum(cd.cantidad) cantidad_total')
            ->innerJoin($db->qn('#__sabullvial_cotizaciondetalle', 'cd') . ' ON (cd.id_producto = p.COD_ARTICU AND cd.id_cotizacion IN (' . $subQuery . '))');

        $query->group('p.COD_ARTICU');

        $query->order('cantidad_total ' . $db->escape('desc'));

        $query->setLimit($limit);
        $db->setQuery($query);

        $items = $db->loadObjectList();

        foreach ($items as &$item) {
            // Convert the images field to an array.
            $registry = new Registry($item->images);
            $item->images = $registry->toArray();
        }

        return $items;
    }
}

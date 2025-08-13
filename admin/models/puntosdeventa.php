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
 * SabullvialList Model
 *
 * @since  0.0.1
 */
class SabullvialModelPuntosDeVenta extends JModelList
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
                'nombre',
                'marca',
                'id_condicionventa',
                'dolar',
                'iva',
                'codigo_sap',
                'selected_id',
                'stock',
                'precio'
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

        $query->select('pi.id id_productoimagen, pi.images, pi.url')
        ->leftJoin($db->qn('#__sabullvial_productoimagen', 'pi') . ' ON (pi.id_producto = p.COD_ARTICU)');

        // Filter: like / search
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $sqlSearchQuery = 'p.DESCRIPCIO LIKE #query# OR p.SINONIMO LIKE #query# OR p.COD_ARTICU LIKE #query# OR p.MARCA LIKE #query#';
            $searchQuery = SabullvialHelper::unorderedSearch($search, $sqlSearchQuery, $db);
            $query->where($searchQuery);
        }

        $codArticulo = $this->getState('filter.cod_articulo');
        if (!empty($codArticulo)) {
            $codArticulo = explode(',', $codArticulo);
            foreach ($codArticulo as &$cod) {
                $cod = $db->q($cod);
            }
            $query->where('p.COD_ARTICU IN ('.implode(',', $codArticulo).')');
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

        $codArticulo = $this->getUserStateFromRequest($this->context . '.filter.cod_articulo', 'filter_cod_articulo');
        $this->setState('filter.cod_articulo', $codArticulo);

        $borrado = $this->getUserStateFromRequest($this->context . '.filter.borrado', 'filter_borrado', 0);
        $this->setState('filter.borrado', $borrado);

        $iva = $this->getUserStateFromRequest($this->context . '.filter.iva', 'filter_iva', true);
        $this->setState('filter.iva', $iva);

        $dolar = $this->getUserStateFromRequest($this->context . '.filter.dolar', 'filter_dolar', false);
        $this->setState('filter.dolar', $dolar);

        //$idCliente = $this->getUserStateFromRequest($this->context . '.filter.id_cliente', 'filter_id_cliente', 0);
        //$this->setState('filter.id_cliente', $idCliente);

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
        $id .= ':' . $this->getState('filter.id_cliente');
        $id .= ':' . $this->getState('filter.id_condicionventa');
        $id .= ':' . $this->getState('filter.iva');
        $id .= ':' . $this->getState('filter.dolar');
        $id .= ':' . $this->getState('filter.borrado');
        $id .= ':' . $this->getState('filter.cod_articulo');

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
}

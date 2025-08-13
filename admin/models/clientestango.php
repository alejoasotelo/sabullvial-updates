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

/**
 * SabullvialList Model
 *
 * @since  0.0.1
 */
class SabullvialModelClientesTango extends ListModel
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
                'c.razon_soci', 'razon_social',
                'c.cod_client', 'codigo_cliente',
                'cuit',
                'c.saldo_cc', 'saldo',
                'condicion_venta'
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
        $vendedor = SabullvialHelper::getVendedor();

        // Initialize variables.
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('c.cod_client id, c.cod_client codcli, c.cod_client, c.razon_soci razon_social')
            ->select('c.cuit, c.saldo_cc saldo, c.PROMEDIO_ULT_REC, c.INHABILITADO, IF(c.INHABILITADO = "N", 1, 0) habilitado')
            ->select('c.COD_VENDED codigo_vendedor')
            ->from($db->quoteName('SIT_CLIENTES', 'c'));

        $query->select('cv.DESC_COND condicion_venta, cv.COND_VTA id_condicion_venta')
            ->leftJoin($db->quoteName('SIT_CONDICIONES_VENTA', 'cv') . ' ON (c.cond_vta = cv.COND_VTA)');

        // Filter: like / search
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $findById = stripos($search, 'id:') === 0;
            $findByCodigoCliente = stripos($search, 'codigo_cliente:') === 0;
            if ($findById || $findByCodigoCliente) {
                $searchValue = $findById ? substr($search, 3) : substr($search, 15);
                $query->where('c.cod_client = ' . $db->q($searchValue));
            } else {
                $sqlSearchQuery = 'c.razon_soci LIKE #query# OR c.cuit LIKE #query# OR REPLACE(c.cuit, "-", "") LIKE #query#
                    OR cv.DESC_COND LIKE #query# OR c.cod_client LIKE #query#';
                $searchQuery = SabullvialHelper::unorderedSearch($search, $sqlSearchQuery, $db);
                $query->where($searchQuery);
            }
        }

        // Filter: like / search
        $condicionVenta = $this->getState('filter.condicion_venta');
        if (is_numeric($condicionVenta)) {
            $query->where('cv.COND_VTA = ' . (int)$condicionVenta);
        }

        $clienteRevendedor = $vendedor->get('clienteRevendedor', '');
        $isRevendedor = $vendedor->get('esRevendedor', false);
        $verTodosLosClientes = $vendedor->get('ver.todosLosClientes', false);
        if ($isRevendedor) {
            $query->where('c.cod_client = ' . $db->q($clienteRevendedor));
        } elseif (!empty($vendedor->get('codigo', '')) && !$verTodosLosClientes) {
            $query->where('c.COD_VENDED = ' . $db->q($vendedor->get('codigo')));
        }

        // Add the list ordering clause.
        $orderCol	= $this->state->get('list.ordering', 'c.razon_soci');
        $orderDirn 	= $this->state->get('list.direction', 'asc');

        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

        return $query;
    }

    public function getAllItems($search = '', $limitstart = 0, $limit = 50)
    {
        $this->setState('filter.search', $search);

        // Get a storage key.
        $store = $this->getStoreId('getAllItems');

        // Try to load the data from internal storage.
        if (isset($this->cache[$store])) {
            return $this->cache[$store];
        }

        try {
            // Load the list items and add the items to the internal cache.
            $this->cache[$store] = $this->_getList($this->_getListQuery(), $limitstart, $limit);
        } catch (\RuntimeException $e) {
            $this->setError($e->getMessage());

            return false;
        }

        return $this->cache[$store];
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
    protected function populateState($ordering = 'c.razon_soci', $direction = 'asc')
    {
        $app = JFactory::getApplication();

        // Adjust the context to support modal layouts.
        if ($layout = $app->input->get('layout')) {
            $this->context .= '.' . $layout;
        }

        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $condicionVenta = $this->getUserStateFromRequest($this->context . '.filter.condicion_venta', 'filter_condicion_venta');
        $this->setState('filter.condicion_venta', $condicionVenta);

        /*$formSubmited = $app->input->post->get('form_submited');

        $authorId   = $this->getUserStateFromRequest($this->context . '.filter.author_id', 'filter_author_id');

        if ($formSubmited)
        {

            $authorId = $app->input->post->get('author_id');
            $this->setState('filter.author_id', $authorId);
        }*/

        // Load the parameters.
        $params = JComponentHelper::getParams('com_sabullvial');
        $this->setState('params', $params);

        // List state information.
        parent::populateState($ordering, $direction);
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

        if (is_null($form)) {
            return null;
        }

        $vendedor = SabullvialHelper::getVendedor();
        $isRevendedor = $vendedor->get('esRevendedor', false);

        if ($isRevendedor) {
            $form->removeField('condicion_venta', 'filter');
        }

        return $form;
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
        $id .= ':' . $this->getState('filter.condicion_venta');

        return parent::getStoreId($id);
    }
}

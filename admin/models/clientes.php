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

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\ListModel;

/**
 * SabullvialList Model
 *
 * @since  0.0.1
 */
class SabullvialModelClientes extends ListModel
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
                'razon_social',
                'a.id_estadocliente', 'id_estadocliente',
                'documento_numero',
                'saldo',
                'created', 'a.created',
                'modified', 'a.modified',
                'created_by', 'a.created_by',
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

        $query->select('c.*')
            ->from($db->quoteName('#__sabullvial_cliente', 'c'));

        $query->select('cv.DESC_COND condicion_venta, cv.COND_VTA id_condicion_venta')
            ->leftJoin($db->quoteName('SIT_CONDICIONES_VENTA', 'cv') . ' ON (c.id_condicionventa = cv.COND_VTA)');

        $query->select('u.name vendedor')
            ->leftJoin($db->quoteName('#__users', 'u') . ' ON (c.created_by = u.id)');

        $query->select('v.NOMBRE_VEN nombre_vendedor')
            ->leftJoin($db->quoteName('SIT_VENDEDORES', 'v') . ' ON (v.COD_VENDED = c.codigo_vendedor)');

        $query->select('ec.nombre estadocliente, ec.color estadocliente_bg_color, ec.color_texto estadocliente_color')
            ->select('ec.aprobado estadocliente_aprobado, ec.rechazado estadocliente_rechazado')
            ->select('ec.cancelado estadocliente_cancelado, ec.pendiente estadocliente_pendiente')
            ->join('LEFT', $db->quoteName('#__sabullvial_estadocliente', 'ec') . ' ON (ec.id = c.id_estadocliente)');

        // Filter: like / search
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('c.id = ' . (int) substr($search, 3));
            } else {
                $sqlSearchQuery = '
                    c.razon_social LIKE #query# OR 
                    c.documento_numero LIKE #query# OR 
                    REPLACE(c.documento_numero, "-", "") LIKE #query# OR 
                    cv.DESC_COND LIKE #query# OR
                    v.NOMBRE_VEN LIKE #query#';
                $searchQuery = SabullvialHelper::unorderedSearch($search, $sqlSearchQuery, $db);
                $query->where($searchQuery);
            }
        }

        // Filter: like / search
        $condicionVenta = $this->getState('filter.condicion_venta');

        if (is_numeric($condicionVenta)) {
            $query->where('cv.COND_VTA = ' . (int)$condicionVenta);
        }

        $verTodosLosClientes = $vendedor->get('ver.todosLosClientes', false);
        if (!empty($vendedor->get('codigo', '')) && !$verTodosLosClientes) {
            $query->where('c.codigo_vendedor = ' . $db->q($vendedor->get('codigo')));
        }

        // Add the list ordering clause.
        $orderCol	= $this->state->get('list.ordering', 'c.id');
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
    protected function populateState($ordering = 'c.id', $direction = 'desc')
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

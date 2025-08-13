<?php

/**
 * @package     Sabullvial.Administrator
 * @subpackage  com_sabullvial
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * SabullvialList Model
 *
 * @since  0.0.1
 */
class SabullvialModelTareas extends JModelList
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
                'id_cotizacion', 'cliente',
                'a.id_regla', 'id_regla',
                'a.codigo_cliente', 'a.id_cliente',
                'regla',
                'group_id', 'group',
                'start_date',
                'expiration_date',
                'task_type',
                'task_value',
                'author',
                'author_id',
                'created', 'a.created',
                'modified', 'a.modified',
                'created_by', 'a.created_by',
                'created_by_alias', 'a.created_by_alias',
                'published', 'a.published',
                'codigo_vendedor', 'c.COD_VENDED',
            ];
        }

        parent::__construct($config);
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return	mixed	The data for the form.
     *
     * @since	3.2
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = parent::loadFormData();

        $startDateFrom = $this->getState('filter.start_date_from');
        $startDateTo = $this->getState('filter.start_date_to');

        if (empty($startDateFrom) && empty($startDateTo)) {
            $this->setState('filter.start_date_to', date('Y-m-d') . ' 23:59:59');
            $data->filter['start_date_to'] = date('Y-m-d');
        }

        return $data;
    }

    /**
     * Method to build an SQL query to load the list data.
     *
     * @return      string  An SQL query
     */
    protected function getListQuery()
    {
        // Initialize variables.
        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        // Create the base select statement.
        $query->select('a.id, a.id_cotizacion, a.group_id, a.start_date, a.expiration_date, a.task_type, a.task_value, a.name , a.published , a.created ')
            ->from($db->quoteName('#__sabullvial_tarea', 'a'));

        $query->select('c.RAZON_SOCI cliente')
            ->leftJoin($db->qn('SIT_CLIENTES', 'c') . ' ON (c.COD_CLIENT = a.codigo_cliente)');

        $query->select('cl.razon_social cliente_sistema')
            ->leftJoin($db->qn('#__sabullvial_cliente', 'cl') . ' ON (cl.id = a.id_cliente)');

        $query->select('r.name regla')
            ->leftJoin($db->qn('#__sabullvial_regla', 'r') . ' ON (r.id = a.id_regla)');

        $query->select('co.tango_fecha_sincronizacion cotizacion_tango_fecha_sincronizacion, co.tango_enviar cotizacion_tango_enviar')
            ->leftJoin($db->qn('#__sabullvial_cotizacion', 'co') . ' ON (co.id = a.id_cotizacion)');

        $query->select('ec.nombre estadocotizacion, ec.color estadocotizacion_bg_color, ec.color_texto estadocotizacion_color')
            ->join('LEFT', $db->quoteName('#__sabullvial_estadocotizacion', 'ec') . ' ON ec.id = co.id_estadocotizacion');

        $query->select('ug.title `group`')
            ->leftJoin($db->qn('#__usergroups', 'ug') . ' ON (ug.id = a.group_id)');

        $query->select('(SELECT COUNT(*) FROM ' . $db->qn('#__sabullvial_tareanota') . ' WHERE id_tarea = a.id) as notas_count');

        // Join with users table to get the username of the author
        $query->select('u.username as author')
            ->leftJoin($db->quoteName('#__users', 'u') . ' ON u.id = a.created_by');


        // Filter by author
        $authorId = $this->getState('filter.author_id');

        if (is_numeric($authorId)) {
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
                $sqlSearchQuery = 'a.name LIKE #query# 
                    OR a.id LIKE #query#
                    OR cl.razon_social LIKE #query# 
                    OR c.RAZON_SOCI LIKE #query# 
                    OR a.id_cotizacion LIKE #query# 
                    OR u.username LIKE #query#';
                $searchQuery = SabullvialHelper::unorderedSearch($search, $sqlSearchQuery, $db);
                $query->where($searchQuery);
            }
        }

        $idRegla = $this->getState('filter.id_regla');
        if ($idRegla > 0) {
            $query->where('a.id_regla = ' . (int) $idRegla);
        }

        $idCotizacion = $this->getState('filter.id_cotizacion');
        if ($idCotizacion > 0) {
            $query->where('a.id_cotizacion = ' . (int) $idCotizacion);
        }

        $idCliente = $this->getState('filter.id_cliente');
        if ($idCliente > 0) {
            $query->where('a.id_cliente = ' . (int) $idCliente);
        }

        $codigoCliente = $this->getState('filter.codigo_cliente');
        if (!empty($codigoCliente)) {
            $query->where('a.codigo_cliente = ' . $db->q($codigoCliente));
        }

        $codigoVendedor = $this->getState('filter.codigo_vendedor');
        if (!empty($codigoVendedor)) {
            $query->where('c.COD_VENDED = ' . $db->q($codigoVendedor));
        }

        $taskType = $this->getState('filter.task_type');
        if (!empty($taskType)) {
            $query->where('a.task_type = ' . $db->q($taskType));
        }

        // Filter by published state
        $published = $this->getState('filter.published', '');

        if (is_numeric($published)) {
            $query->where('a.published = ' . (int) $published);
        } elseif ($published === '') {
            $query->where('(a.published IN (0, 1))');
        }

        $startDateFrom = $this->getState('filter.start_date_from');
        $startDateTo = $this->getState('filter.start_date_to');
        if ($startDateFrom && $startDateTo) {
            $query->where('a.start_date BETWEEN ' . $db->q($startDateFrom) . ' AND ' . $db->q($startDateTo));
        } elseif ($startDateFrom) {
            $query->where('DATE(a.start_date) >= ' . $db->q($startDateFrom));
        } elseif ($startDateTo) {
            $query->where('DATE(a.start_date) <= ' . $db->q($startDateTo));
        }

        $vendedor = SabullvialHelper::getVendedor();
        $verTareas = $vendedor->get('ver.tareas', 0);

        if ($verTareas == SabullvialHelper::VER_NINGUNA) {
            $query->where('a.id = 0');
        } elseif ($verTareas == SabullvialHelper::VER_PROPIAS) {
            $user = Factory::getUser();
            $query->where('(a.id IN (SELECT id_tarea FROM ' .$db->qn('#__sabullvial_tareausuario'). ' WHERE user_id = ' . $user->id . ') OR a.created_by = ' . $user->id . ')');
        }

        // Add the list ordering clause.
        $orderCol	= $this->state->get('list.ordering', 'a.id');
        $orderDirn 	= $this->state->get('list.direction', 'desc');

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
    protected function populateState($ordering = 'a.id', $direction = 'desc')
    {
        $app = Factory::getApplication();

        // Adjust the context to support modal layouts.
        if ($layout = $app->input->get('layout')) {
            $this->context .= '.' . $layout;
        }

        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        $idRegla = $this->getUserStateFromRequest($this->context . '.filter.id_regla', 'filter_id_regla', '');
        $this->setState('filter.id_regla', $idRegla);

        $idCotizacion = $this->getUserStateFromRequest($this->context . '.filter.id_cotizacion', 'filter_id_cotizacion', '');
        $this->setState('filter.id_cotizacion', $idCotizacion);

        $idCliente = $this->getUserStateFromRequest($this->context . '.filter.id_cliente', 'filter_id_cliente', '');
        $this->setState('filter.id_cliente', $idCliente);

        $codigoCliente = $this->getUserStateFromRequest($this->context . '.filter.codigo_cliente', 'filter_codigo_cliente', '');
        $this->setState('filter.codigo_cliente', $codigoCliente);

        $taskType = $this->getUserStateFromRequest($this->context . '.filter.task_type', 'filter_task_type', '');
        $this->setState('filter.task_type', $taskType);

        $startDateFrom = $this->getUserStateFromRequest($this->context . '.filter.start_date_from', 'filter_start_date_from', '');
        $this->setState('filter.start_date_from', $startDateFrom);

        $startDateTo = $this->getUserStateFromRequest($this->context . '.filter.start_date_to', 'filter_start_date_to', '');
        $this->setState('filter.start_date_to', $startDateTo);

        $codigoVendedor = $this->getUserStateFromRequest($this->context . '.filter.codigo_vendedor', 'filter_codigo_vendedor', '');
        $this->setState('filter.codigo_vendedor', $codigoVendedor);

        $formSubmited = $app->input->post->get('form_submited');

        $authorId   = $this->getUserStateFromRequest($this->context . '.filter.author_id', 'filter_author_id');

        if ($formSubmited) {

            $authorId = $app->input->post->get('author_id');
            $this->setState('filter.author_id', $authorId);
        }

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
        $id .= ':' . $this->getState('filter.id_regla');
        $id .= ':' . $this->getState('filter.id_cotizacion');
        $id .= ':' . $this->getState('filter.id_cliente');
        $id .= ':' . $this->getState('filter.codigo_cliente');
        $id .= ':' . $this->getState('filter.task_type');
        $id .= ':' . $this->getState('filter.start_date_from');
        $id .= ':' . $this->getState('filter.start_date_to');
        $id .= ':' . $this->getState('filter.codigo_vendedor');

        return parent::getStoreId($id);
    }
}

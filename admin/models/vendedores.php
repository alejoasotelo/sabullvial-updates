<?php

/**
 * @package     Sabullvial.Administrator
 * @subpackage  com_sabullvial
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\Utilities\ArrayHelper;

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * SabullvialList Model
 *
 * @since  0.0.1
 */
class SabullvialModelVendedores extends JModelList
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
                'id', 'u.id',
                'name', 'u.name',
                'username', 'u.username',
                'registerDate', 'u.registerDate',
                'cotizaciones',
                'cotizaciones_aprobadas',
                'cotizaciones_efectividad',
                'cotizaciones_sin_concretar',
                'date_from', 'date_to', 'created'
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

        // Create the base select statement.
        $query->select('u.id as id, u.name, u.username, u.registerDate')
            ->from($db->quoteName('#__users', 'u'));

        $queryCotizaciones = $this->getCotizacionesQuery($db);
        $queryCotizacionesAprobadas = $this->getCotizacionesAprobadasQuery($db);

        $query->select("($queryCotizaciones) cotizaciones, ($queryCotizacionesAprobadas) cotizaciones_aprobadas")
            ->select("IFNULL(($queryCotizacionesAprobadas)/($queryCotizaciones), 0) cotizaciones_efectividad")
            ->select("(($queryCotizaciones) - ($queryCotizacionesAprobadas)) cotizaciones_sin_concretar");

        // Filter by author
        $authorId = $this->getState('filter.author_id');

        if (is_numeric($authorId)) {
            $type = $this->getState('filter.author_id.include', true) ? '= ' : '<>';
            $query->where('u.id ' . $type . (int) $authorId);
        } elseif (is_array($authorId)) {
            $authorId = ArrayHelper::toInteger($authorId);
            $authorId = implode(',', $authorId);
            $query->where('u.id IN (' . $authorId . ')');
        }

        // Filter: like / search
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            $like = $db->quote('%' . $search . '%');
            $query->where('u.name LIKE ' . $like);
        }

        // Add the list ordering clause.
        $orderCol	= $this->state->get('list.ordering', 'name');
        $orderDirn 	= $this->state->get('list.direction', 'asc');

        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

        return $query;
    }

    protected function getCotizacionesQuery($db)
    {
        $query = $db->getQuery(true);
        $query
            ->select('count(*)')
            ->from($db->qn('#__sabullvial_cotizacion', 'c'))
            ->where('c.created_by = u.id');

        $dateFrom = $this->getState('filter.date_from', '');
        if (!empty($dateFrom)) {
            $query->where('c.created >= ' . $db->quote($dateFrom . ' 00:00:00'));
        }

        $dateTo = $this->getState('filter.date_to', '');
        if (!empty($dateTo)) {
            $query->where('c.created <= ' . $db->quote($dateTo . ' 23:59:59'));
        }

        $clienteId = $this->getState('filter.cliente_id');
        if (!empty($clienteId)) {
            $query->where('c.id_cliente = ' . $db->q($clienteId));
        }

        $totalFrom = $this->getState('filter.total_from', '');
        if (!empty($totalFrom)) {
            $query->where('IF(c.total_revision > 0, c.total_revision, c.total) >= ' . $db->q($totalFrom));
        }

        $totalTo = $this->getState('filter.total_to', '');
        if (!empty($totalTo)) {
            $query->where('IF(c.total_revision > 0, c.total_revision, c.total) <= ' . $db->q($totalTo));
        }

        return $query;
    }

    protected function getCotizacionesAprobadasQuery($db)
    {
        $query = $db->getQuery(true);

        $query
            ->select('count(*)')
            ->from($db->qn('#__sabullvial_cotizacion', 'c'))
            ->innerJoin($db->qn('#__sabullvial_estadocotizacion', 'e') . ' ON (e.id = c.id_estadocotizacion AND e.aprobado = 1)')
            ->where('c.created_by = u.id');

        $dateFrom = $this->getState('filter.date_from', '');
        if (!empty($dateFrom)) {
            $query->where('c.created >= ' . $db->quote($dateFrom . ' 00:00:00'));
        }

        $dateTo = $this->getState('filter.date_to', '');
        if (!empty($dateTo)) {
            $query->where('c.created <= ' . $db->quote($dateTo . ' 23:59:59'));
        }

        $clienteId = $this->getState('filter.cliente_id');
        if (!empty($clienteId)) {
            $query->where('c.id_cliente = ' . $db->q($clienteId));
        }

        $totalFrom = $this->getState('filter.total_from', '');
        if (!empty($totalFrom)) {
            $query->where('IF(c.total_revision > 0, c.total_revision, c.total) >= ' . $db->q($totalFrom));
        }

        $totalTo = $this->getState('filter.total_to', '');
        if (!empty($totalTo)) {
            $query->where('IF(c.total_revision > 0, c.total_revision, c.total) <= ' . $db->q($totalTo));
        }

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
    protected function populateState($ordering = 'u.name', $direction = 'asc')
    {
        $app = JFactory::getApplication();

        // Adjust the context to support modal layouts.
        if ($layout = $app->input->get('layout')) {
            $this->context .= '.' . $layout;
        }

        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $dateFrom = $this->getUserStateFromRequest($this->context . '.filter.date_from', 'filter_date_from');
        $this->setState('filter.date_from', $dateFrom);

        $dateTo = $this->getUserStateFromRequest($this->context . '.filter.date_to', 'filter_date_to');
        $this->setState('filter.date_to', $dateTo);

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
        $id .= ':' . serialize($this->getState('filter.author_id'));
        $id .= ':' . $this->getState('filter.date_to');
        $id .= ':' . $this->getState('filter.date_from');

        return parent::getStoreId($id);
    }
}

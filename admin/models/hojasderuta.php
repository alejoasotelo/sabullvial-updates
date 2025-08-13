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
class SabullvialModelHojasDeRuta extends JModelList
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
                'nombre', 'a.nombre',
                'chofer', 'patente',
                'delivery_date',
                'created', 'a.created',
                'modified', 'a.modified',
                'created_by', 'a.created_by',
                'created_by_alias', 'a.created_by_alias',
                'published', 'a.published',
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
        $query->select('a.id, a.nombre, a.chofer, a.patente, a.delivery_date, a.published, a.created')
            ->from($db->quoteName('#__sabullvial_hojaderuta', 'a'));

        $query->select('(SELECT count(*) FROM '.$db->quoteName('#__sabullvial_hojaderutaremito').' WHERE id_hojaderuta = a.id) count_remitos');

        // Join with users table to get the username of the author
        $query->select('u.username as author')
            ->join('LEFT', $db->quoteName('#__users', 'u') . ' ON u.id = a.created_by');


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
            $sqlSearchQuery = '
                a.id LIKE #query# OR
				a.nombre LIKE #query# OR 
				a.chofer LIKE #query# OR 
                a.patente LIKE #query#
			';
            $searchQuery = SabullvialHelper::unorderedSearch($search, $sqlSearchQuery, $db);
            $query->where($searchQuery);
        }

        // Filter by published state
        $published = $this->getState('filter.published');

        if (is_numeric($published)) {
            $query->where('a.published = ' . (int) $published);
        } elseif ($published === '') {
            $query->where('(a.published IN (0, 1))');
        }

        // Add the list ordering clause.
        $orderCol	= $this->state->get('list.ordering', 'a.created');
        $orderDirn 	= $this->state->get('list.direction', 'desc');

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
    protected function populateState($ordering = 'a.created', $direction = 'desc')
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

        return parent::getStoreId($id);
    }
}

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
 * Cotizacion Model
 *
 * @since  0.0.1
 */
class SabullvialModelCotizacion extends JModelItem
{
    /**
     * Model context string.
     *
     * @var        string
     */
    protected $_context = 'com_sabullvial.cotizacion';

    /**
     * @var object item
     */
    protected $item;

    /**
     * Method to auto-populate the model state.
     *
     * This method should only be called once per instantiation and is designed
     * to be called on the first call to the getState() method unless the model
     * configuration flag to ignore the request is set.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return	void
     * @since	2.5
     */
    protected function populateState()
    {
        // Get the message id
        $jinput = JFactory::getApplication()->input;
        $id     = $jinput->get('id', 1, 'INT');
        $this->setState('cotizacion.id', $id);

        // Load the parameters.
        //$this->setState('params', JFactory::getApplication()->getParams());
        parent::populateState();
    }

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
    public function getTable($type = 'Cotizacion', $prefix = 'SabullvialTable', $config = [])
    {
        //JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Get the message
     * @return object The message to be displayed to the user
     */
    public function getItem()
    {
        if (!isset($this->item)) {
            $id    = $this->getState('cotizacion.id');
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('h.greeting, h.params, c.title as category')
                ->from('#__sabullvialcotizacion as h')
                ->where('h.id=' . (int)$id);
            $db->setQuery((string)$query);

            $this->item = $db->loadObject();
        }
        return $this->item;
    }
}

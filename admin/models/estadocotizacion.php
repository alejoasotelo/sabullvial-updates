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
 * EstadoCotizacion Model
 *
 * @since  0.0.1
 */
class SabullvialModelEstadoCotizacion extends JModelAdmin
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
    public function getTable($type = 'EstadoCotizacion', $prefix = 'SabullvialTable', $config = [])
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
            'com_sabullvial.estadocotizacion',
            'estadocotizacion',
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
        $data = JFactory::getApplication()->getUserState(
            'com_sabullvial.edit.estadocotizacion.data',
            []
        );

        if (empty($data)) {
            $data = $this->getItem();
        }

        if (is_array($data)) {
            $data['title'] = $data['nombre'];
        } else {
            $data->title = $data->nombre;
        }

        return $data;
    }

    /**
     * Method to check if it's OK to delete a message. Overrides JModelAdmin::canDelete
     */
    protected function canDelete($record)
    {
        if (!empty($record->id)) {
            return JFactory::getUser()->authorise("core.delete", "com_sabullvial.estadocotizacion." . $record->id);
        }
    }

    /**
     * Method to test whether a record can have its state edited.
     *
     * @param   object  $record  A record object.
     *
     * @return  boolean  True if allowed to change the state of the record. Defaults to the permission set in the component.
     *
     * @since   1.6
     */
    protected function canEditState($record)
    {
        // Check for existing article.
        if (!empty($record->id)) {
            return JFactory::getUser()->authorise('core.edit.state', 'com_sabullvial.estadocotizacion.' . (int) $record->id);
        }

        // Default to component settings if neither article nor category known.
        return parent::canEditState($record);
    }
}

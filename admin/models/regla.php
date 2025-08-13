<?php

/**
 * @package     Sabullvial.Administrator
 * @subpackage  com_sabullvial
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\Registry\Registry;

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Regla Model
 *
 * @since  0.0.1
 */
class SabullvialModelRegla extends JModelAdmin
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
    public function getTable($type = 'Regla', $prefix = 'SabullvialTable', $config = [])
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
            'com_sabullvial.regla',
            'regla',
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
            'com_sabullvial.edit.regla.data',
            []
        );

        if (!empty($data)) {
            return $data;
        }

        $data = $this->getItem();

        if (is_null($data->id)) {
            return $data;
        }

        // $taskDataDecompressed = gzuncompress(base64_decode($data->data));
        // $taskData = new Registry($taskDataDecompressed);
        // $data->setProperties($taskData->toArray());

        $taskData = new Registry($data->data);
        $data->setProperties($taskData->toArray());

        return $data;
    }

    public function save($data = [])
    {
        // filter $data array by name start with task_
        $taskFields = $this->filterFields($data);
        $taskData = new Registry($taskFields);
        $data['data'] = $taskData->toString();

        // $taskDataCompressed = gzcompress($taskData->toString(), 9);
        // $data['data'] = base64_encode($taskDataCompressed);

        return parent::save($data);

    }

    protected function filterFields($fields)
    {
        $excludeFields = [
            'id',
            'name',
            'description',
            'created',
            'created_by',
            'created_by_alias',
            'modified',
            'modified_by',
            'published'
        ];

        foreach ($excludeFields as $key) {
            if (isset($fields[$key])) {
                unset($fields[$key]);
            }
        }

        return $fields;
    }
}

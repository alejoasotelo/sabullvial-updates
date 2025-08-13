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

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;

FormHelper::loadFieldClass('textarea');

/**
 * TareaNota Form Field class for the Sabullvial component
 *
 * @since  0.0.1
 */
class JFormFieldTareaNota extends \JFormFieldTextarea
{
    /**
     * The field type.
     *
     * @var         string
     */
    protected $type = 'TareaNota';

    /**
     * Layout to render the label
     *
     * @var  string
     */
    protected $layout = 'joomla.form.field.tareanota';

    /**
     * Get the data that is going to be passed to the layout
     *
     * @return  array
     */
    public function getLayoutData()
    {
        // Get the basic field data
        $data = parent::getLayoutData();

        $itemId = $this->form->getValue('id');

        $extraData = [
            'itemId' => $itemId,
            'notas' => $this->getNotas($itemId),
        ];

        return array_merge($data, $extraData);
    }

    protected function getNotas($tareaId)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select('a.id, a.body, a.created');
        $query->from('#__sabullvial_tareanota AS a');

        $query->select('u.username as author')
            ->leftJoin($db->quoteName('#__users', 'u') . ' ON u.id = a.created_by');

        $query->where('a.id_tarea = ' . (int) $tareaId);
        $query->order('a.created DESC');

        $db->setQuery($query);

        return $db->loadObjectList();
    }
}

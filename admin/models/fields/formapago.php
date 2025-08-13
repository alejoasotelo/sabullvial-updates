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

JFormHelper::loadFieldClass('list');

/**
 * FormaPago Form Field class for the Sabullvial component
 *
 * @since  0.0.1
 */
class JFormFieldFormaPago extends JFormFieldList
{
    /**
     * The field type.
     *
     * @var         string
     */
    protected $type = 'FormaPago';

    /**
     * Method to get a list of options for a list input.
     *
     * @return  array  An array of JHtml options.
     */
    protected function getOptions()
    {
        $db    = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('id, nombre')
            ->from('#__sabullvial_formapago')
            ->order('nombre ASC');
        $db->setQuery((string) $query);
        $items = $db->loadObjectList();
        $options  = [];

        if ($items) {
            foreach ($items as $item) {
                $options[] = JHtml::_('select.option', $item->id, $item->nombre);
            }
        }

        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }
}

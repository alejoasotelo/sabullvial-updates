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
 * EstadoCotizacion Form Field class for the Sabullvial component
 *
 * @since  0.0.1
 */
class JFormFieldEstadoCotizacion extends JFormFieldList
{
    /**
     * The field type.
     *
     * @var         string
     */
    protected $type = 'EstadoCotizacion';

    /**
     * Method to get a list of options for a list input.
     *
     * @return  array  An array of JHtml options.
     */
    protected function getOptions()
    {
        $excludeAllExcept = !empty($this->element['excludeallexcept']) ? explode(',', $this->element['excludeallexcept']) : [];

        $user = JFactory::getUser();
        $db    = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('id, nombre');
        $query->from('#__sabullvial_estadocotizacion')
            ->order('nombre');

        if (count($excludeAllExcept)) {
            $query->where('id IN (' . implode(',', $excludeAllExcept) . ')');
        }

        if (!$user->authorise('core.admin')) {
            $userAccessLevels = implode(',', $user->getAuthorisedViewLevels());
            $query->where('access IN (' . $userAccessLevels . ')');
        }

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

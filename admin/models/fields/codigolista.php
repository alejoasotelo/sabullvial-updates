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
 * Cliente Form Field class for the Sabullvial component
 *
 * @since  0.0.1
 */
class JFormFieldCodigoLista extends JFormFieldList
{
    /**
     * The field type.
     *
     * @var         string
     */
    protected $type = 'CodigoLista';

    /**
     * Method to get a list of options for a list input.
     *
     * @return  array  An array of JHtml options.
     */
    protected function getOptions()
    {
        $db    = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('ap.COD_LISTA id, ap.COD_LISTA name')
            ->from($db->quoteName('SIT_ARTICULOS_PRECIOS', 'ap'))
            ->group('ap.COD_LISTA');

        $db->setQuery((string) $query);
        $items = $db->loadObjectList();
        $options  = [];

        if ($items) {
            foreach ($items as $item) {
                $name = JText::sprintf('COM_SABULLVIAL_FIELD_CODIGO_LISTA_TEXT', $item->name);
                $options[] = JHtml::_('select.option', $item->id, $name);
            }
        }

        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }
}

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

use Joomla\CMS\Table\Table;

JFormHelper::loadFieldClass('list');

/**
 * Cliente Form Field class for the Sabullvial component
 *
 * @since  0.0.1
 */
class JFormFieldTransporte extends JFormFieldList
{
    /**
     * The field type.
     *
     * @var         string
     */
    protected $type = 'Transporte';

    /**
     * Method to get a list of options for a list input.
     *
     * @return  array  An array of JHtml options.
     */
    protected function getOptions()
    {
        Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_sabullvial/tables');
        $table = Table::getInstance('BullvialSitTransportes', 'SabullvialTable');
        $items = $table->getAll();

        $options  = [];

        if ($items) {
            foreach ($items as $item) {
                $options[] = JHtml::_('select.option', $item->id, $item->name);
            }
        }

        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }
}

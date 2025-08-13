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
 * SitVendedor Form Field class for the Sabullvial component
 *
 * @since  0.0.1
 */
class JFormFieldSitVendedor extends JFormFieldList
{
    /**
     * The field type.
     *
     * @var         string
     */
    protected $type = 'SitVendedor';

    /**
     * Method to get a list of options for a list input.
     *
     * @return  array  An array of JHtml options.
     */
    protected function getOptions()
    {
        $db    = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query
            ->select('COD_VENDED id, NOMBRE_VEN name, INHABILITADO inhabilitado')
            ->from('SIT_VENDEDORES')
            ->order('name');

        $showOnlyHabilitados = filter_var((string) $this->element['show_inhabilitados'], FILTER_VALIDATE_BOOLEAN) === false;
        $showCodigoLabel = filter_var((string) $this->element['show_codigo_label'], FILTER_VALIDATE_BOOLEAN);

        if ($showOnlyHabilitados) {
            $query->where('INHABILITADO = 0');
        }

        $db->setQuery((string) $query);
        $items = $db->loadObjectList();
        $options  = [];

        if (!$items) {
            return parent::getOptions();
        }

        foreach ($items as $item) {
            $name = '';

            if ($showCodigoLabel) {
                $name .= $item->id . ' - ';
            }

            $name .= mb_convert_case($item->name, MB_CASE_TITLE, 'UTF-8');

            if ((int) $item->inhabilitado == 1) {
                $name .= ' (Inhabilitado)';
            }

            $options[] = JHtml::_('select.option', $item->id, $name);
        }

        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }
}

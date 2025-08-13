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

use Joomla\CMS\Factory;

/**
 * PuntosDeVenta View
 *
 * @since  0.0.1
 */
class SabullvialViewRemitos extends JViewLegacy
{
    /**
     * Display the Productos view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        // Get data from the model
        $this->items		= $this->get('Items');
        $this->pagination	= $this->get('Pagination');
        $this->state			= $this->get('State');
        //$this->filter_order 	= $app->getUserStateFromRequest($context . 'filter_order', 'filter_order', 'greeting', 'cmd');
        //$this->filter_order_Dir = $app->getUserStateFromRequest($context . 'filter_order_Dir', 'filter_order_Dir', 'asc', 'cmd');
        $this->filterForm    	= $this->get('FilterForm');
        $this->activeFilters 	= $this->get('ActiveFilters');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            echo new JResponseJson(null, $errors, false);
            die();
        }

        $data = [
            'items' => $this->items,
            'pagination' => $this->pagination,
            'vendedor' => SabullvialHelper::getVendedor(),
            'state' => $this->state,
            'filterForm' => $this->filterForm,
            'activeFilters' => $this->activeFilters,
        ];

        Factory::getApplication()->setHeader('Content-Type', 'application/json');
        echo new JResponseJson($data);
    }
}

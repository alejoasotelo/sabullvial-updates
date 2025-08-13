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

use Joomla\CMS\HTML\HTMLHelper;

/**
 * Cotizaciones View
 *
 * @since  0.0.1
 */
class SabullvialViewCotizaciones extends JViewLegacy
{
    public $params;

    /**
     * Display the Cotizaciones view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        // Get application
        $app = JFactory::getApplication();
        $context = "sabullvial.list.admin.cotizacion";

        $params = JComponentHelper::getParams('com_sabullvial');

        // Get data from the model
        $this->items		= $this->get('Items');
        $this->pagination	= $this->get('Pagination');
        $this->state			= $this->get('State');
        $this->params = $params;
        //$this->filter_order 	= $app->getUserStateFromRequest($context . 'filter_order', 'filter_order', 'greeting', 'cmd');
        //$this->filter_order_Dir = $app->getUserStateFromRequest($context . 'filter_order_Dir', 'filter_order_Dir', 'asc', 'cmd');
        $this->filterForm    	= $this->get('FilterForm');
        $this->activeFilters 	= $this->get('ActiveFilters');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode('<br />', $errors));

            return false;
        }

        // Set the submenu
        SabullvialHelper::addSubmenu('cotizaciones');

        // Set the toolbar and number of found items
        $this->addToolBar();

        // Display the template
        parent::display($tpl);

        // Set the document
        $this->setDocument();
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function addToolBar()
    {
        $canDo = JHelperContent::getActions('com_sabullvial', 'cotizacion');
        $user  = JFactory::getUser();

        $title = JText::_('COM_SABULLVIAL_COTIZACIONES_MANAGER');

        if ($this->pagination->total) {
            $title .= ' <span style="font-size: 0.85em; vertical-align: middle;" class="hasTooltip" title=' . JText::_('COM_SABULLVIAL_COTIZACIONES_CANTIDAD').'>(' . $this->pagination->total . ')</span>';
        }

        JToolBarHelper::title($title, 'stack cotizacion');

        // if ($canDo->get('core.create') || count($user->getAuthorisedCategories('com_sabullvial', 'core.create')) > 0)
        // {
        // 	JToolbarHelper::addNew('cotizacion.add');
        // }

        if ($canDo->get('core.edit') || $canDo->get('core.edit.own')) {
            JToolbarHelper::editList('cotizacion.edit');
        }

        if (SabullvialHelper::isUserAdministrador()) {
            JToolbarHelper::publishList('cotizaciones.aprobado', 'JACTION_APPROVE');
            JToolbarHelper::unpublishList('cotizaciones.rechazado', 'JACTION_REJECT');
        }

        if ($canDo->get('core.delete')) {
            JToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'cotizaciones.delete');
        }

        if ($user->authorise('core.admin', 'com_sabullvial') || $user->authorise('core.options', 'com_sabullvial')) {
            JToolbarHelper::preferences('com_sabullvial');
        }
    }

    /**
     * Method to set up the document properties
     *
     * @return void
     */
    protected function setDocument()
    {
        $document = JFactory::getDocument();
        $document->setTitle(JText::_('COM_SABULLVIAL_ADMINISTRATION'));

        HTMLHelper::stylesheet('com_sabullvial/default.css', ['version' => 'auto', 'relative' => true]);
    }
}

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
 * Choferes View
 *
 * @since  0.0.1
 */
class SabullvialViewChoferes extends JViewLegacy
{
    /**
     * Display the Choferes view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        // Get application
        $app = JFactory::getApplication();
        $context = "sabullvial.list.admin.chofer";

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
            JError::raiseError(500, implode('<br />', $errors));

            return false;
        }

        // Set the submenu
        SabullvialHelper::addSubmenu('choferes');

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
        $canDo = JHelperContent::getActions('com_sabullvial', 'chofer');
        $user  = JFactory::getUser();

        $title = JText::_('COM_SABULLVIAL_CHOFERES_MANAGER');

        if ($this->pagination->total) {
            $title .= ' <span class="page-title-count">(' . $this->pagination->total . ')</span>';
        }

        JToolBarHelper::title($title, 'chofer');
        JToolbarHelper::addNew('chofer.add');
        JToolbarHelper::editList('chofer.edit');

        if ($canDo->get('core.edit.state')) {
            JToolbarHelper::publish('choferes.publish', 'JTOOLBAR_PUBLISH', true);
            JToolbarHelper::unpublish('choferes.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            JToolbarHelper::trash('choferes.trash');
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

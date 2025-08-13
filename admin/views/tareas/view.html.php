<?php

/**
 * @package     Sabullvial.Administrator
 * @subpackage  com_sabullvial
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use Joomla\CMS\HTML\HTMLHelper;

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Tareas View
 *
 * @since  0.0.1
 */
class SabullvialViewTareas extends JViewLegacy
{
    /**
     * Display the Tareas view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        // Se cambio el orden de la carga del state, filterForm, etc. para que funcione el filter.start_date_to
        $this->state = $this->get('State');
        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

        // Get data from the model
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');


        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode('<br />', $errors));

            return false;
        }

        // Set the submenu
        SabullvialHelper::addSubmenu('tareas');

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
        $title = JText::_('COM_SABULLVIAL_TAREAS_MANAGER');

        if ($this->pagination->total) {
            $title .= ' <span style="font-size: 0.85em; vertical-align: middle;">(' . $this->pagination->total . ')</span>';
        }

        JToolBarHelper::title($title, 'tarea');
        JToolbarHelper::addNew('tarea.add');
        JToolbarHelper::editList('tarea.edit');
        JToolbarHelper::deleteList('', 'tareas.delete');
        JToolBarHelper::preferences('com_sabullvial');
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

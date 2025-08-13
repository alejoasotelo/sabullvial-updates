<?php

/**
 * @package     Sabullvial.Administrator
 * @subpackage  com_sabullvial
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\HTML\HTMLHelper;

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * TareasCalendario View
 *
 * @since  0.0.1
 */
class SabullvialViewTareasCalendario extends JViewLegacy
{
    /**
     * Display the TareasCalendario view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        // $modelTareas = ListModel::getInstance('Tareas', 'SabullvialModel');
        // $this->setModel($modelTareas, true);
        // Get application
        $app = JFactory::getApplication();
        $context = "sabullvial.list.admin.tareacalendario";

        // Get data from the model
        // $this->items		= $this->get('Items');
        // $this->pagination	= $this->get('Pagination');
        // $this->state			= $this->get('State');
        //$this->filter_order 	= $app->getUserStateFromRequest($context . 'filter_order', 'filter_order', 'greeting', 'cmd');
        //$this->filter_order_Dir = $app->getUserStateFromRequest($context . 'filter_order_Dir', 'filter_order_Dir', 'asc', 'cmd');
        // $this->filterForm    	= $this->get('FilterForm');
        // $this->activeFilters 	= $this->get('ActiveFilters');

        // Check for errors.
        // if (count($errors = $this->get('Errors'))) {
        // 	JError::raiseError(500, implode('<br />', $errors));

        // 	return false;
        // }

        // Set the submenu
        SabullvialHelper::addSubmenu('tareascalendario');

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
        $title = JText::_('COM_SABULLVIAL_TAREAS_CALENDARIO_MANAGER');

        // if ($this->pagination->total) {
        // 	$title .= "<span style='font-size: 0.5em; vertical-align: middle;'>(" . $this->pagination->total . ")</span>";
        // }

        JToolBarHelper::title($title, 'tareacalendario');
        // JToolbarHelper::addNew('tareacalendario.add');
        // JToolbarHelper::editList('tareacalendario.edit');
        // JToolbarHelper::deleteList('', 'tareascalendario.delete');
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

        $cmpParams = SabullvialHelper::getComponentParams();
        $diasParaExpiracion = $cmpParams->get('tareas_dias_alerta_expiracion', 3);

        $document->addScriptOptions('com_sabullvial', [
            'vendedor' => SabullvialHelper::getVendedor(),
            'token' => JSession::getFormToken(),
            'uriRoot' => JUri::root(),
            'diasParaExpiracion' => $diasParaExpiracion,
        ]);

        HTMLHelper::script('com_sabullvial/libs/fullcalendar/dist/index.global.min.js', ['version' => 'auto', 'relative' => true]);
        HTMLHelper::script('com_sabullvial/libs/fullcalendar/packages/core/locales/es.global.min.js', ['version' => 'auto', 'relative' => true]);
        HTMLHelper::script('com_sabullvial/fullcalendar-bootstrap.js', ['version' => 'auto', 'relative' => true]);
        HTMLHelper::script('com_sabullvial/tareascalendario.js', ['version' => 'auto', 'relative' => true]);

        HTMLHelper::stylesheet('com_sabullvial/default.css', ['version' => 'auto', 'relative' => true]);
        HTMLHelper::stylesheet('com_sabullvial/modal-fullscreen.min.css', ['version' => 'auto', 'relative' => true]);
        HTMLHelper::stylesheet('com_sabullvial/tareascalendario.min.css', ['version' => 'auto', 'relative' => true]);
    }
}

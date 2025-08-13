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
 * Productos View
 *
 * @since  0.0.1
 */
class SabullvialViewProductos extends JViewLegacy
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
        // Get application
        $app = JFactory::getApplication();
        $context = "sabullvial.list.admin.producto";

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

        if ($this->getLayout() !== 'modal') {
            // Set the submenu
            SabullvialHelper::addSubmenu('productos');

            // Set the toolbar and number of found items
            $this->addToolBar();
        }

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
        $canDo = JHelperContent::getActions('com_sabullvial');

        $title = JText::_('COM_SABULLVIAL_PRODUCTOS_MANAGER');

        if ($this->pagination->total) {
            $title .= ' <span style="font-size: 0.85em; vertical-align: middle;cursor:pointer" class="hasTooltip" title="Cantidad de productos">('. $this->pagination->total . ')</span>';
        }

        JToolBarHelper::title($title, 'producto');

        $bar = JToolbar::getInstance('toolbar');
        $layout = new JLayoutFile('joomla.toolbar.confirm');

        $dhtml = $layout->render([
            'text' => JText::_('JTOOLBAR_IMPORTAR_IMAGENES'),
            'doTask' => "jQuery( '#modalImportarImagenes' ).modal('show'); return true;",
            'class' => 'icon-download'
        ]);
        $bar->appendButton('Custom', $dhtml, 'batch');

        JToolBarHelper::custom('productos.exportImages', 'upload', '', 'Exportar CSV', false, false);

        $message = "alert('".JText::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST') . "');";
        $dhtml = $layout->render([
            'text' => JText::_('JTOOLBAR_SUBIR_IMAGENES'),
            'doTask' => "if (document.adminForm.boxchecked.value==0){".$message."}else{jQuery( '#modalSubirImagenes' ).modal('show'); return true;}",
            'class' => 'icon-image'
        ]);
        $bar->appendButton('Custom', $dhtml, 'batch');

        if ($canDo->get('core.admin')) {
            JToolBarHelper::divider();
            JToolBarHelper::preferences('com_sabullvial');
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
        HTMLHelper::script('com_sabullvial/splide.min.js', ['version' => 'auto', 'relative' => true]);
        HTMLHelper::stylesheet('com_sabullvial/splide.min.css', ['version' => 'auto', 'relative' => true]);
    }
}

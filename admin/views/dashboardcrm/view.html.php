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
 * DashboardCrm View
 *
 * @since  0.0.1
 */
class SabullvialViewDashboardCrm extends JViewLegacy
{
    /**
     * View form
     *
     * @var form
     */
    protected $form = null;

    /**
     * Display the DashboardCrm view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        // Get the Data
        $this->tareas = $this->get('Tareas');
        $this->productos = $this->get('ProductosMasVendidos');
        $this->vendedores = $this->get('RankingVendedores');
        $this->totalVentasRealizadas = $this->get('TotalVentasRealizadas');
        $this->ventasRealizadas = $this->get('VentasRealizadas');
        $this->cantidadVentasRealizadas = $this->get('CantidadVentasRealizadas');
        $this->clientesUltimaCompra = $this->get('ClientesConUltimaCompra');
        $this->clientesQueMasCompran = $this->get('ClientesQueMasCompran');
        $this->clientesQueMenosCompran = $this->get('ClientesQueMenosCompran');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode('<br />', $errors));

            return false;
        }

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
        $title = JText::_('COM_SABULLVIAL_DASHBOARD_CRM_MANAGER_TITLE');

        JToolBarHelper::title($title, 'dashboardcrm');

        // JToolBarHelper::preferences('com_sabullvial');
    }

    /**
     * Method to set up the document properties
     *
     * @return void
     */
    protected function setDocument()
    {
        $document = JFactory::getDocument();
        $document->setTitle(JText::_('COM_SABULLVIAL_DASHBOARD_CRM_MANAGER_TITLE'));

        HTMLHelper::stylesheet('com_sabullvial/default.css', ['version' => 'auto', 'relative' => true]);
        HTMLHelper::stylesheet('com_sabullvial/dashboardcrm.min.css', ['version' => 'auto', 'relative' => true]);
    }
}

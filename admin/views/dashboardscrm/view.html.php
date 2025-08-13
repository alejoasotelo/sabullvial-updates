<?php

/**
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or exit('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * DashboardsCrm View.
 *
 * @since  0.0.1
 */
class SabullvialViewDashboardsCrm extends JViewLegacy
{
    /**
     * Display the DashboardsCrm view.
     *
     * @param string $tpl the name of the template file to parse; automatically searches through the template paths
     *
     * @return void
     */
    public function display($tpl = null)
    {
        $this->state = $this->get('State');
        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

        $this->cotizaciones = $this->get('Cotizaciones');
        $this->cotizacionesRealizadas = $this->get('CotizacionesRealizadas');
        $this->cotizacionesVendidas = $this->get('CotizacionesVendidas');
        $this->cotizacionesRechazadas = $this->get('CotizacionesRechazadas');

        // Get application
        $app = JFactory::getApplication();
        $context = 'sabullvial.list.admin.dashboardcrm';

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode('<br />', $errors));

            return false;
        }

        // Set the submenu
        SabullvialHelper::addSubmenu('dashboardscrm');

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
     * @return void
     *
     * @since   1.6
     */
    protected function addToolBar()
    {
        $canDo = JHelperContent::getActions('com_sabullvial');
        $title = JText::_('COM_SABULLVIAL_DASHBOARD_CRM_MANAGER_TITLE');

        JToolBarHelper::title($title, 'chart');

        if ($canDo->get('core.admin')) {
            JToolBarHelper::divider();
            JToolBarHelper::preferences('com_sabullvial');
        }
    }

    /**
     * Method to set up the document properties.
     *
     * @return void
     */
    protected function setDocument()
    {
        $document = JFactory::getDocument();
        $document->setTitle(JText::_('COM_SABULLVIAL_DASHBOARD_CRM_MANAGER_TITLE'));

        $listForm = $this->filterForm->getGroup('list');
        [$ordering, $direction] = explode(' ', $listForm['list_fullordering']->value);

        $app = JFactory::getApplication();
        $budgetValues = [
            'realizadas' => $app->getUserState('com_sabullvial.dashboardscrm.realizadas', 0),
            'vendidas' => $app->getUserState('com_sabullvial.dashboardscrm.vendidas', 0),
            'rechazadas' => $app->getUserState('com_sabullvial.dashboardscrm.rechazadas', 0)
        ];

        $document->addScriptOptions('com_sabullvial', [
            'vendedor' => SabullvialHelper::getVendedor(),
            'activeFilters' => $this->activeFilters,
            'ordering' => $ordering,
            'direction' => $direction,
            'token' => JSession::getFormToken(),
            'uriRoot' => JUri::root(),
            'cotizaciones' => [
                'todas' => $this->cotizaciones,
                'realizadas' => $this->cotizacionesRealizadas,
                'vendidas' => $this->cotizacionesVendidas,
                'rechazadas' => $this->cotizacionesRechazadas
            ],
            'budgetValues' => $budgetValues,
        ]);

        HTMLHelper::stylesheet('com_sabullvial/default.css', ['version' => 'auto', 'relative' => true]);
        HTMLHelper::stylesheet('com_sabullvial/dashboardcrm.min.css', ['version' => 'auto', 'relative' => true]);

        HTMLHelper::_('jquery.framework');

        $isVueProd = JComponentHelper::getParams('com_sabullvial')->get('vue_production', false);
        HTMLHelper::script('com_sabullvial/vue.' . ($isVueProd ? 'global.prod.js' : 'global.js'), ['version' => '3.5.18', 'relative' => true]);
        HTMLHelper::script('com_sabullvial/tools.js', ['version' => 'auto', 'relative' => true]);
        HTMLHelper::script('com_sabullvial/libs/charts.js', ['version' => '4.5.0', 'relative' => true]);
        HTMLHelper::script('com_sabullvial/components/chosen-select.js', ['version' => 'auto', 'relative' => true]);
        HTMLHelper::script('com_sabullvial/components/card-budget.js', ['version' => 'auto', 'relative' => true]);
        HTMLHelper::script('com_sabullvial/dashboardcrm.js', ['version' => 'auto', 'relative' => true]);

        Text::script('COM_SABULLVIAL_DASHBOARD_CRM_COTIZACIONES_REALIZADAS_CANTIDAD');
        Text::script('COM_SABULLVIAL_DASHBOARD_CRM_COTIZACIONES_VENDIDAS_CANTIDAD');
        Text::script('COM_SABULLVIAL_DASHBOARD_CRM_COTIZACIONES_RECHAZADAS_CANTIDAD');
        Text::script('COM_SABULLVIAL_DASHBOARD_CRM_CHART_TYPE_BAR');
        Text::script('COM_SABULLVIAL_DASHBOARD_CRM_CHART_TYPE_PIE');
        Text::script('COM_SABULLVIAL_DASHBOARD_CRM_CHART_TYPE_POLAR_AREA');
        Text::script('COM_SABULLVIAL_DASHBOARD_CRM_CHART_TYPE_DOUGHNUT');
    }
}

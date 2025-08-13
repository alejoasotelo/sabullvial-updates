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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Table\Table;

/**
 * PuntosDeVenta View
 *
 * @since  0.0.1
 */
class SabullvialViewPuntosDeVenta extends JViewLegacy
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
        $context = "sabullvial.list.admin.puntodeventa";

        // Get data from the model
        // $this->items		= $this->get('Items');
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
            SabullvialHelper::addSubmenu('puntodeventas');

            // Set the toolbar and number of found items
            $this->addToolBar();
        }

        // Set the document
        $this->setDocument();

        // Display the template
        parent::display($tpl);
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

        $title = '{{quotation.id ? JText("COM_SABULLVIAL_PUNTOS_DE_VENTA_TOOLBAR_EDITANDO_COTIZACION", quotation.id) : "'.Text::_('COM_SABULLVIAL_PUNTOS_DE_VENTA_TOOLBAR_CREANDO_COTIZACION').'"}}';

        ToolbarHelper::title($title, 'basket');
        ToolbarHelper::addNew('puntodeventa.add', 'COM_SABULLVIAL_PUNTOS_DE_VENTA_TOOLBAR_NUEVA_COTIZACION');
        ToolbarHelper::modal('previewCotizacion', 'icon-search', 'COM_SABULLVIAL_PUNTOS_DE_VENTA_TOOLBAR_VER_COTIZACIONES');

        $layout = new JLayoutFile('vue.toolbar.monto');
        $batchButtonHtml = $layout->render([
            'title' => JText::_('COM_SABULLVIAL_TOTAL_CON_IVA'),
            'class' => 'monto-total btn-text'
        ]);
        /** @var Joomla\CMS\Toolbar\Toolbar $bar */
        $bar = JToolbar::getInstance('toolbar');
        $bar->appendButton('Custom', $batchButtonHtml, 'montoTotal');

        ToolbarHelper::modal('myCotizaciones', 'icon-list', JText::_('COM_SABULLVIAL_PUNTOS_DE_VENTA_TOOLBAR_MIS_COTIZACIONES'));

        if ($canDo->get('core.admin')) {
            ToolbarHelper::divider();
            ToolbarHelper::preferences('com_sabullvial');
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
        $document->setTitle(Text::_('COM_SABULLVIAL_ADMINISTRATION'));

        $config = SabullvialHelper::getMultiConfig([
            'PORC_DIA',
            'COTI_DOL'
        ], [
            0,
            0
        ]);

        $filterCondicionVenta = (int)$this->state->get('filter.id_condicionventa', 49);
        $condicionVenta = JTable::getInstance('SitCondicionesVenta', 'SabullvialTable');
        $condicionVenta->loadByCondicionVenta($filterCondicionVenta);

        $cotizacionEstadoCreado = $this->state->params->get('cotizacion_estado_creado');

        if (!$cotizacionEstadoCreado) {
            JError::raiseError(500, 'Falta configurar el Estado pedido creado.');
        }

        $ordenDeTrabajoEstadoCreado = $this->state->params->get('orden_de_trabajo_estado_creado');
        if (!$ordenDeTrabajoEstadoCreado) {
            JError::raiseError(500, 'Falta configurar el Estado de Orden de trabajo creada.');
        }

        $idEstadoEsperarPagosEnEspera = $this->state->params->get('cotizacion_estado_esperar_pagos_en_espera');
        if (!$idEstadoEsperarPagosEnEspera) {
            JError::raiseError(500, 'Falta configurar el Estado de cotización en espera de pagos.');
        }

        $depositoConditions = $this->getDepositoConditions();

        $document->addScriptOptions('com_sabullvial', [
            'vendedor' => SabullvialHelper::getVendedor(),
            'pagination' => $this->pagination,
            'activeFilters' => $this->activeFilters,
            'token' => JSession::getFormToken(),
            'config' => [
                'porcentajeDia' => $config['PORC_DIA'],
                'cotizacionDolar' => $config['COTI_DOL'],
                'condicionVenta' => $condicionVenta,
                'cotizacion_estado_creado' => $cotizacionEstadoCreado,
                'orden_de_trabajo_estado_creado' => $ordenDeTrabajoEstadoCreado,
                'cotizacion_estado_esperar_pagos_en_espera' => (int) $idEstadoEsperarPagosEnEspera,
                'depositoConditions' => $depositoConditions
            ],
            'condicionesVenta' => $this->getCondicionesDeVenta(),
            'transportes' => JTable::getInstance('BullvialSitTransportes', 'SabullvialTable')->getAll(),
            'depositos' => array_map(function ($deposito) {
                return [
                    'id' => (int) $deposito->id,
                    'name' => $deposito->name,
                    'id_tango' => (int) $deposito->id_tango
                ];
            }, JTable::getInstance('Deposito', 'SabullvialTable')->getAll()),
            'uriRoot' => JUri::root(),
        ]);

        // $assetVersion = SabullvialHelper::getAssetVersion();

        JHtml::_('jquery.framework');

        $isVueProd = JComponentHelper::getParams('com_sabullvial')->get('vue_production', false);
        HTMLHelper::script('com_sabullvial/vue.' . ($isVueProd ? 'global.prod.js' : 'global.js'), ['version' => 'auto', 'relative' => true]);
        HTMLHelper::script('com_sabullvial/jquery.twbsPagination.min.js', ['version' => 'auto', 'relative' => true]);
        HTMLHelper::script('com_sabullvial/splide.min.js', ['version' => 'auto', 'relative' => true]);
        HTMLHelper::script('com_sabullvial/choices.min.js', ['version' => 'auto', 'relative' => true]);
        HTMLHelper::script('com_sabullvial/notify.min.js', ['version' => 'auto', 'relative' => true]);
        HTMLHelper::script('com_sabullvial/libs/select2.min.js', ['version' => 'auto', 'relative' => true]);
        HTMLHelper::script('com_sabullvial/libs/select2.es.js', ['version' => 'auto', 'relative' => true]);
        HTMLHelper::script('com_sabullvial/tools.js', ['version' => 'auto', 'relative' => true]);
        HTMLHelper::script('com_sabullvial/components/select2.js', ['version' => 'auto', 'relative' => true]);
        HTMLHelper::script('com_sabullvial/components/select2ajax.js', ['version' => 'auto', 'relative' => true]);
        HTMLHelper::script('com_sabullvial/components/chosen-select.js', ['version' => 'auto', 'relative' => true]);
        HTMLHelper::script('com_sabullvial/components/choices-select.js', ['version' => 'auto', 'relative' => true]);
        HTMLHelper::script('com_sabullvial/components/button-yesno.js', ['version' => 'auto', 'relative' => true]);
        HTMLHelper::script('com_sabullvial/components/popover.js', ['version' => 'auto', 'relative' => true]);
        HTMLHelper::script('com_sabullvial/components/carousel.js', ['version' => 'auto', 'relative' => true]);
        HTMLHelper::script('com_sabullvial/puntosdeventa.js', ['version' => 'auto', 'relative' => true]);

        HTMLHelper::stylesheet('com_sabullvial/choices.min.css', ['version' => 'auto', 'relative' => true]);
        HTMLHelper::stylesheet('com_sabullvial/libs/select2.min.css', ['version' => 'auto', 'relative' => true]);
        HTMLHelper::stylesheet('com_sabullvial/libs/select2.bootstrap.min.css', ['version' => 'auto', 'relative' => true]);
        HTMLHelper::stylesheet('com_sabullvial/splide.min.css', ['version' => 'auto', 'relative' => true]);
        Factory::getDocument()->addStyleDeclaration('.choices[data-type*="select-one"] .choices__inner{width: 96%;}');
        HTMLHelper::stylesheet('com_sabullvial/default.css', ['version' => 'auto', 'relative' => true]);
        HTMLHelper::stylesheet('com_sabullvial/puntosdeventa.css', ['version' => 'auto', 'relative' => true]);

        JHtml::_('formbehavior.chosen', 'select:not(.no-chosen)');
        JHtml::_('script', 'jui/ajax-chosen.min.js', ['version' => 'auto', 'relative' => true]);

        Text::script('COM_SABULLVIAL_CONSUMIDOR_FINAL');
        Text::script('COM_SABULLVIAL_PUNTOS_DE_VENTA_SAVE_SUCCESS');
        Text::script('COM_SABULLVIAL_PUNTOS_DE_VENTA_ENVIADA_A_FACTURACION_SUCCESS');
        Text::script('COM_SABULLVIAL_PUNTOS_DE_VENTA_ENVIADA_A_FACTURACION_ERROR');
        Text::script('COM_SABULLVIAL_PUNTOS_DE_VENTA_ERROR_CART_PRODUCTS_INVALID');
        Text::script('COM_SABULLVIAL_PUNTOS_DE_VENTA_ERROR_DIRECCION_Y_TRANSPORTE_REQUERIDOS');
        Text::script('COM_SABULLVIAL_PUNTOS_DE_VENTA_ERROR_CLIENTE_NO_TIENE_COND_VENTA_VENDEDOR');
        Text::script('COM_SABULLVIAL_PUNTOS_DE_VENTA_TOOLBAR_EDITANDO_COTIZACION');
        Text::script('COM_SABULLVIAL_PUNTOS_DE_VENTA_COTIZACION_MODAL_TITLE_EDITAR');
    }

    /**
     * Devuelve las condiciones de deposito configuradas en el backend según el transporte elegido
     *
     * @return array
     */
    public function getDepositoConditions()
    {
        $conditions = [];

        for ($i = 0; $i <= 1; $i++) {
            $ifIdTransporte = $this->state->params->get('cotizacion_deposito_if_transporte_' . $i, 0);

            if ($ifIdTransporte > 0) {
                $thenIdDeposito = (int) $this->state->params->get('cotizacion_deposito_if_transporte_' . $i . '_deposito', 0);

                $deposito = Table::getInstance('Deposito', 'SabullvialTable');
                $deposito->load($thenIdDeposito);

                $conditions[] = [
                    'id_transporte' => $ifIdTransporte,
                    'id_deposito' => $thenIdDeposito,
                    'id_deposito_tango' => $deposito->id_tango
                ];
            }
        }

        return $conditions;
    }

    public function getCondicionesDeVenta()
    {
        $vendedor = SabullvialHelper::getVendedor();

        $db    = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('COND_VTA id, DESC_COND nombre, dias, DESC_COND text, COND_VTA value')
            ->from($db->quoteName('SIT_CONDICIONES_VENTA', 'ap'))
            ->order('ID_SIT_CONDICIONES_VENTA, dias ASC');

        $condicionesVenta = $vendedor->get('condicionesDeVenta');
        if ($vendedor->get('tipo') != 'A' && $condicionesVenta && !SabullvialHelper::hasAllCondiciones($condicionesVenta)) {
            $query->where('COND_VTA IN (' . $condicionesVenta . ')');
        }

        $db->setQuery($query);
        $items = $db->loadObjectList();

        return $items;
        ;
    }
}

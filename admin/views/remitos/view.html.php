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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;

/**
 * Remitos View
 *
 * @since  0.0.1
 */
class SabullvialViewRemitos extends JViewLegacy
{
    /**
     * Display the Remitos view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        // Get application
        $app = JFactory::getApplication();
        $context = "sabullvial.list.admin.remito";

        $layout = $this->getLayout();
        if ($layout == 'optimize_images') {
            if (!SabullvialHelper::isUserSuperAdministrador() || JFactory::getUser()->username != 'alejo') {
                $app->redirect(JRoute::_('index.php?option=com_sabullvial&view=remitos', false), Text::_('COM_SABULLVIAL_SIN_PERMISOS_REMITOS_OPTIMIZE_IMAGES'), 'error');
                return;
            }
        } else {
            // Get data from the model
            // $this->items		= $this->get('Items');
            $this->pagination	= $this->get('Pagination');
            $this->state			= $this->get('State');
            $this->filterForm    	= $this->get('FilterForm');
            $this->activeFilters 	= $this->get('ActiveFilters');
        }

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode('<br />', $errors));

            return false;
        }

        // Set the submenu
        SabullvialHelper::addSubmenu('remitos');

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
        $title = Text::_('COM_SABULLVIAL_REMITOS_MANAGER');

        $layout = $this->getLayout();

        if ($layout == 'optimize_images') {
            JToolBarHelper::title($title, 'stack');
            JToolBarHelper::back();
            return;
        }

        $title .= " <span v-if='pagination.total' style='font-size: 0.85em; vertical-align: middle;'>({{pagination.total}})</span>";

        JToolBarHelper::title($title, 'stack');

        // we use a standard Joomla layout to get the html for the batch button
        /** @var Joomla\CMS\Toolbar\Toolbar $bar */
        $bar = JToolbar::getInstance('toolbar');
        $layout = new JLayoutFile('vue.toolbar.button');

        $batchButtonHtml = $layout->render([
            'title' => Text::_('JTOOLBAR_GENERAR_HOJA_DE_RUTA'),
            'icon' => 'icon-checkbox-partial',
            'onclick' => 'showModalGenerateRouteSheet()',
            'class' => 'btn-success'
        ]);

        $deliveredButtonHtml = $layout->render([
            'title' => Text::_('JTOOLBAR_MARCAR_COMO_ENTREGADO_POR_MOSTRADOR'),
            'icon' => 'icon-cube',
            'onclick' => 'showModalDeliveredByCounter()', // this is the function that will be called when the button is clicked
        ]);

        $addRemitoToRouteSheetButtonHtml = $layout->render([
            'title' => Text::_('JTOOLBAR_AGREGAR_REMITO_A_HOJA_DE_RUTA'),
            'icon' => 'icon-plus-circle',
            'onclick' => 'showModalAddRemitoToRouteSheet()',
            'attribs' => [
                'v-show' => 'cartRemitosEnProcesoSelected > 0'
            ]
        ]);

        $removeRemitoFromRouteSheetButtonHtml = $layout->render([
            'title' => Text::_('JTOOLBAR_ELIMINAR_REMITO_DE_HOJA_DE_RUTA'),
            'icon' => 'icon-unpublish',
            'onclick' => 'deleteRemitosFromRouteSheet()',
            'attribs' => [
                'v-show' => 'cartRemitosEnPreparacionSelected > 0'
            ]
        ]);

        $layout = new JLayoutFile('joomla.toolbar.monto');
        $montoTotalButtonHtml = $layout->render([
            'title' => Text::_('Monto total: '),
            'class' => 'monto-total',
            'value' => '{{cartTotalRemitosFormated}}'
        ]);

        $bar->appendButton('Custom', $batchButtonHtml, 'batch');
        $bar->appendButton('Custom', $deliveredButtonHtml, 'deliveredbycounter');
        $bar->appendButton('Custom', $addRemitoToRouteSheetButtonHtml, 'addRemitoToRouteSheet');
        $bar->appendButton('Custom', $removeRemitoFromRouteSheetButtonHtml, 'removeRemitoFromRouteSheet');
        $bar->appendButton('Custom', $montoTotalButtonHtml, 'montoTotal');

        if (SabullvialHelper::isUserSuperAdministrador() && JFactory::getUser()->username == 'alejo') {
            JToolBarHelper::link('index.php?option=com_sabullvial&view=remitos&layout=optimize_images', Text::_('COM_SABULLVIAL_REMITOS_OPTIMIZAR_IMAGENES'), 'image');
        }

        if (SabullvialHelper::isUserAdministrador()) {
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
        $document->setTitle(Text::_('COM_SABULLVIAL_ADMINISTRATION'));

        $layout = $this->getLayout();

        if ($layout == 'optimize_images') {
            return;
        }

        $listForm = $this->filterForm->getGroup('list');
        [$ordering, $direction] = explode(' ', $listForm['list_fullordering']->value);

        $document->addScriptOptions('com_sabullvial', [
            'vendedor' => SabullvialHelper::getVendedor(),
            'pagination' => $this->pagination,
            'activeFilters' => $this->activeFilters,
            'ordering' => $ordering,
            'direction' => $direction,
            'token' => JSession::getFormToken(),
            'uriRoot' => JUri::root(),
            'vehiculos' => Table::getInstance('Vehiculo', 'SabullvialTable')->getAll(),
            'choferes' => Table::getInstance('Chofer', 'SabullvialTable')->getAll(),
        ]);

        // $assetVersion = SabullvialHelper::getAssetVersion();

        HTMLHelper::_('jquery.framework');

        $isVueProd = JComponentHelper::getParams('com_sabullvial')->get('vue_production', false);
        HTMLHelper::script('com_sabullvial/vue.' . ($isVueProd ? 'global.prod.js' : 'global.js'), ['version' => 'auto', 'relative' => true]);
        HTMLHelper::script('com_sabullvial/jquery.twbsPagination.min.js', ['relative' => true]);
        HTMLHelper::script('com_sabullvial/notify.min.js', ['relative' => true]);
        HTMLHelper::script('com_sabullvial/tools.js', ['version' => 'auto', 'relative' => true]);
        HTMLHelper::script('com_sabullvial/libs/select2.min.js', ['version' => 'auto', 'relative' => true]);
        HTMLHelper::script('com_sabullvial/libs/select2.es.js', ['version' => 'auto', 'relative' => true]);
        HTMLHelper::script('com_sabullvial/components/label-estado.js', ['version' => 'auto', 'relative' => true]);
        HTMLHelper::script('com_sabullvial/components/select2ajax.js', ['version' => 'auto', 'relative' => true]);
        HTMLHelper::script('com_sabullvial/components/chosen-select.js', ['version' => 'auto', 'relative' => true]);
        HTMLHelper::script('com_sabullvial/remitos.js', ['version' => 'auto', 'relative' => true]);

        HTMLHelper::stylesheet('com_sabullvial/libs/select2.min.css', ['version' => 'auto', 'relative' => true]);
        HTMLHelper::stylesheet('com_sabullvial/libs/select2.bootstrap.min.css', ['version' => 'auto', 'relative' => true]);
        HTMLHelper::stylesheet('com_sabullvial/default.css', ['version' => 'auto', 'relative' => true]);
        HTMLHelper::stylesheet('com_sabullvial/remitos.css', ['version' => 'auto', 'relative' => true]);

        Text::script('COM_SABULLVIAL_REMITOS_DELIVERED_BY_COUNTER_ERROR_DELIVERY_DATE');
        Text::script('COM_SABULLVIAL_REMITOS_DELIVERED_BY_COUNTER_SUCCESS');

        Text::script('COM_SABULLVIAL_REMITOS_GENERATE_ROUTE_SHEET_ERROR_DELIVERY_DATE');
        Text::script('COM_SABULLVIAL_REMITOS_GENERATE_ROUTE_SHEET_ERROR_VEHICLE');
        Text::script('COM_SABULLVIAL_REMITOS_GENERATE_ROUTE_SHEET_ERROR_DRIVER');
        Text::script('COM_SABULLVIAL_REMITOS_GENERATE_ROUTE_SHEET_SUCCESS');
        Text::script('COM_SABULLVIAL_REMITOS_GENERATE_ROUTE_SHEET_ERROR');

        Text::script('COM_SABULLVIAL_REMITOS_MODAL_REMITO_DETAIL_REMITO_REMITO_REQUERIDO');
        Text::script('COM_SABULLVIAL_REMITOS_PLEASE_SELECT_REMITOS_EN_PROCESO');
        Text::script('COM_SABULLVIAL_REMITOS_PLEASE_SELECT_REMITOS_EN_PROCESO_ONLY');
        Text::script('COM_SABULLVIAL_REMITOS_HOJA_DE_RUTA_DESC');
        Text::script('COM_SABULLVIAL_REMITOS_PLEASE_SELECT_ROUTE_SHEET');
        Text::script('COM_SABULLVIAL_REMITOS_ADD_REMITOS_TO_ROUTE_SHEET_SUCCESS');

        Text::script('COM_SABULLVIAL_REMITOS_PLEASE_SELECT_REMITOS_EN_PREPARACION');
        Text::script('COM_SABULLVIAL_REMITOS_PLEASE_SELECT_REMITOS_EN_PREPARACION_ONLY');
        Text::script('COM_SABULLVIAL_REMITOS_CONFIRM_DELETE_REMITOS_FROM_ROUTE_SHEET');
        Text::script('COM_SABULLVIAL_REMITOS_DELETE_REMITOS_FROM_ROUTE_SHEET_SUCCESS');
        Text::script('COM_SABULLVIAL_REMITOS_DELETING_REMITOS_FROM_ROUTE_SHEET');
        Text::script('COM_SABULLVIAL_REMITOS_CONFIRM_DELETE_REMITO_IMAGE');
        Text::script('COM_SABULLVIAL_REMITOS_DELETE_REMITO_IMAGE_SUCCESS');
    }
}

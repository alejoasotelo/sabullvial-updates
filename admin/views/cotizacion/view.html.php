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
use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;

/**
 * Cotizacion View
 *
 * @since  0.0.1
 */
class SabullvialViewCotizacion extends JViewLegacy
{
    /**
     * View form
     *
     * @var         form
     */
    protected $form = null;

    protected $canDo = null;

    /**
     * Display the Cotizacion view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        // Get the Data
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->state = $this->get('State');
        $this->canDo = JHelperContent::getActions('com_sabullvial', 'cotizacion', $this->item->id);

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode('<br />', $errors));

            return false;
        }

        $layout = $this->getLayout();

        if ($layout == 'modal_approve') {
            $this->prepareItemForModalApprove();
        } elseif ($layout !== 'modal_review' || $layout !== 'modal_view_review') {
            // Set the toolbar
            $this->addToolBar();

            // Set the document
            $this->setDocument();
        }

        // Display the template
        parent::display($tpl);
    }

    protected function prepareItemForModalApprove()
    {
        Table::addIncludePath(JPATH_COMPONENT . '/tables/');
        $cliente = Table::getInstance('SitClientes', 'SabullvialTable');
        $cliente->loadByCodCliente($this->item->id_cliente);
        $this->cliente = $cliente;

        $condicionVenta = Table::getInstance('SitCondicionesVenta', 'SabullvialTable');
        $this->item->condicionventa = $condicionVenta->getLabelById($this->item->id_condicionventa);
        $this->item->condicionventaFake = $condicionVenta->getLabelById($this->item->id_condicionventa_fake);

        $itemsDetalle = count($this->item->revisiondetalle) ? $this->item->revisiondetalle : $this->item->cotizaciondetalle;

        $hasIVA = (int)$this->item->iva == 1;
        $this->item->hasIVA = $hasIVA;

        $this->item->subtotal = SabullvialCotizacionesHelper::calcSubtotal($itemsDetalle);
        $this->item->ivaTotal = SabullvialCotizacionesHelper::calcIva($hasIVA, $itemsDetalle);
        $this->item->iibb = SabullvialCotizacionesHelper::calcIIBB($this->item->porcentaje_iibb, $itemsDetalle, $hasIVA);

        $this->item->total = $this->item->subtotal + $this->item->ivaTotal + $this->item->iibb;
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
        $app = Factory::getApplication();
        $app->input->set('hidemainmenu', true);
        $user       = Factory::getUser();
        $userId     = $user->id;
        $isNew = ($this->item->id == 0);

        // Built the actions for new and existing records.
        $canDo = $this->canDo;
        $vendedor = SabullvialHelper::getVendedor();

        JToolbarHelper::title(JText::_('COM_SABULLVIAL_COTIZACION_MANAGER_' . ($isNew ? 'NEW' : 'EDIT')), 'pencil-2 article-add');

        if ($isNew) {
            if ($canDo->get('core.create')) {
                JToolbarHelper::apply('cotizacion.apply');
                JToolbarHelper::save('cotizacion.save');
            }

            JToolbarHelper::cancel('cotizacion.cancel');
        } else {
            // Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
            $itemEditable = $canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId) || $vendedor->get('ver.presupuestos', false);

            if ($itemEditable && !$this->item->was_aprobado) {
                JToolbarHelper::apply('cotizacion.apply');
                JToolbarHelper::save('cotizacion.save');
            }

            /** @var Joomla\CMS\Toolbar\Toolbar $bar */
            $bar = JToolbar::getInstance('toolbar');
            $layout = new JLayoutFile('joomla.toolbar.customlink');

            $isAdministrador = SabullvialHelper::isUserAdministrador();
            $cotizacionEstadoAprobadoAutomatico = $this->state->params->get('cotizacion_estado_aprobado_automatico');
            if ($isAdministrador && $this->item->id_estadocotizacion != $cotizacionEstadoAprobadoAutomatico && $this->item->is_reviewed) {
                $hash = md5($this->item->modified);
                $url =  JRoute::_('index.php?option=com_sabullvial&view=cotizacion&type=revisiondetalle&format=pdf&layout=print&id=' . $this->item->id . '&' . $hash . '=1');

                $batchButtonHtml = $layout->render([
                    'doTask' => $url,
                    'text' => JText::_('COM_SABULLVIAL_COTIZACIONES_TOOLBAR_IMPRIMIR_REVISION'),
                    'class' => 'icon-print',
                    'target' => '_blank'
                ]);
                $bar->appendButton('Custom', $batchButtonHtml, 'print-revision');
            }

            if (count($this->item->cotizaciondetalle)) {
                $hash = md5($this->item->modified);
                $url = JRoute::_('index.php?option=com_sabullvial&view=cotizacion&format=pdf&layout=print&id=' . $this->item->id . '&' . $hash . '=1');

                $batchButtonHtml = $layout->render([
                    'doTask' => $url,
                    'text' => JText::_('COM_SABULLVIAL_COTIZACIONES_TOOLBAR_IMPRIMIR_COTIZACION'),
                    'class' => 'icon-print',
                    'target' => '_blank'
                ]);
                $bar->appendButton('Custom', $batchButtonHtml, 'print-cotizacion');
            }

            if (SabullvialButtonsHelper::canEnviarAFacturacion($this->item->id_estadocotizacion)) {
                $data = [
                    'cotizacion' => $this->item,
                    'params' => $this->state->params,
                    'type' => 'item',
                    'show' => 'button'
                ];
                $buttonHtml = JLayoutHelper::render('joomla.content.cotizaciones.buttons.enviar_a_facturacion', $data);
                $bar->appendButton('Custom', $buttonHtml, 'enviar-a-facturacion');
            }

            if (JComponentHelper::isEnabled('com_contenthistory') && $this->state->params->get('save_history', 0) && $itemEditable) {
                JToolbarHelper::versions('com_sabullvial.cotizacion', $this->item->id);
            }

            $return = $app->input->get('return', '', 'base64');

            if (empty($return)) {
                JToolbarHelper::cancel('cotizacion.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
            } else {
                JToolbarHelper::cancel('cotizacion.cancel', 'JTOOLBAR_BACK');
            }
        }
    }

    /**
     * Method to set up the document properties
     *
     * @return void
     */
    protected function setDocument()
    {
        $isNew = ($this->item->id < 1);
        $document = Factory::getDocument();
        $document->setTitle($isNew ? JText::_('COM_SABULLVIAL_COTIZACION_CREATING') :
            JText::_('COM_SABULLVIAL_COTIZACION_EDITING'));


        $idDireccion = $this->form->getValue('id_direccion', null, 0);
        $porcIB = 0;
        if ($idDireccion > 0) {
            $table = JTable::getInstance('SitClientesDireccionEntrega', 'SabullvialTable');
            $table->load(['ID_DIRECCION_ENTREGA' => $idDireccion]);
            $porcIB = $table->PORC_IB;
        }
        $document = Factory::getDocument();
        $document->addScriptOptions('com_sabullvial.cotizacion', [
            'assignedIdCliente' => $this->form->getValue('id_cliente'),
            'porcentajeIIBB' => $porcIB
        ]);

        HTMLHelper::stylesheet('com_sabullvial/default.css', ['version' => 'auto', 'relative' => true]);
        HTMLHelper::stylesheet('com_sabullvial/libs/select2.min.css', ['version' => 'auto', 'relative' => true]);
        HTMLHelper::stylesheet('com_sabullvial/libs/select2.bootstrap.min.css', ['version' => 'auto', 'relative' => true]);
        HTMLHelper::stylesheet('com_sabullvial/libs/select2.joomla.min.css', ['version' => 'auto', 'relative' => true]);

        JHtml::_('jquery.framework');
        JHtml::_('behavior.keepalive');
        HTMLHelper::script('com_sabullvial/libs/select2.min.js', ['version' => 'auto', 'relative' => true]);
        HTMLHelper::script('com_sabullvial/libs/select2.es.js', ['version' => 'auto', 'relative' => true]);
        HTMLHelper::script('com_sabullvial/fields/select2.js', ['version' => 'auto', 'relative' => true]);
        HTMLHelper::script('com_sabullvial/cotizacion.js', ['version' => 'auto', 'relative' => true]);
    }
}

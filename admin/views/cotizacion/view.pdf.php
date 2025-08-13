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
use Joomla\CMS\Table\Table;
use Joomla\CMS\Version;
use Joomla\CMS\Document\PdfDocument;

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
        $this->type = Factory::getApplication()->input->getCmd('type', 'cotizaciondetalle');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode('<br />', $errors));

            return false;
        }

        $this->prepareItem();

        // Set the document
        $this->setDocument();

        // Display the template
        parent::display($tpl);
    }

    protected function prepareItem()
    {
        Table::addIncludePath(JPATH_COMPONENT . '/tables/');
        $cliente = Table::getInstance('SitClientes', 'SabullvialTable');
        $cliente->loadByCodCliente($this->item->id_cliente);
        $this->cliente = $cliente;

        $condicionVenta = Table::getInstance('SitCondicionesVenta', 'SabullvialTable');
        $condicionVenta->loadByCondicionVenta($this->item->id_condicionventa);
        $this->item->condicionVenta = $condicionVenta;

        $condicionVentaFake = Table::getInstance('SitCondicionesVenta', 'SabullvialTable');
        $condicionVentaFake->loadByCondicionVenta($this->item->id_condicionventa_fake);
        $this->item->condicionVentaFake = $condicionVentaFake;

        $itemsDetalle = $this->type == 'revisiondetalle' ? $this->item->revisiondetalle : $this->item->cotizaciondetalle;

        $hasIVA = (int)$this->item->iva == 1;

        $this->item->subtotal = SabullvialCotizacionesHelper::calcSubtotal($itemsDetalle);
        $this->item->ivaTotal = SabullvialCotizacionesHelper::calcIva($hasIVA, $itemsDetalle);
        $this->item->iibb = SabullvialCotizacionesHelper::calcIIBB($this->item->porcentaje_iibb, $itemsDetalle, $hasIVA);

        $this->item->total = $this->item->subtotal + $this->item->ivaTotal + $this->item->iibb;
    }

    /**
     * Method to set up the document properties
     *
     * @return void
     */
    protected function setDocument()
    {
        $input = Factory::getApplication()->input;
        $document = Factory::getDocument();
        $suffix = $this->type == 'revisiondetalle' ? 'REVISION' : 'COTIZACION';
        $document->setTitle(Text::_('COM_SABULLVIAL_COTIZACION_PDF_TITLE_' . $suffix));

        $created = JHtml::_('date', $this->item->created, JText::_('DATE_FORMAT_LC4'));
        $document->setName(Text::sprintf('COM_SABULLVIAL_COTIZACION_PDF_FILENAME_' . $suffix, $this->item->id, $created));

        $download = $input->get('download', false);

        $document->setOptions([
            'Attachment' => $download
        ]);
    }
}

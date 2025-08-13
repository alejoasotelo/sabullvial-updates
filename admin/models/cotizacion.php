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

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;

/**
 * Cotizacion Model
 *
 * @since  0.0.1
 */
class SabullvialModelCotizacion extends AdminModel
{
    public $typeAlias = 'com_sabullvial.cotizacion';

    /**
     * Method to get a table object, load it if necessary.
     *
     * @param   string  $type    The table name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  JTable  A JTable object
     *
     * @since   1.6
     */
    public function getTable($type = 'Cotizacion', $prefix = 'SabullvialTable', $config = [])
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to get the record form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  mixed    A JForm object on success, false on failure
     *
     * @since   1.6
     */
    public function getForm($data = [], $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm(
            'com_sabullvial.cotizacion',
            'cotizacion',
            [
                'control' => 'jform',
                'load_data' => $loadData
            ]
        );

        if (empty($form)) {
            return false;
        }

        $data = JFactory::getApplication()->getUserState(
            'com_sabullvial.edit.cotizacion.data',
            []
        );

        // Adjust the context to support modal layouts.
        $formData = $form->getData();
        $codigoCliente = $formData->get('id_cliente', ''); // COD_CLIENT
        if (!empty($codigoCliente)) {
            $form->setFieldAttribute('id_direccion', 'id_cliente', $codigoCliente);
        }

        $cliente = $formData->get('cliente', '');
        if (!empty($cliente)) {
            $form->setFieldAttribute('id_cliente', 'fieldclientevalue', $cliente);
        }

        $idCotizacion = $this->getState($this->getName() . '.id');
        if ($idCotizacion > 0) {
            $form->setFieldAttribute('cotizacionhistorico', 'id_cotizacion', (int)$idCotizacion);
            $form->setFieldAttribute('cotizaciontangohistorico', 'id_cotizacion', (int)$idCotizacion);
            $form->setFieldAttribute('cotizacionpagohistorico', 'id_cotizacion', (int)$idCotizacion);
        }

        $esperarPagos = (int) $formData->get('esperar_pagos', 0);
        if ($esperarPagos === 0) {
            $form->setFieldAttribute('id_estadocotizacionpago', 'type', 'hidden');
            $form->setFieldAttribute('cotizacionpagohistorico', 'type', 'hidden');
        }

        $vendedor = SabullvialHelper::getVendedor();

        if (!$vendedor->get('modificar.precios', false) && $loadData) {
            $elementPrecio = $form->getFieldXml('cotizaciondetalle')->xpath('//field[@name="precio"]')[0];
            $elementPrecio->addAttribute('readonly', true);
            $form->setField($elementPrecio);
        }

        $params = JComponentHelper::getParams('com_sabullvial');
        $maxLengthDescripcion = $params->get('cotizacion_productos_modificar_descripcion_maxlength', 30);
        $element = $form->getFieldXml('cotizaciondetalle')->xpath('//field[@name="nombre"]')[0];
        $element->addAttribute('maxlength', $maxLengthDescripcion);

        if (!$vendedor->get('modificar.descripcion', false) && $loadData) {
            $element->addAttribute('readonly', true);
            $form->setField($element);
        }

        if ($vendedor->get('tipo') == SabullvialHelper::USUARIO_TIPO_VENDEDOR) {
            $form->setFieldAttribute('id_estado_tango', 'readonly', true);

            $idEstadoCotizacion = $formData->get('id_estadocotizacion');
            $canCancelar = SabullvialButtonsHelper::canCancelar((int) $formData->get('id_estadocotizacion'));
            if (!$canCancelar) {
                $form->setFieldAttribute('id_estadocotizacion', 'readonly', true);
            } else {
                $idEstadosCotizacion = array_map(function ($estado) {
                    return $estado->id;
                }, Table::getInstance('EstadoCotizacion', 'SabullvialTable')->getEstadosCancelado());
                $idEstadosCotizacion[] = $idEstadoCotizacion;
                $form->setFieldAttribute('id_estadocotizacion', 'excludeallexcept', implode(',', $idEstadosCotizacion));
            }

            $condicionesVenta = $vendedor->get('condicionesDeVenta');
            if (!SabullvialHelper::hasAllCondiciones($condicionesVenta)) {
                $condicionesVenta = explode(',', $condicionesVenta);
                $condicionesVenta = ArrayHelper::toInteger($condicionesVenta);
                $idCondicionVenta = $formData->get('id_condicionventa');
                if (!in_array($idCondicionVenta, $condicionesVenta)) {
                    $form->setFieldAttribute('id_condicionventa', 'include', $idCondicionVenta);
                }

                $idCondicionVentaFake = $formData->get('id_condicionventa_fake');
                if (!in_array($idCondicionVentaFake, $condicionesVenta)) {
                    $form->setFieldAttribute('id_condicionventa_fake', 'include', $idCondicionVentaFake);
                }

            }
        }

        $isRevendedor = $vendedor->get('esRevendedor', false);
        if ($isRevendedor) {
            $form->setFieldAttribute('id_condicionventa_fake', 'type', 'hidden');
            $form->setFieldAttribute('id_condicionventa', 'type', 'hidden');
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed  The data for the form.
     *
     * @since   1.6
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState(
            'com_sabullvial.edit.cotizacion.data',
            []
        );

        if (empty($data)) {
            $data = $this->getItem();

            if (!empty($data->ordendecompra_file_name)) {
                $hash = $data->ordendecompra_file_hash;
                $data->ordendecompra_file = JRoute::_('index.php?option=com_sabullvial&task=cotizaciones.downloadOrdenDeCompra&id=' . $data->id . '&hash=' . $hash . '&' . JSession::getFormToken() . '=1', false, -1);
                $data->ordendecompra_file_delete = false;
            }
        }

        if ((is_array($data) && isset($data['id_direccion']) && $data['id_direccion'] > 0) || (is_object($data) && isset($data->id_direccion) && $data->id_direccion > 0)) {
            $idDireccion = is_array($data) ? $data['id_direccion'] : $data->id_direccion;
            /** @var SabullvialTableSitClientesDireccionEntrega $clienteDirecciones */
            $table = JTable::getInstance('SitClientesDireccionEntrega', 'SabullvialTable');
            $table->load(['ID_DIRECCION_ENTREGA' => $idDireccion]);
            if (is_array($data)) {
                $data['porcentaje_iibb'] = $table->PORC_IB;
            } else {
                $data->porcentaje_iibb = $table->PORC_IB;
            }
        }

        $app = JFactory::getApplication();
        $input = $app->input;
        $view = $input->getCmd('view', '');

        $subtask = $app->getUserState('com_sabullvial.edit.' . $view . '.subtask', '');
        $app->setUserState('com_sabullvial.edit.cotizacion.subtask', '');

        if ($view == 'cotizacion') {
            switch ($subtask) {
                case 'cotizacion.changeTransporte':
                    $config = SabullvialHelper::getComponentParams();

                    for ($i = 0; $i <= 1; $i++) {
                        $ifIdTransporte = $config->get('cotizacion_deposito_if_transporte_' . $i, 0);
                        $thenIdDeposito = (int) $config->get('cotizacion_deposito_if_transporte_' . $i . '_deposito', 0);

                        if (!($ifIdTransporte > 0) || !($thenIdDeposito > 0) || $ifIdTransporte != $data['id_transporte']) {
                            continue;
                        }

                        $deposito = Table::getInstance('Deposito', 'SabullvialTable');
                        $deposito->load($thenIdDeposito);

                        $data['id_deposito'] = $thenIdDeposito;
                        $data['id_deposito_tango'] = $deposito->id_tango;
                        break;
                    }

                    break;
                case 'cotizacion.changeCliente':

                    if (!isset($data['id_cliente']) || $data['id_cliente'] == '000000') {
                        $data['id_condicionventa'] = 49;

                        $condicionVenta = JTable::getInstance('SitCondicionesVenta', 'SabullvialTable');
                        $condicionVenta->loadByCondicionVenta($data['id_condicionventa']);

                        $data['iva'] = $condicionVenta->hasIVA() ? 1 : 0;
                        $data['id_lista_precio'] = (bool)$data['dolar'] ? ((bool)$data['iva'] ? 10 : 7) : ((bool)$data['iva'] ? 1 : 2);
                        break;
                    }

                    //$idCliente = $app->getUserState('com_sabullvial.edit.'.$view.'.data.id_cliente', null);
                    JTable::addIncludePath(JPATH_COMPONENT . '/tables/');
                    $cliente = JTable::getInstance('SitClientes', 'SabullvialTable');
                    $cliente->loadByCodCliente($data['id_cliente']);

                    $condicionVenta = JTable::getInstance('SitCondicionesVenta', 'SabullvialTable');
                    $condicionVenta->loadByCondicionVenta($cliente->COND_VTA);

                    $data['id_condicionventa'] = (int)$cliente->COND_VTA;

                    $data['iva'] = $condicionVenta->hasIVA() ? 1 : 0;
                    $data['id_lista_precio'] = (bool)$data['dolar'] ? ((bool)$data['iva'] ? 10 : 7) : ((bool)$data['iva'] ? 1 : 2);

                    if (is_array($data['cotizaciondetalle'])) {
                        $porcentajeDia = (float)SabullvialHelper::getConfig('PORC_DIA');
                        $cotizacionDolar = (float)SabullvialHelper::getConfig('COTI_DOL');
                        $kInteres = ((int)$condicionVenta->DIAS) * $porcentajeDia / 100;

                        foreach ($data['cotizaciondetalle'] as &$detalle) {
                            $producto = JTable::getInstance('SitArticulos', 'SabullvialTable');
                            $producto->loadByCodArticulo($detalle['id_producto']);

                            $producto->precio = $producto->getPrecio();

                            $producto->precio += $producto->precio * $kInteres;

                            if ((bool)$data['dolar']) {
                                $producto->precio /= $cotizacionDolar;
                            }

                            if ((bool)$data['iva']) {
                                $producto->precio *= 1.21;
                            }

                            $detalle['precio'] = $producto->precio;
                            $detalle['subtotal'] = $detalle['precio'] * (int)$detalle['cantidad'] * (1 - ((float)$detalle['descuento'] / 100));
                        }
                    }

                    break;

                case 'cotizacion.changeCondicionVenta':
                    if (empty($data['id_condicionventa'])) {
                        $data['id_condicionventa'] = 49;

                        $condicionVenta = JTable::getInstance('SitCondicionesVenta', 'SabullvialTable');
                        $condicionVenta->loadByCondicionVenta($data['id_condicionventa']);

                        $data['iva'] = $condicionVenta->hasIVA() ? 1 : 0;
                        $data['id_lista_precio'] = (bool)$data['dolar'] ? ((bool)$data['iva'] ? 10 : 7) : ((bool)$data['iva'] ? 1 : 2);

                        break;
                    }
                    $condicionVenta = JTable::getInstance('SitCondicionesVenta', 'SabullvialTable');
                    $condicionVenta->loadByCondicionVenta($data['id_condicionventa']);

                    $data['iva'] = $condicionVenta->hasIVA() ? 1 : 0;
                    $data['id_lista_precio'] = (bool)$data['dolar'] ? ((bool)$data['iva'] ? 10 : 7) : ((bool)$data['iva'] ? 1 : 2);

                    if (is_array($data['cotizaciondetalle'])) {
                        $porcentajeDia = (float)SabullvialHelper::getConfig('PORC_DIA');
                        $cotizacionDolar = (float)SabullvialHelper::getConfig('COTI_DOL');
                        $kInteres = ((int)$condicionVenta->DIAS) * $porcentajeDia / 100;

                        foreach ($data['cotizaciondetalle'] as &$detalle) {
                            $producto = JTable::getInstance('SitArticulos', 'SabullvialTable');
                            $producto->loadByCodArticulo($detalle['id_producto']);

                            $producto->precio = $producto->getPrecio();

                            $producto->precio += $producto->precio * $kInteres;

                            if ((bool)$data['dolar']) {
                                $producto->precio /= $cotizacionDolar;
                            }

                            if ((bool)$data['iva']) {
                                $producto->precio *= 1.21;
                            }

                            $detalle['precio'] = $producto->precio;
                            $detalle['subtotal'] = $detalle['precio'] * (int)$detalle['cantidad'] * (1 - ((float)$detalle['descuento'] / 100));
                        }
                    }
                    break;

                case 'cotizacion.changeIva':
                case 'cotizacion.changeDolar':

                    if (is_array($data['cotizaciondetalle'])) {
                        $condicionVenta = JTable::getInstance('SitCondicionesVenta', 'SabullvialTable');
                        $condicionVenta->loadByCondicionVenta($data['id_condicionventa']);

                        $porcentajeDia = (float)SabullvialHelper::getConfig('PORC_DIA');
                        $cotizacionDolar = (float)SabullvialHelper::getConfig('COTI_DOL');
                        $kInteres = ((int)$condicionVenta->DIAS) * $porcentajeDia / 100;

                        foreach ($data['cotizaciondetalle'] as &$detalle) {
                            $producto = JTable::getInstance('SitArticulos', 'SabullvialTable');
                            $producto->loadByCodArticulo($detalle['id_producto']);
                            $producto->precio = $producto->getPrecio();

                            $producto->precio +=  $producto->precio * $kInteres;

                            if ((bool)$data['dolar']) {
                                $producto->precio /= $cotizacionDolar;
                            }

                            if ((bool)$data['iva']) {
                                $producto->precio *= (1 + (SabullvialHelper::IVA_21 / 100));
                            }

                            $detalle['precio'] = $producto->precio;
                            $detalle['subtotal'] = $detalle['precio'] * (int)$detalle['cantidad'] * (1 - ((float)$detalle['descuento'] / 100));
                        }
                    }

                    $data['id_lista_precio'] = (bool)$data['dolar'] ? ((bool)$data['iva'] ? 10 : 7) : ((bool)$data['iva'] ? 1 : 2);
                    break;

                default:
                    break;
            }
        }

        return $data;
    }

    public function getItem($pk = null)
    {
        $item = parent::getItem($pk);

        if ($item->id > 0) {
            /** @var SabullvialTableCotizacionDetalle $table */
            $table = JTable::getInstance('CotizacionDetalle', 'SabullvialTable');
            $item->cotizaciondetalle = $table->loadByIdCotizacion($item->id);

            /** @var SabullvialTableRevisionDetalle $tableRevision */
            $tableRevision = JTable::getInstance('RevisionDetalle', 'SabullvialTable');
            $item->revisiondetalle = $tableRevision->loadByIdCotizacion($item->id);

            /** @var SabullvialTableCotizacion $tableCotizacion */
            $tableCotizacion = JTable::getInstance('Cotizacion', 'SabullvialTable');
            $item->was_aprobado = $tableCotizacion->isAprobado($item->id);
            $item->is_reviewed = $tableCotizacion->isRevisado($item->id);
            $item->has_rechazado = $tableCotizacion->hasRechazado($item->id);
            $item->has_custom_products = $tableCotizacion->hasCustomProducts($item->id);
            $item->is_orden_de_trabajo = $tableCotizacion->isOrdenDeTrabajo($item->id);
        }

        return $item;
    }

    public function save($data)
    {
        if (empty($data['cliente']) && empty($data['id_cliente'])) {
            $data['cliente'] = JText::_('COM_SABULLVIAL_CONSUMIDOR_FINAL');
            $data['id_cliente'] = '000000';
        }

        if (empty($data['delivery_term'])) {
            $data['delivery_term'] = JText::_('COM_SABULLVIAL_COTIZACION_PLAZO_DE_ENTREGA_INMEDIATO');
        }

        // Calculo el subtotal, iva, iibb y total.
        $data['subtotal'] = $this->calcSubtotal($data);
        $data['iibb'] = $this->calcIIBB($data);
        $iva = $this->calcIva($data);
        $data['total'] = $data['subtotal'] + $iva + $data['iibb'];

        $deposito = JTable::getInstance('Deposito', 'SabullvialTable');
        if (!empty($data['id_deposito']) && $deposito->load($data['id_deposito'])) {
            $data['id_deposito_tango'] = $deposito->id_tango;
        }

        $input = JFactory::getApplication()->input;
        $formData  = $input->get('jform', [], 'array');
        $formFiles = $input->files->get('jform', null, 'RAW');

        $deleteOrdenDeCompra = false;
        $fileToDelete = '';

        $isSaved = parent::save($data);

        if ($isSaved) {
            $idCotizacion = $this->getState($this->getName() . '.id');
            $table = $this->getTable();
            $table->load($idCotizacion);

            // Si se envia el archivo de cómputo lo subo y luego lo guardo
            if (isset($formFiles['ordendecompra_file']) && $formFiles['ordendecompra_file']['error'] == 0) {
                $fileName = JFile::makeSafe($formFiles['ordendecompra_file']['name']);
                $fileHash = md5($idCotizacion . '-computation');

                $src = $formFiles['ordendecompra_file']['tmp_name'];
                $path = JPATH_SITE . '/media/com_sabullvial/ordendes_de_compra/' . $idCotizacion;
                $dest = $path . '/' . $fileHash;

                JFolder::create($path);

                if (JFile::upload($src, $dest)) {
                    $table->ordendecompra_file_name = $fileName;
                    $table->ordendecompra_file_hash = $fileHash;
                    $table->ordendecompra_file_ext = JFile::getExt($fileName);
                    $table->store();
                } else {
                    return false;
                }
            } elseif (isset($formData['ordendecompra_file_delete']) && $formData['ordendecompra_file_delete'] == 'on') {
                $deleteOrdenDeCompra = true;
                $path = JPATH_SITE . '/media/com_sabullvial/ordendes_de_compra/' . $idCotizacion;
                $fileToDelete = $path . '/' . $data['ordendecompra_file_hash'];

                $table->ordendecompra_file_hash = '';
                $table->ordendecompra_file_name = '';
                $table->ordendecompra_file_ext = '';
                $table->store();
            }


            $cotizacionDetalles = $table->getCotizacionDetalles();

            // Obtengo los IDs de los productos nuevos o que ya había
            $newCotizacionDetallesIds = $this->extractIdsFromArray($data['cotizaciondetalle']);
            $currentCotizacionDetallesIds = $this->extractIdsFromArray($cotizacionDetalles);
            $cotizacionDetallesToDelete = SabullvialHelper::array_diff($newCotizacionDetallesIds, $currentCotizacionDetallesIds);

            $cotizacionDetalleTable = JTable::getInstance('CotizacionDetalle', 'SabullvialTable');
            foreach ($cotizacionDetallesToDelete as $id) {
                $cotizacionDetalleTable->delete($id);
            }

            $totalProducts = 0;

            foreach ($data['cotizaciondetalle'] as $cotizacionDetalle) {
                $cotizacionDetalle['id_cotizacion'] = $idCotizacion;
                $descuento = (float)$cotizacionDetalle['descuento'] / 100;
                $cotizacionDetalle['precio_total'] = (float)$cotizacionDetalle['precio'] * (int)$cotizacionDetalle['cantidad'] * (1 - $descuento);
                $cotizacionDetalleTable->save($cotizacionDetalle);

                $totalProducts += $cotizacionDetalle['precio_total'];
            }

            if ($deleteOrdenDeCompra) {
                if (file_exists($fileToDelete)) {
                    @unlink($fileToDelete);
                }
            }
        }

        return $isSaved;
    }

    public function review($data, $recordId, $context)
    {
        $pedidoCompleto = true;

        foreach ($data as $cotizacionDetalle) {
            if ($pedidoCompleto && $cotizacionDetalle->cantidad != $cotizacionDetalle->cantidad_disponible) {
                $pedidoCompleto = false;
            }

            //$hasCantidad = ((int)$cotizacionDetalle->cantidad_disponible) > 0;
            //if (!$hasCantidad) {
            //	continue;
            //}

            $table = JTable::getInstance('RevisionDetalle', 'SabullvialTable');
            $table->id_cotizacion_detalle = $cotizacionDetalle->id;
            $table->cantidad = $cotizacionDetalle->cantidad_disponible;
            $table->store();
        }

        $params = JComponentHelper::getParams('com_sabullvial');
        $newEstadoCotizacion = $params->get('cotizacion_estado_' . ($pedidoCompleto ? 'completo' : 'incompleto'));

        $this->changeEstadoCotizacion($recordId, $newEstadoCotizacion);
        $this->updateTotalesRevisionCotizacion($recordId);

        return true;
    }

    public function updateTotalesRevisionCotizacion(&$pks)
    {
        /** @var SabullvialTableCotizacion $table */
        $table = $this->getTable();
        $pks = (array) $pks;

        // Access checks.
        foreach ($pks as $i => $pk) {
            $table->reset();

            if ($table->load($pk)) {
                $hasIVA = (int)$table->iva == 1;

                $subtotalRevision = $this->getSubtotalRevision($pk);
                $iva = $hasIVA ? 0 : ($subtotalRevision * 0.21);
                $iibbRevision = $hasIVA ? $subtotalRevision / 1.21 : $subtotalRevision;
                $iibbRevision *= ((float)$table->porcentaje_iibb / 100); // 0.1%

                $table->subtotal_revision = $subtotalRevision;
                $table->iibb_revision = $iibbRevision;
                $table->total_revision = $subtotalRevision + $iva + $table->iibb_revision;
                $table->store();
            }
        }

        return true;
    }

    /**
     * Calcula el subtotal de los productos revisionados de la cotización.
     *
     * @param int $idCotizacion
     * @return float
     */
    protected function getSubtotalRevision($idCotizacion)
    {
        $db = $this->getDbo();

        $query = $db->getQuery(true);

        $query->select('sum(rd.cantidad * cd.precio) subtotal')
            ->from($db->qn('#__sabullvial_cotizaciondetalle', 'cd'))
            ->innerJoin($db->qn('#__sabullvial_revisiondetalle', 'rd') . ' ON (rd.id_cotizacion_detalle = cd.id)')
            ->where('cd.id_cotizacion = ' . $idCotizacion);

        $db->setQuery($query);
        return (float)$db->loadResult();
    }

    public function createWithFaltantes($recordId, $context)
    {
        $faltantes = $this->getFaltantes($recordId);

        if (count($faltantes) == 0) {
            $this->setError(JText::_('COM_SABULLVIAL_COTIZACION_NO_HAY_FALTANTES'));
            return false;
        }

        $totalProducts = 0;
        foreach ($faltantes as $i => $cotizacionDetalle) {
            $descuento = (float)$cotizacionDetalle['descuento'] / 100;
            $faltantes[$i]['subtotal'] = (float)$cotizacionDetalle['precio'] * (int)$cotizacionDetalle['cantidad_faltante'] * (1 - $descuento);
            $totalProducts += $cotizacionDetalle['subtotal'];
        }

        /** @var SabullvialTableCotizacion $table */
        $table = clone $this->getTable();
        $table->load($recordId);

        $tableEstado = clone $this->getTable('EstadoCotizacion');
        $idEstadoPendiente = $tableEstado->getEstadoPendienteId();

        $table->id = null;
        $table->id_estadocotizacion = $idEstadoPendiente;
        $table->parent_id = $recordId;
        $table->reference .= '-' . SabullvialTableCotizacion::generateReference();
        $table->total = $totalProducts;
        $table->created = null;
        $table->created_by = null;
        $table->created_by_alias = '';
        $table->modified = null;
        $table->modified_by = null;

        if (!$table->store()) {
            $this->setError(JText::_('COM_SABULLVIAL_COTIZACION_ERROR_CREAR_COTIZACION_CON_FALTANTES'));
            return false;
        }

        $idCotizacion = (int)$table->id;
        $totalProducts = 0;

        foreach ($faltantes as $cotizacionDetalle) {
            /** @var SabullvialTableCotizacionDetalle $cotizacionDetalle */
            $tableCotizacionDetalle = clone $this->getTable('CotizacionDetalle');
            $tableCotizacionDetalle->load($cotizacionDetalle['id']);

            $tableCotizacionDetalle->id = null;
            $tableCotizacionDetalle->id_cotizacion = $idCotizacion;
            $tableCotizacionDetalle->cantidad = (int)$cotizacionDetalle['cantidad_faltante'];
            $tableCotizacionDetalle->subtotal = (float)$cotizacionDetalle['subtotal'];
            $tableCotizacionDetalle->created = null;
            $tableCotizacionDetalle->created_by = null;
            $tableCotizacionDetalle->created_by_alias = '';
            $tableCotizacionDetalle->modified = null;
            $tableCotizacionDetalle->modified_by = null;

            $tableCotizacionDetalle->store();
        }

        return true;
    }

    /**
     *  Obtengo los IDs de los order details desde el array $data.
     *
     * @param array $orderDetails
     * @return array<int>
     */
    private function extractIdsFromArray($data)
    {
        $ids = [];
        foreach ($data as $item) {
            if (isset($item['id']) && (int)$item['id'] > 0) {
                $ids[] = (int)$item['id'];
            }
        }

        return $ids;
    }

    /**
     * Method to check if it's OK to delete a message. Overrides JModelAdmin::canDelete
     */
    protected function canDelete($record)
    {
        if (!empty($record->id)) {
            return JFactory::getUser()->authorise("core.delete", "com_sabullvial.cotizacion." . (int)$record->id);
        }
    }

    /**
     * Method to test whether a record can have its state edited.
     *
     * @param   object  $record  A record object.
     *
     * @return  boolean  True if allowed to change the state of the record. Defaults to the permission set in the component.
     *
     * @since   1.6
     */
    protected function canEditState($record)
    {
        // Check for existing article.
        if (!empty($record->id)) {
            return JFactory::getUser()->authorise('core.edit.state', 'com_sabullvial.cotizacion.' . (int) $record->id);
        }

        // Default to component settings if neither article nor category known.
        return parent::canEditState($record);
    }

    public function getCotizacionHistorico($idCotizacion)
    {
        $db    = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('ch.id, ec.nombre estado, ec.color bg_color, ec.color_texto color, ch.created, ch.created_by_alias');
        $query->from('#__sabullvial_cotizacionhistorico ch')
            ->leftJoin('#__sabullvial_estadocotizacion ec ON (ec.id = ch.id_estadocotizacion)')
            ->where('ch.id_cotizacion = ' . $idCotizacion)
            ->order('ch.created DESC');
        $db->setQuery($query);
        return $db->loadObjectList();
    }

    public function getCotizacionTangoHistorico($idCotizacion)
    {
        $db    = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('ch.*');
        $query->from('#__sabullvial_cotizaciontangohistorico ch')
            ->where('ch.id_cotizacion = ' . $idCotizacion)
            ->order('ch.created DESC');
        $db->setQuery($query);
        $items = $db->loadObjectList();

        JFormHelper::loadFieldClass('EstadoCotizacionTango');

        foreach ($items as &$item) {
            $estado = JFormFieldEstadoCotizacionTango::findById($item->id_estado_tango);
            $item->estado = $estado['text'];
            $item->bg_color = $estado['background_color'];
            $item->color = $estado['color'];
        }

        return $items;
    }

    /**
     * Method to change the published state of one or more records.
     *
     * @param   array    &$pks   A list of the primary keys to change.
     * @param   integer  $value  The value of the published state.
     *
     * @return  boolean  True on success.
     *
     * @since   1.6
     */
    public function changeEstadoCotizacion(&$pks, $value)
    {
        /** @var SabullvialTableCotizacion $table */
        $table = $this->getTable();
        $pks = (array) $pks;

        if (!$value) {
            JLog::add(JText::_('Falta el valor'), \JLog::WARNING, 'jerror');
        }

        // Access checks.
        foreach ($pks as $i => $pk) {
            $table->reset();

            if ($table->load($pk)) {
                if (!$this->canEditState($table)) {
                    // Prune items that you can't change.
                    unset($pks[$i]);

                    JLog::add(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), \JLog::WARNING, 'jerror');

                    return false;
                }
            }
        }

        // Check if there are items to change
        if (!count($pks)) {
            return true;
        }

        // Attempt to change the state of the records.
        if (!$table->changeEstadoCotizacion($pks, $value)) {
            $this->setError($table->getError());

            return false;
        }

        // Clear the component's cache
        $this->cleanCache();

        return true;
    }

    /**
     * Method to change the published state of one or more records.
     *
     * @param   array    &$pks   A list of the primary keys to change.
     * @param   integer  $value  The value of the published state.
     *
     * @return  boolean  True on success.
     *
     * @since   1.6
     */
    public function changeEstadoTangoCotizacion(&$pks, $value)
    {
        /** @var SabullvialTableCotizacion $table */
        $table = $this->getTable();
        $pks = (array) $pks;

        if (!$value) {
            JLog::add(JText::_('Falta el valor'), \JLog::WARNING, 'jerror');
        }

        // Access checks.
        foreach ($pks as $i => $pk) {
            $table->reset();

            if ($table->load($pk)) {
                if (!$this->canEditState($table)) {
                    // Prune items that you can't change.
                    unset($pks[$i]);

                    JLog::add(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), \JLog::WARNING, 'jerror');

                    return false;
                }

                // si no es consumidor final y no tiene dirección y transporte
                if ($table->id_cliente != '000000' && (empty($table->id_transporte) || empty($table->id_direccion))) {
                    unset($pks[$i]);
                    $this->setError(JText::_('COM_SABULLVIAL_PUNTOS_DE_VENTA_ERROR_DIRECCION_Y_TRANSPORTE_REQUERIDOS'));
                    JLog::add(JText::_('COM_SABULLVIAL_PUNTOS_DE_VENTA_ERROR_DIRECCION_Y_TRANSPORTE_REQUERIDOS'), \JLog::WARNING, 'jerror');

                    return false;
                }
            }
        }

        // Check if there are items to change
        if (!count($pks)) {
            return true;
        }

        // Attempt to change the state of the records.
        if (!$table->changeEstadoTangoCotizacion($pks, $value)) {
            $this->setError($table->getError());

            return false;
        }

        // Clear the component's cache
        $this->cleanCache();

        return true;
    }

    public function changeEstadoCotizacionPago(&$pks, $value)
    {
        /** @var SabullvialTableCotizacion $table */
        $table = $this->getTable();
        $pks = (array) $pks;

        if (!$value) {
            JLog::add(JText::_('Falta el valor'), \JLog::WARNING, 'jerror');
        }

        // Access checks.
        foreach ($pks as $i => $pk) {
            $table->reset();

            if ($table->load($pk)) {
                if (!$this->canEditState($table)) {
                    // Prune items that you can't change.
                    unset($pks[$i]);

                    JLog::add(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), \JLog::WARNING, 'jerror');

                    return false;
                }
            }
        }

        // Check if there are items to change
        if (!count($pks)) {
            return true;
        }

        // Attempt to change the state of the records.
        if (!$table->changeEstadoCotizacionPago($pks, $value)) {
            $this->setError($table->getError());

            return false;
        }

        // Clear the component's cache
        $this->cleanCache();

        return true;
    }

    /**
     * Method to delete one or more records.
     *
     * @param   array  &$pks  An array of record primary keys.
     *
     * @return  boolean  True if successful, false if an error occurs.
     *
     * @since   1.6
     */
    public function delete(&$pks)
    {
        $pks = (array)$pks;

        foreach ($pks as $idCotizacion) {
            $db = $this->getDbo();

            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__sabullvial_revisiondetalle'))
                ->where($db->quoteName('id_cotizacion_detalle') . ' IN (SELECT id FROM ' . $db->qn('#__sabullvial_cotizaciondetalle') . ' WHERE id_cotizacion = ' . $idCotizacion . ')');

            $db->setQuery($query);
            $db->execute();

            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__sabullvial_cotizaciondetalle'))
                ->where($db->quoteName('id_cotizacion') . ' = ' . (int)$idCotizacion);

            $db->setQuery($query);
            $db->execute();
        }

        return parent::delete($pks);
    }

    /**
     * Devuelve los items (cotizacionDetalle) que no tienen stock.
     * Sería la diferencia entre cotizaciondetalle y revisiondetalle
     *
     * @param object $item sería $this->getItem()
     * @return array<object>
     */
    public function getFaltantes($idCotizacion = null)
    {
        $idCotizacion = is_null($idCotizacion) ? $this->getState($this->getName() . '.id') : $idCotizacion;

        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('cd.*, rd.cantidad cantidad_disponible, IFNULL(cd.cantidad - rd.cantidad, 0) cantidad_faltante')
            ->from($db->qn('#__sabullvial_cotizaciondetalle', 'cd'))
            ->innerJoin($db->qn('#__sabullvial_revisiondetalle', 'rd') . ' ON (rd.id_cotizacion_detalle = cd.id AND rd.cantidad < cd.cantidad)')
            ->where('cd.id_cotizacion = ' . $idCotizacion);

        $db->setQuery($query);

        return $db->loadAssocList();
    }

    protected function calcSubtotal($data)
    {
        return SabullvialCotizacionesHelper::calcSubtotal($data['cotizaciondetalle']);
    }

    protected function calcIva($data)
    {
        $hasIVA = (int)$data['iva'] == 1;

        return SabullvialCotizacionesHelper::calcIva($hasIVA, $data['cotizaciondetalle']);
    }

    protected function calcIIBB($data)
    {
        $porcentajeIIBB = (float)$data['porcentaje_iibb'];
        $itemsDetalle = $data['cotizaciondetalle'];
        $hasIVA = (int)$data['iva'] == 1;

        return SabullvialCotizacionesHelper::calcIIBB($porcentajeIIBB, $itemsDetalle, $hasIVA);
    }



    /**
     * Duplica un registro de la base de datos.
     *
     * @param   int    $pk       El ID del registro a duplicar.
     *
     * @return  int|boolean  El ID del nuevo registro o false si falla.
     */
    public function duplicate($pk)
    {
        // Get current user
        $this->user = Factory::getUser();

        // Get table
        $this->table = $this->getTable();

        $db     = $this->getDbo();

        $this->table->reset();

        // Check that the row actually exists
        if (!$this->table->load($pk)) {
            if ($error = $this->table->getError()) {
                $this->setError($error);
            } else {
                $this->setError(\JText::sprintf('JLIB_APPLICATION_ERROR_RECORD_NOT_FOUND', $pk));
            }

            return false;
        }

        // Check for asset_id
        if ($this->table->hasField($this->table->getColumnAlias('asset_id'))) {
            $oldAssetId = $this->table->asset_id;
        }

        $params = JComponentHelper::getParams('com_sabullvial');

        /** @var SabullvialTableCotizacion $tableCotizacion */
        $tableCotizacion = Table::getInstance('Cotizacion', 'SabullvialTable');
        $isOrdenDeTrabajo = $tableCotizacion->isOrdenDeTrabajo($pk);

        // Reset the ID because we are making a copy
        $this->table->id = 0;
        $this->table->reference = null;
        $this->table->id_estado_tango = 0;
        $this->table->id_estadocotizacion = $isOrdenDeTrabajo ? $params->get('orden_de_trabajo_estado_duplicado') : $params->get('cotizacion_estado_duplicado');
        $this->table->tango_enviar = 0;
        $this->table->tango_fecha_sincronizacion = null;
        $this->table->created = null;
        $this->table->created_by = null;
        $this->table->created_by_alias = null;
        $this->table->modified = null;
        $this->table->modified_by = null;

        // Check the row.
        if (!$this->table->check()) {
            $this->setError($this->table->getError());

            return false;
        }

        // Store the row.
        if (!$this->table->store()) {
            $this->setError($this->table->getError());

            return false;
        }

        // Get the new item ID
        $newId = $this->table->get('id');

        if (!empty($oldAssetId)) {
            // Copy rules
            $query = $db->getQuery(true);
            $query->clear()
                ->update($db->quoteName('#__assets', 't'))
                ->join(
                    'INNER',
                    $db->quoteName('#__assets', 's') .
                    ' ON ' . $db->quoteName('s.id') . ' = ' . $oldAssetId
                )
                ->set($db->quoteName('t.rules') . ' = ' . $db->quoteName('s.rules'))
                ->where($db->quoteName('t.id') . ' = ' . $this->table->asset_id);

            $db->setQuery($query)->execute();
        }

        if (!empty($this->table->ordendecompra_file_hash)) {
            $src = JPATH_SITE . '/media/com_sabullvial/ordendes_de_compra/' . $pk . '/' . $this->table->ordendecompra_file_hash;

            $fileHash = md5($newId . '-computation');
            $path = JPATH_SITE . '/media/com_sabullvial/ordendes_de_compra/' . $newId;
            $dest = $path . '/' . $fileHash;

            JFolder::create($path);

            if (!JFile::copy($src, $dest)) {
                $this->setError(JText::_('COM_SABULLVIAL_COTIZACION_ERROR_DUPLICAR_ORDEN_DE_COMPRA'));
                $this->delete($newId);
                return false;
            }

            $this->table->ordendecompra_file_hash = $fileHash;
        }

        if ($isOrdenDeTrabajo) {
            $this->table->id_estadocotizacion = $params->get('orden_de_trabajo_estado_creado');
        } else {
            $this->table->id_estadocotizacion = $params->get('cotizacion_estado_creado');
        }

        if (!$this->table->store()) {
            $this->setError($this->table->getError());
            $this->delete($newId);
            return false;
        }

        if (!$this->duplicateCotizacionDetalle($pk, $newId)) {
            $this->delete($newId);
            return false;
        }

        // Clean the cache
        $this->cleanCache();

        return $newId;
    }

    protected function duplicateCotizacionDetalle($oldIdCotizacion, $newIdCotizacion)
    {
        $table = $this->getTable();
        $table->load($oldIdCotizacion);
        $cotizacionDetalles = $table->getCotizacionDetalles();

        foreach ($cotizacionDetalles as $cotizacionDetalle) {
            $cotizacionDetalle['id'] = 0;
            $cotizacionDetalle['id_cotizacion'] = $newIdCotizacion;

            $tableCotizacionDetalle = JTable::getInstance('CotizacionDetalle', 'SabullvialTable');
            if (!$tableCotizacionDetalle->save($cotizacionDetalle)) {
                $this->setError($tableCotizacionDetalle->getError());
                return false;
            }
        }

        return true;
    }
}

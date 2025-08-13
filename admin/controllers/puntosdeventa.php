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
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;

/**
 * PuntosDeVenta Controller
 *
 * @package     Sabullvial.Administrator
 * @subpackage  com__sabullvial
 */
class SabullvialControllerPuntosDeVenta extends AdminController
{
    /**
     * Proxy for getModel.
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  object  The model.
     *
     * @since   1.6
     */
    public function getModel($name = 'PuntoDeVenta', $prefix = 'SabullvialModel', $config = ['ignore_request' => true])
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    public function changeCliente()
    {
        $app = Factory::getApplication();
        $app->setHeader('Content-Type', 'application/json');

        if (!$this->checkToken('get', false)) {
            echo new JResponseJson(null, 'invalid token', true);
            die();
        }

        $data = $this->prepareDataOnChangecliente(
            $this->input->getInt('id_condicionventa', 49),
            $this->input->getInt('iva', 1),
            $this->input->getInt('dolar', 0),
            $this->input->getCwd('id_cliente', '')
        );

        $app->setBody(new JResponseJson($data));
        echo $app->toString(true);
        die();
    }

    public function changeCondicionVenta()
    {
        $app = Factory::getApplication();
        $app->setHeader('Content-Type', 'application/json');

        if (!$this->checkToken('get', false)) {
            $app->setBody(new JResponseJson(null, 'invalid token', true));
            echo $app->toString(true);
            die();
        }

        $data = [
            'id_cliente' => $this->input->getCwd('id_cliente', ''), // COD_CLIENT
            'id_condicionventa' => $this->input->getInt('id_condicionventa', 0),
            'iva' =>  $this->input->getInt('iva', 1),
            'dolar' => $this->input->getInt('dolar', 0),
            'config' => [
                'porcentajeDia' => SabullvialHelper::getConfig('PORC_DIA'),
                'cotizacionDolar' => SabullvialHelper::getConfig('COTI_DOL'),
                'condicionVenta' => null,
                'cliente' => null
            ]
        ];

        if (!$data['id_condicionventa']) {
            $data['id_condicionventa'] = 0;
            $app->setBody(new JResponseJson($data));
            echo $app->toString(true);
            die();
        }

        JTable::addIncludePath(JPATH_COMPONENT . '/tables/');
        $condicionVenta = JTable::getInstance('SitCondicionesVenta', 'SabullvialTable');
        $condicionVenta->loadByCondicionVenta($data['id_condicionventa']);
        $data['config']['condicionVenta'] = $condicionVenta;
        $data['iva'] = $condicionVenta->hasIVA() ? 1 : 0;

        $app->setBody(new JResponseJson($data));
        echo $app->toString(true);
        die();
    }

    public function listClientes()
    {
        $app = Factory::getApplication();
        $app->setHeader('Content-Type', 'application/json');

        $search = $this->input->getString('query', '');

        /** @var SabullvialModelClientesTango $modelClientesTango */
        $modelClientesTango = $this->getModel('ClientesTango');
        $items = $modelClientesTango->getAllItems($search);

        $app->setBody(new JResponseJson($items));
        echo $app->toString(true);
        die();
    }

    public function listQuotes()
    {
        $app = Factory::getApplication();
        $app->setHeader('Content-Type', 'application/json');

        /** @var SabullvialModelCotizaciones $model */
        $model = $this->getModel('Cotizaciones', 'SabullvialModel', ['ignore_request' => false]);
        $model->setSuffixContext('puntosdeventa.modal');
        $items = $model->getItems();
        $pagination = $model->getPagination();

        JTable::addIncludePath(JPATH_COMPONENT . '/tables/');
        $condicionVenta = JTable::getInstance('SitCondicionesVenta', 'SabullvialTable');

        foreach ($items as &$item) {
            $condicionVenta->loadByCondicionVenta($item->id_condicionventa);
            $item->condicionVenta = $condicionVenta->DESC_COND;
            $item->canSendToFacturacion = SabullvialButtonsHelper::canEnviarAFacturacion($item->id_estadocotizacion);
        }

        $app->setBody(new JResponseJson([
            'items' => $items,
            'pagination' => $pagination
        ]));
        echo $app->toString(true);
        die();
    }


    public function saveCotizacion()
    {
        $app = Factory::getApplication();
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->checkToken('get', false)) {
            echo new JResponseJson(null, 'invalid token', true);
            die();
        }

        $data = $this->input->post->get('jform', [], 'array');

        $productos = $data['productos'];
        if (!empty($productos)) {
            $productos = json_decode($productos);
        }

        $cotizaciondetalle = [];
        foreach ($productos as $i => $producto) {
            $cotizaciondetalle['cotizaciondetalle' . $i] = [
                'codigo_sap' => $producto->codigo_sap,
                'nombre' => $producto->nombre,
                'marca' => $producto->marca,
                'precio' => (float)$producto->precioFinal,
                'cantidad' => (int)$producto->cantidad,
                'descuento' => (float)$producto->descuento,
                'subtotal' => (float)$producto->precioFinal * (int)$producto->cantidad * (1 - ((float)$producto->descuento / 100)),
                'id_producto' => $producto->id, // COD_ARTICU
                'id' => ''
            ];
        }

        $file = $this->input->files->get('ordendecompra_file', null);

        $this->input->post->set('jform[ordendecompra_file]', $file);
        $this->input->post->set('jform[ordendecompra_file_hash]', '');
        $this->input->post->set('jform[ordendecompra_file_name]', '');
        $this->input->post->set('jform[ordendecompra_file_ext]', '');
        $this->input->post->set('jform[ordendecompra_file_delete]', 'off');
        $this->input->post->set('jform[cotizaciondetalle]', $cotizaciondetalle);

        /** @var SabullvialModelCotizacion $model  */
        $model = $this->getModel('Cotizacion');

        $data  = $this->input->post->get('jform', [], 'array');
        $data['cotizaciondetalle'] = $cotizaciondetalle;

        $isSaved = $model->save($data);
        $idCotizacion = $model->getState($model->getName() . '.id');
        $table = $model->getTable();
        $table->load($idCotizacion);

        if ($isSaved) {
            $app->enqueueMessage(Text::_('COM_SABULLVIAL_PUNTOS_DE_VENTA_SAVE_SUCCESS'));
            echo new JResponseJson(['isSaved' => $isSaved, 'data' => $data, 'item' => $table]);
        } else {
            $app->enqueueMessage(Text::_('COM_SABULLVIAL_PUNTOS_DE_VENTA_SAVE_ERROR'), 'warning');
            echo new JResponseJson(['isSaved' => $isSaved, 'data' => $data, 'item' => $table], 'Error al guardar', true);
        }

        die();
    }

    public function changeEstadoTango()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->checkToken('get', false)) {
            echo new JResponseJson(null, 'invalid token', true);
            die();
        }

        $idCotizacion = $this->input->getInt('id');
        $newEstadoTango = $this->input->getInt('id_estado_tango');

        $model = $this->getModel('Cotizacion');
        /** @var SabullvialTableCotizacion $table */
        $table = $model->getTable();
        $table->load($idCotizacion);

        // Si no es consumidor final y le falta el transporte o direccion no avanzo
        if ($table->id_cliente != '000000' && (empty($table->id_transporte) || empty($table->id_direccion))) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_SABULLVIAL_PUNTOS_DE_VENTA_ERROR_DIRECCION_Y_TRANSPORTE_REQUERIDOS'), 'error');
            echo new JResponseJson(['isSaved' => false, 'item' => $table], 'Error al enviar facturación', true);
            die();
        }

        $estadosTango = [SabullvialTableCotizacion::ESTADO_TANGO_SRL, SabullvialTableCotizacion::ESTADO_TANGO_PRUEBA];
        $isEstadoTangoValido = in_array($newEstadoTango, $estadosTango);
        if ($isEstadoTangoValido) {
            $vendedor = SabullvialHelper::getVendedor();
            $params = JComponentHelper::getParams('com_sabullvial');
            $aprobacionAutomatica = $vendedor->get('aprobar.presupuestosAutomaticamente', false);

            if ($aprobacionAutomatica) {
                $table->id_estadocotizacion = $params->get('cotizacion_estado_aprobado_automatico', 0);
                $table->tango_enviar = 1;
            } else {
                $cotizacionesHelper = new SabullvialCotizacionesHelper($params);
                $newIdEstadoCotizacion = $cotizacionesHelper->getNewEstadoCotizacionFromSendToFacturacion($table->id_cliente, $table->id_condicionventa);
                $table->id_estadocotizacion = $newIdEstadoCotizacion;
            }
        }

        $table->id_estado_tango = $newEstadoTango;

        if (!$table->store()) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_SABULLVIAL_PUNTOS_DE_VENTA_ENVIADA_A_FACTURACION_ERROR'), 'warning');
            echo new JResponseJson(['isSaved' => false, 'item' => $table], 'Error al guardar', true);
            die();
        }

        JPluginHelper::importPlugin('system');
        $dispatcher = JEventDispatcher::getInstance();
        $dispatcher->trigger('onContentChangeEstadoTango', ['com_sabullvial.cotizacion', &$table, $table->id_estado_tango]);

        if ($isEstadoTangoValido) {
            $dispatcher->trigger('onContentChangeEstadoCotizacion', ['com_sabullvial.puntosdeventa.modal', &$table, $table->id_estadocotizacion]);
        }

        Factory::getApplication()->enqueueMessage(Text::_('COM_SABULLVIAL_PUNTOS_DE_VENTA_ENVIADA_A_FACTURACION_SUCCESS'));
        echo new JResponseJson(['isSaved' => true, 'item' => $table]);
        die();
    }

    public function getCotizacion()
    {
        $app = Factory::getApplication();
        $app->setHeader('Content-Type', 'application/json');

        if (!$this->checkToken('get', false)) {
            echo new JResponseJson(null, 'invalid token', true);
            die();
        }

        $idCotizacion = $this->input->getInt('id');

        /** @var SabullvialModelCotizacion $model */
        $model = $this->getModel('Cotizacion');

        $item = $model->getItem($idCotizacion);

        $data = $this->prepareDataOnChangecliente(
            $item->id_condicionventa,
            (int)$item->iva,
            (int)$item->dolar,
            $item->id_cliente
        );

        /** @var SabullvialModelPuntoDeVenta $modelPuntoDeVenta */
        $modelPuntoDeVenta = $this->getModel('PuntoDeVenta');
        $cliente = $modelPuntoDeVenta->findCliente($item->id_cliente);

        $app->setBody(new JResponseJson([
            'cotizacion' => $item,
            'cliente' => $cliente,
            'direcciones' => $data['direcciones'],
            'config' => $data['config'],
        ]));
        echo $app->toString(true);
        die();
    }

    protected function prepareDataOnChangecliente($idCondicionVenta = 49, $iva = 1, $dolar = 0, $codigoCliente = '')
    {
        $config = SabullvialHelper::getMultiConfig(['PORC_DIA', 'COTI_DOL']);
        $data = [
            'id_condicionventa' => $idCondicionVenta,
            'iva' =>  $iva,
            'dolar' => $dolar,
            'direcciones' => [],
            'config' => [
                'porcentajeDia' => $config['PORC_DIA'],
                'cotizacionDolar' => $config['COTI_DOL'],
                'condicionVenta' => null,
                'cliente' => null,
            ]
        ];

        // si es consumidor final
        if (empty($codigoCliente) || $codigoCliente == '000000') {
            $data['id_condicionventa'] = 49; // default para consumidor final

            $condicionVenta = JTable::getInstance('SitCondicionesVenta', 'SabullvialTable');
            $condicionVenta->loadByCondicionVenta($data['id_condicionventa']);
            $data['config']['condicionVenta'] = $condicionVenta;
            $data['iva'] = $condicionVenta->hasIVA() ? 1 : 0;

            return $data;
        }

        JTable::addIncludePath(JPATH_COMPONENT . '/tables/');
        /** @var SabullvialTableSitClientes $cliente */
        $cliente = JTable::getInstance('SitClientes', 'SabullvialTable');
        $cliente->loadByCodCliente($codigoCliente);
        $data['config']['cliente'] = $cliente;

        /** @var SabullvialTableSitClientesDireccionEntrega $clienteDirecciones */
        $clienteDirecciones = JTable::getInstance('SitClientesDireccionEntrega', 'SabullvialTable');
        $data['direcciones'] = $clienteDirecciones->listByCodCliente($codigoCliente);

        $condicionVenta = JTable::getInstance('SitCondicionesVenta', 'SabullvialTable');
        $condicionVenta->loadByCondicionVenta($cliente->COND_VTA);
        $data['config']['condicionVenta'] = $condicionVenta;

        $data['id_condicionventa'] = (int)$condicionVenta->COND_VTA;
        $data['iva'] = $condicionVenta->hasIVA() ? 1 : 0;

        return $data;
    }
}

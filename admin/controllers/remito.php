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
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Language\Text;

/**
 * Remito Controller
 *
 * @package     Sabullvial.Administrator
 * @subpackage  com__sabullvial
 */
class SabullvialControllerRemito extends FormController
{
    protected $view_list = 'remitos';

    public function batch($model = null)
    {
        /** @var SabullvialModelRemito $model */
        $model = $this->getModel();
        $this->setRedirect((string)JUri::getInstance());

        $vars = $this->input->post->get('batch', [], 'array');
        $cid  = (array) $this->input->post->get('cid', [], 'cmd'); // sobrescribo la función batch para permitir ID de String

        // Remove zero values resulting from input filter
        $cid = array_filter($cid);

        // Build an array of item contexts to check
        $contexts = [];

        $option = isset($this->extension) ? $this->extension : $this->option;

        foreach ($cid as $id) {
            // If we're coming from com_categories, we need to use extension vs. option
            $contexts[$id] = $option . '.' . $this->context . '.' . $id;
        }

        // Attempt to run the batch operation.
        if ($model->batch($vars, $cid, $contexts)) {
            $this->setMessage(\JText::_('JLIB_APPLICATION_SUCCESS_BATCH'));

            return true;
        } else {
            $this->setMessage(\JText::sprintf('JLIB_APPLICATION_ERROR_BATCH_FAILED', $model->getError()), 'warning');

            return false;
        }
    }

    public function marcarComoEntregadoPorMostrador()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->checkToken('get', false)) {
            echo new JsonResponse(null, 'invalid token', true);
            die();
        }

        $cid  = (array) $this->input->post->get('cid', [], 'cmd'); // sobrescribo la función batch para permitir ID de String
        $deliveryDate = $this->input->post->get('delivery_date', '', 'string');

        // Remove zero values resulting from input filter
        $cid = array_filter($cid);

        // Build an array of item contexts to check
        $contexts = [];

        $option = isset($this->extension) ? $this->extension : $this->option;

        foreach ($cid as $id) {
            // If we're coming from com_categories, we need to use extension vs. option
            $contexts[$id] = $option . '.' . $this->context . '.' . $id;
        }

        $data = [
            'delivery_date' => $deliveryDate,
        ];

        /** @var SabullvialModelRemito $model */
        $model = $this->getModel();
        if (!$model->batchMarcarComoEntregadoPorMostrador($data, $cid, $contexts)) {
            echo new JsonResponse(null, $model->getError(), true);
            die();
        }

        echo new JsonResponse(null, null, false);
        die();
    }

    public function addRemitosToRouteSheet()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->checkToken('get', false)) {
            echo new JsonResponse(null, 'invalid token', true);
            die();
        }

        $cid  = (array) $this->input->post->get('cid', [], 'cmd'); // sobrescribo la función batch para permitir ID de String
        $idHojaDeRuta = $this->input->post->get('id_hojaderuta', '', 'string');

        // Remove zero values resulting from input filter
        $cid = array_filter($cid);

        // Build an array of item contexts to check
        $contexts = [];

        $option = isset($this->extension) ? $this->extension : $this->option;

        foreach ($cid as $id) {
            // If we're coming from com_categories, we need to use extension vs. option
            $contexts[$id] = $option . '.' . $this->context . '.' . $id;
        }

        $data = [
            'id_hojaderuta' => $idHojaDeRuta,
        ];

        /** @var SabullvialModelRemito $model */
        $model = $this->getModel();
        if (!$model->batchAddRemitosToHojaDeRuta($data, $cid, $contexts)) {
            echo new JsonResponse(null, $model->getError(), true);
            die();
        }

        echo new JsonResponse(null, null, false);
        die();
    }

    public function deleteRemitosFromRouteSheet()
    {
        $app = Factory::getApplication();
        $app->setHeader('Content-Type', 'application/json');

        if (!$this->checkToken('get', false)) {
            echo new JsonResponse(null, 'invalid token', true);
            die();
        }

        $cid  = (array) $this->input->post->get('cid', [], 'cmd');

        // Remove zero values resulting from input filter
        $cid = array_filter($cid);

        // Build an array of item contexts to check
        $contexts = [];

        $option = isset($this->extension) ? $this->extension : $this->option;

        foreach ($cid as $id) {
            // If we're coming from com_categories, we need to use extension vs. option
            $contexts[$id] = $option . '.' . $this->context . '.' . $id;
        }

        $data = [];

        /** @var SabullvialModelRemito $model */
        $model = $this->getModel();
        if (!$model->batchDeleteRemitosFromHojaDeRuta($data, $cid, $contexts)) {
            $app->setBody(new JsonResponse(null, $model->getError(), true));
            echo $app->toString(true);
            die();
        }


        $app->setBody(new JsonResponse(null, null, false));
        echo $app->toString(true);
        die();
    }

    public function generarHojaDeRuta()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->checkToken('get', false)) {
            echo new JsonResponse(null, 'invalid token', true);
            die();
        }

        $cid  = (array) $this->input->post->get('cid', [], 'cmd'); // sobrescribo la función batch para permitir ID de String
        $deliveryDate = $this->input->post->get('delivery_date', '', 'string');
        $idVehiculo = $this->input->post->get('id_vehiculo', '', 'string');
        $patente = $this->input->post->get('patente', '', 'string');
        $idChofer = $this->input->post->get('id_chofer', '', 'string');
        $choferNombre = $this->input->post->get('chofer_nombre', '', 'string');

        // Remove zero values resulting from input filter
        $cid = array_filter($cid);

        // Build an array of item contexts to check
        $contexts = [];

        $option = isset($this->extension) ? $this->extension : $this->option;

        foreach ($cid as $id) {
            // If we're coming from com_categories, we need to use extension vs. option
            $contexts[$id] = $option . '.' . $this->context . '.' . $id;
        }

        $data = [
            'delivery_date' => $deliveryDate,
            'id_vehiculo' => $idVehiculo,
            'patente' => $patente,
            'id_chofer' => $idChofer,
            'chofer' => $choferNombre,
        ];

        /** @var SabullvialModelRemito $model */
        $model = $this->getModel();
        if (!$model->batchGenerarHojaDeRuta($data, $cid, $contexts)) {
            echo new JsonResponse(null, $model->getError(), true);
            die();
        }

        echo new JsonResponse(null, null, false);
        die();
    }

    public function getDetailRemito()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->checkToken('get', false)) {
            echo new JsonResponse(null, 'invalid token', true);
            die();
        }

        $idRemito = $this->input->post->get('numero_remito', '', 'string');

        /** @var SabullvialModelRemito $model */
        $model = $this->getModel();
        $remito = $model->getItem($idRemito);

        $productos = $model->getProductos($idRemito);
        $remito->productos = array_map(function ($producto) {
            $producto->cantidad_factura = (int) $producto->cantidad_factura;
            $producto->cantidad_pedido = (int) $producto->cantidad_pedido;
            $producto->cantidad_remito = (int) $producto->cantidad_remito;
            return $producto;
        }, $productos);

        $historico = $model->getHistorico($idRemito);
        $remito->historico = array_map(function ($item) {
            $item->entregado = (bool)$item->entregado;
            $item->entregado_mostrador = (bool)$item->entregado_mostrador;
            $item->published = (bool)$item->published;
            return $item;
        }, $historico);

        echo new JsonResponse($remito, null, false);
        die();
    }

    public function listHojasDeRuta()
    {
        // if (!$this->checkToken('get', false)) {
        //     echo new JsonResponse(null, 'invalid token', true);
        //     die();
        // }

        $app = Factory::getApplication();
        $app->setHeader('Content-Type', 'application/json');

        $search = $this->input->getString('query', '');

        /** @var SabullvialModelHojasDeRuta $model */
        $model = $this->getModel('HojasDeRuta', 'SabullvialModel');
        $model->setState('filter.published', 1);
        $items = $model->getAllItems($search);

        $app->setBody(new JResponseJson($items));
        echo $app->toString(true);
        die();
    }

    public function deleteImagen()
    {
        $app = Factory::getApplication();
        $app->setHeader('Content-Type', 'application/json');

        if (!$this->checkToken('get', false)) {
            $app->enqueueMessage(Text::_('JINVALID_TOKEN'), 'error');

            $app->setBody(new JsonResponse(null, 'invalid token', true));
            echo $app->toString(true);
            die();
        }

        $model = $this->getModel();

        $recordId = $this->input->getCwd('id');

        if (!$model->deleteImage($recordId)) {
            $app->enqueueMessage($model->getError(), 'error');

            $app->setBody(new JsonResponse(null, null, true));
            echo $app->toString(true);
            die();
        }

        $app->enqueueMessage(Text::sprintf('COM_SABULLVIAL_REMITO_IMAGE_DELETED_SUCCESSFULLY', $recordId), 'message');
        $app->setBody(new JsonResponse(null, null, false));
        echo $app->toString(true);
        die();
    }

    public function deleteImagenByIdRemitoHistorico()
    {
        $app = Factory::getApplication();
        $app->setHeader('Content-Type', 'application/json');

        if (!$this->checkToken('get', false)) {
            $app->enqueueMessage(Text::_('JINVALID_TOKEN'), 'error');

            $app->setBody(new JsonResponse(null, 'invalid token', true));
            echo $app->toString(true);
            die();
        }

        $model = $this->getModel();

        $recordId = $this->input->getCwd('id');

        if (!$model->deleteImageByIdRemitoHistorico($recordId)) {
            $app->enqueueMessage($model->getError(), 'error');

            $app->setBody(new JsonResponse(null, null, true));
            echo $app->toString(true);
            die();
        }

        $app->enqueueMessage(Text::sprintf('COM_SABULLVIAL_REMITO_IMAGE_DELETED_SUCCESSFULLY', $recordId), 'message');
        $app->setBody(new JsonResponse(null, null, false));
        echo $app->toString(true);
        die();
    }
}

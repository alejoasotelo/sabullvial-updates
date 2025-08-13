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

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Response\JsonResponse;

/**
 * Remitos Controller
 *
 * @package     Sabullvial.Administrator
 * @subpackage  com__sabullvial
 */
class SabullvialControllerRemitos extends AdminController
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
    public function getModel($name = 'Remito', $prefix = 'SabullvialModel', $config = ['ignore_request' => true])
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    public function optimizarImagenesChunk()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->checkToken('post', false)) {
            echo new JsonResponse(null, 'Invalid token', true);
            die();
        }

        $model = $this->getModel('Remitos');
        $result = $model->processImagesChunk();

        $params = ComponentHelper::getParams('com_sabullvial');
        $index = (int) $params->get('remitos_optimizar_imagenes_index', 0);

        if ($result['hasMore']) {
            $params->set('remitos_optimizar_imagenes_index', $index + 1);
        } else {
            // Si ya no hay más, reiniciar a 0
            $params->set('remitos_optimizar_imagenes_index', 0);
        }

        $result['resetIndex'] = !$result['hasMore'];

        // Guardar config
        $table = Table::getInstance('extension');
        $table->load(['element' => 'com_sabullvial']);
        $table->params = (string) $params->toString();
        $table->store();

        // Devolver JSON
        echo new JsonResponse($result, 'Chunk processed successfully', false);

        Factory::getApplication()->close();
    }

    public function resetOptimiceImagesChunkIndex()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->checkToken('post', false)) {
            echo new JsonResponse(null, 'Invalid token', true);
            die();
        }

        $params = ComponentHelper::getParams('com_sabullvial');
        $params->set('remitos_optimizar_imagenes_index', 0);
        // Guardar config
        $table = Table::getInstance('extension');
        $table->load(['element' => 'com_sabullvial']);
        $table->params = (string) $params->toString();
        $table->store();

        echo new JsonResponse(null, 'Índice reiniciado correctamente', false);

        Factory::getApplication()->close();
    }
}

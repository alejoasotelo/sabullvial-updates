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

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

require_once JPATH_COMPONENT_ADMINISTRATOR . '/tables/estadocotizacion.php';

/**
 * Cotizaciones Controller
 *
 * @package     Sabullvial.Administrator
 * @subpackage  com__sabullvial
 */
class SabullvialControllerCotizaciones extends AdminController
{
    public const ESTADO_COTIZACION_APROBADO_COMPLETO = 'aprobado_completo';
    public const ESTADO_COTIZACION_APROBADO_CON_FALTANTES = 'aprobado_con_faltantes';

    public const ACTION_ESTADO_COTIZACION_CANCELADO_PATTERN = 'cancelado_';

    protected $text_prefix = 'COM_SABULLVIAL_COTIZACIONES';

    /**
     * Constructor.
     *
     * @param   array                $config   An optional associative array of configuration settings.
     * @param   MVCFactoryInterface  $factory  The factory.
     *
     * @see     \JControllerLegacy
     * @since   1.6
     * @throws  \Exception
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $this->registerTask('aprobado', 'changeEstadoCotizacion');
        $this->registerTask('rechazado', 'changeEstadoCotizacion');
        $this->registerTask('pendiente', 'changeEstadoCotizacion');
        $this->registerTask(self::ESTADO_COTIZACION_APROBADO_COMPLETO, 'changeEstadoCotizacion');
        $this->registerTask(self::ESTADO_COTIZACION_APROBADO_CON_FALTANTES, 'changeEstadoCotizacion');

        $items = Table::getInstance('EstadoCotizacion', 'SabullvialTable')->getEstadosCancelado();
        foreach ($items as $item) {
            $this->registerTask(self::ACTION_ESTADO_COTIZACION_CANCELADO_PATTERN . $item->id, 'changeEstadoCotizacion');
        }

        $this->registerTask('srl', 'changeEstadoTangoCotizacion');
        $this->registerTask('automatica', 'changeEstadoTangoCotizacion');
        $this->registerTask('prueba', 'changeEstadoTangoCotizacion');
    }
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
    public function getModel($name = 'Cotizacion', $prefix = 'SabullvialModel', $config = ['ignore_request' => true])
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    public function duplicate()
    {
        $this->checkToken('get');

        $id = $this->input->getInt('id');

        $model = $this->getModel();

        try {
            if (!$model->duplicate($id)) {
                $this->setMessage($model->getError(), 'error');
            } else {
                $this->setMessage(Text::_($this->text_prefix . '_DUPLICATE_SUCCESS'));
            }
        } catch (\Exception $e) {
            $this->setMessage($e->getMessage(), 'error');
        }

        $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
    }

    /**
     * Method to approve a list of items
     *
     * @return  void
     *
     * @since   1.6
     */
    public function changeEstadoCotizacion()
    {
        // Check for request forgeries
        $this->checkToken();

        // Get items to publish from the request.
        $cid = (array) $this->input->get('cid', [], 'int');
        $task = $this->getTask();

        $newIdEstadoCotizacion = $this->getIdEstadoCotizacionByTask($task);

        $cid = array_filter($cid);

        if (empty($cid)) {
            \JLog::add(\JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), \JLog::WARNING, 'jerror');
        } else {
            // Get the model.
            /** @var SabullvialModelCotizacion $model */
            $model = $this->getModel();

            // Publish the items.
            try {
                $model->changeEstadoCotizacion($cid, $newIdEstadoCotizacion);

                $errors = $model->getErrors();

                $suffix = $this->isTaskCancelado($task) ? 'CANCELADO' : strtoupper($task);

                if ($errors) {
                    \JFactory::getApplication()->enqueueMessage(\JText::plural($this->text_prefix . '_N_ITEMS_FAILED_' . $suffix, count($cid)), 'error');
                } else {
                    $this->processEnviarATango($cid, $task);

                    $ntext = $this->text_prefix . '_N_ITEMS_' . $suffix;
                    $this->setMessage(\JText::plural($ntext, count($cid)));
                }
            } catch (\Exception $e) {
                $this->setMessage($e->getMessage(), 'error');
            }
        }

        $this->setRedirect(\JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
    }

    /**
     * Función que procesa el envío de la cotización a Tango
     *
     * @param array $cid
     * @param string $task
     *
     * @return void
     */
    public function processEnviarATango($cid, $task)
    {
        if ($task != self::ESTADO_COTIZACION_APROBADO_COMPLETO && $task != self::ESTADO_COTIZACION_APROBADO_CON_FALTANTES) {
            return;
        }

        foreach ($cid as $id) {
            $table = Table::getInstance('Cotizacion', 'SabullvialTable');
            $table->load($id);
            $table->tango_enviar = 1;
            $table->store();
        }
    }

    /**
     * Method to approve a list of items
     *
     * @return  void
     *
     * @since   1.6
     */
    public function changeEstadoTangoCotizacion()
    {
        // Check for request forgeries
        $this->checkToken();

        // Get items to publish from the request.
        $cid = (array) $this->input->get('cid', [], 'int');
        $task = $this->getTask();

        // Get the model.
        /** @var SabullvialModelCotizacion $model */
        $model = $this->getModel();
        $table = $model->getTable();
        $constant = 'SabullvialTableCotizacion::ESTADO_TANGO_' . strtoupper($task);

        if (!defined($constant)) {
            \JLog::add(\JText::_($this->text_prefix . '_NO_VALUE_EXISTS'), \JLog::WARNING, 'jerror');
            return $this->setRedirect(\JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
        }

        $value = constant($constant);

        // Remove zero values resulting from input filter
        $cid = array_filter($cid);

        if (empty($cid)) {
            \JLog::add(\JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), \JLog::WARNING, 'jerror');
        } else {
            // Publish the items.
            try {
                $model->changeEstadoTangoCotizacion($cid, $value);

                $errors = $model->getErrors();

                if ($errors) {
                    \JFactory::getApplication()->enqueueMessage(\JText::plural($this->text_prefix . '_N_ITEMS_FAILED_' . strtoupper($task), count($cid)), 'error');
                } else {
                    $estadosTango = [SabullvialTableCotizacion::ESTADO_TANGO_SRL, SabullvialTableCotizacion::ESTADO_TANGO_PRUEBA];
                    if (in_array($value, $estadosTango)) {
                        $vendedor = SabullvialHelper::getVendedor();
                        $params = JComponentHelper::getParams('com_sabullvial');
                        $aprobacionAutomatica = $vendedor->get('aprobar.presupuestosAutomaticamente', false);

                        if ($aprobacionAutomatica) {
                            $idEstadoAprobadoAutomatico = $params->get('cotizacion_estado_aprobado_automatico', 0);
                            $model->changeEstadoCotizacion($cid, $idEstadoAprobadoAutomatico);

                            foreach ($cid as $id) {
                                $table->load($id);
                                $table->tango_enviar = 1;
                                $table->store();
                            }
                        } else {
                            $table->load($cid[0]);

                            if (!$table->id) {
                                $newIdEstadoCotizacion = Table::getInstance('EstadoCotizacion', 'SabullvialTable')->getEstadoPendienteId();
                            } else {
                                $cotizacionesHelper = new SabullvialCotizacionesHelper($params);
                                $newIdEstadoCotizacion = $cotizacionesHelper->getNewEstadoCotizacionFromSendToFacturacion($table->id_cliente, $table->id_condicionventa);
                            }

                            $model->changeEstadoCotizacion($cid, $newIdEstadoCotizacion);
                        }
                    }

                    $ntext = $this->text_prefix . '_N_ITEMS_' . strtoupper($task);
                    $this->setMessage(\JText::plural($ntext, count($cid)));
                }
            } catch (\Exception $e) {
                $this->setMessage($e->getMessage(), 'error');
            }
        }


        $this->setRedirect(\JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
    }

    public function aprobarPago()
    {
        // Check for request forgeries
        $this->checkToken();

        // Get items to publish from the request.
        $cid = (array) $this->input->get('cid', [], 'int');
        $task = $this->getTask();

        $cmpParams = SabullvialHelper::getComponentParams();
        $idEstadoCotizacionPago = (int) $cmpParams->get('cotizacion_estado_esperar_pagos_pagado');

        if (!$idEstadoCotizacionPago) {
            \JLog::add(\JText::_($this->text_prefix . '_NO_VALUE_EXISTS'), \JLog::WARNING, 'jerror');
            return $this->setRedirect(\JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
        }

        $cid = array_filter($cid);

        if (empty($cid)) {
            \JLog::add(\JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), \JLog::WARNING, 'jerror');
        } else {
            // Get the model.
            /** @var SabullvialModelCotizacion $model */
            $model = $this->getModel();

            // Publish the items.
            try {
                $model->changeEstadoCotizacionPago($cid, $idEstadoCotizacionPago);

                $errors = $model->getErrors();

                $suffix = 'APROBADO';

                if ($errors) {
                    \JFactory::getApplication()->enqueueMessage(\JText::plural($this->text_prefix . '_N_ITEMS_FAILED_' . $suffix, count($cid)), 'error');
                } else {
                    $this->processEnviarATango($cid, $task);

                    $ntext = $this->text_prefix . '_N_ITEMS_' . $suffix;
                    $this->setMessage(\JText::plural($ntext, count($cid)));
                }
            } catch (\Exception $e) {
                $this->setMessage($e->getMessage(), 'error');
            }
        }

        $this->setRedirect(\JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
    }

    public function downloadOrdenDeCompra()
    {
        JSession::checkToken('get') or die('Invalid Token');
        $idProject = JRequest::getInt('id', 0);
        $hash = JRequest::getString('hash', 0);

        $cotizacion = $this->getModel()->getTable();
        $cotizacion->load($idProject);

        //Table::addIncludePath(__DIR__);$SasteelhouseTableProject

        if ($cotizacion->id > 0 && $hash == $cotizacion->ordendecompra_file_hash) {
            $file = JPATH_SITE . '/media/com_sabullvial/ordendes_de_compra/' . $cotizacion->id . '/' . $cotizacion->ordendecompra_file_hash;

            $size = filesize($file);

            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $cotizacion->ordendecompra_file_name . '"');
            header('Content-Transfer-Encoding: binary');
            header('Connection: Keep-Alive');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . $size);
            readfile($file);
        }
        exit();
    }

    public function listCotizaciones()
    {
        $app = JFactory::getApplication();
        $app->setHeader('Content-Type', 'application/json');

        /** @var SabullvialModelCotizaciones $model */
        $model = $this->getModel('Cotizaciones', 'SabullvialModel', ['ignore_request' => false]);
        $items = $model->getItems();
        $pagination = $model->getPagination();

        $app->setBody(new JResponseJson([
            'items' => $items,
            'pagination' => $pagination
        ]));
        echo $app->toString(true);
        die();
    }

    /**
     * Devuelve el id del estado de la cotización según la tarea
     *
     * @param string $task
     *
     * @return int
     */
    protected function getIdEstadoCotizacionByTask($task)
    {
        if ($this->isTaskCancelado($task)) {
            $id = (int) str_replace(self::ACTION_ESTADO_COTIZACION_CANCELADO_PATTERN, '', $task);
            return $id;
        }

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('id')
            ->from('#__sabullvial_estadocotizacion');

        if ($task == self::ESTADO_COTIZACION_APROBADO_COMPLETO) {
            $query->where('aprobado = 1 AND revisado = ' . SabullvialTableEstadoCotizacion::ESTADO_REVISION_COMPLETA);
        } elseif ($task == self::ESTADO_COTIZACION_APROBADO_CON_FALTANTES) {
            $query->where('aprobado = 1 AND revisado = ' . SabullvialTableEstadoCotizacion::ESTADO_REVISION_COMPLETA_CON_FALTANTES);
        } else {
            $query->where($task . ' = 1');
        }

        return (int)$db->setQuery($query)->loadResult();
    }

    /**
     * Devuelve true si la tarea es de cancelación
     *
     * @param string $task
     *
     * @return bool
     */
    protected function isTaskCancelado($task)
    {
        return strpos($task, self::ACTION_ESTADO_COTIZACION_CANCELADO_PATTERN) === 0;
    }
}

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
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Factory;

/**
 * Cotizacion Controller
 *
 * @package     Sabullvial.Administrator
 * @subpackage  com__sabullvial
 */
class SabullvialControllerCotizacion extends JControllerForm
{
    protected $view_list = 'cotizaciones';

    protected $text_prefix = 'COM_SABULLVIAL_COTIZACION';

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

        $this->registerTask('srl', 'changeEstadoTangoCotizacion');
        $this->registerTask('automatica', 'changeEstadoTangoCotizacion');
        $this->registerTask('prueba', 'changeEstadoTangoCotizacion');
    }

    public function reload($key = null, $urlVar = null)
    {
        $app = JFactory::getApplication();
        $subtask = $app->input->getCmd('subtask', '');

        if (!empty($subtask)) {
            $app->setUserState($this->option . '.edit.' . $this->context . '.subtask', $subtask);

            if ($subtask == 'cotizacion.changeCliente' || $subtask == 'cotizacion.changeTransporte') {
                $app->setUserState($this->option . '.edit.' . $this->context . '.tab', 'general');
            } else {
                $app->setUserState($this->option . '.edit.' . $this->context . '.tab', 'products');
            }
        }

        return parent::reload();
    }

    /**
     * Implement to allowAdd or not
     *
     * Not used at this time (but you can look at how other components use it....)
     * Overwrites: JControllerForm::allowAdd
     *
     * @param array $data
     * @return bool
     */
    protected function allowAdd($data = [])
    {
        return parent::allowAdd($data);
    }

    /**
     * Method override to check if you can edit an existing record.
     *
     * @param   array   $data  An array of input data.
     * @param   string  $key   The name of the key for the primary key.
     *
     * @return  boolean
     *
     * @since   1.6
     */
    protected function allowEdit($data = [], $key = 'id')
    {
        $recordId = (int) isset($data[$key]) ? $data[$key] : 0;
        $user = JFactory::getUser();
        $vendedor = SabullvialHelper::getVendedor();

        // Zero record (id:0), return component edit permission by calling parent controller method
        if (!$recordId) {
            return parent::allowEdit($data, $key);
        }

        // Check edit on the record asset (explicit or inherited)
        if ($user->authorise('core.edit', 'com_sabullvial.cotizacion.' . $recordId) || $vendedor->get('ver.presupuestos', false)) {
            return true;
        }

        // Check edit own on the record asset (explicit or inherited)
        if ($user->authorise('core.edit.own', 'com_sabullvial.cotizacion.' . $recordId)) {
            // Existing record already has an owner, get it
            $record = $this->getModel()->getItem($recordId);

            if (empty($record)) {
                return false;
            }

            // Grant if current user is owner of the record
            return $user->id == $record->created_by;
        }

        return false;
    }

    /**
     * Function that allows child controller access to model data
     * after the data has been saved.
     *
     * @param   \JModelLegacy  $model      The data model object.
     * @param   array          $validData  The validated data.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function postSaveHook($model, $validData = [])
    {
        // Si no tiene estado de Tango (prueba o si) salgo
        $estadosTango = [SabullvialTableCotizacion::ESTADO_TANGO_SRL, SabullvialTableCotizacion::ESTADO_TANGO_PRUEBA];
        if (!in_array($validData['id_estado_tango'], $estadosTango)) {
            return false;
        }

        $table = $model->getTable();
        $table->load($this->input->getInt('id'));

        if (!$table->id) {
            return false;
        }

        $vendedor = SabullvialHelper::getVendedor();
        $aprobacionAutomatica = $vendedor->get('aprobar.presupuestosAutomaticamente', false);

        /** @var SabullvialTableEstadoCotizacion $estadoCotizacion */
        $estadoCotizacion = $table->getEstadoCotizacion($table->id_estadocotizacion);

        // Si no tiene aprobación automática o no está aprobado salgo
        if (!$aprobacionAutomatica && !$estadoCotizacion->aprobado) {
            return false;
        }

        $table->tango_enviar = 1;

        if ($aprobacionAutomatica) {
            $table->id_estadocotizacion = $estadoCotizacion->getEstadoAprobadoAutomaticoId();
        }

        return $table->store();
    }

    public function review($key = null, $urlVar = null)
    {
        $this->checkToken();

        /** @var SabullvialModelCotizacion $model */
        $model = $this->getModel();
        $table = $model->getTable();
        $context = "$this->option.review.$this->context";
        $this->setRedirect((string)JUri::getInstance());

        $recordId = $this->input->getInt('reviewCotizacionId', 0);

        $data = $this->input->post->getString('review', '');

        if (empty($data)) {
            return false;
        }

        $data = json_decode($data);

        // Attempt to run the review operation.
        if (!$model->review($data, $recordId, $context)) {
            $this->setMessage(JText::sprintf('JLIB_APPLICATION_ERROR_BATCH_FAILED', $model->getError()), 'warning');
            $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
            return false;
        }

        $this->setMessage(JText::_('JLIB_APPLICATION_SUCCESS_BATCH'));
        $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
        return true;
    }

    public function createWithFaltantes($key = null, $urlVar = null)
    {
        $this->checkToken();

        /** @var SabullvialModelCotizacion $model */
        $model = $this->getModel();
        $context = "$this->option.createWithFaltantes.$this->context";
        $this->setRedirect((string)JUri::getInstance());

        $recordId = $this->input->getInt('viewReviewCotizacionId', 0);

        if (!$recordId) {
            return false;
        }

        // Attempt to run the review operation.
        if (!$model->createWithFaltantes($recordId, $context)) {
            $this->setMessage(JText::sprintf('JLIB_APPLICATION_ERROR_BATCH_FAILED', $model->getError()), 'warning');
            $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
            return false;
        }

        $this->setMessage(JText::_('JLIB_APPLICATION_SUCCESS_BATCH'));
        $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
        return true;
    }

    /**
     * Method to approve a list of items
     *
     * @return  void
     *
     * @since   1.6
     */
    public function changeEstadoTangoCotizacion($key = null, $urlVar = null)
    {
        // Check for request forgeries
        $this->checkToken();

        // Get the model.
        /** @var SabullvialModelCotizacion $model */
        $model = $this->getModel();
        $table = $model->getTable();
        $task = $this->getTask();

        // Determine the name of the primary key for the data.
        if (empty($key)) {
            $key = $table->getKeyName();
        }

        // To avoid data collisions the urlVar may be different from the primary key.
        if (empty($urlVar)) {
            $urlVar = $key;
        }

        $recordId = $this->input->getInt($urlVar);
        $idCotizacion = $recordId; // clonoe recordId porque changeEstadoTangoCotizacion cambia los ids
        $constant = 'SabullvialTableCotizacion::ESTADO_TANGO_' . strtoupper($task);

        if (!defined($constant)) {
            \JLog::add(\JText::_($this->text_prefix . '_NO_VALUE_EXISTS'), \JLog::WARNING, 'jerror');

            return $this->setRedirect(
                \JRoute::_(
                    'index.php?option=' . $this->option . '&view=' . $this->view_item
                    . $this->getRedirectToItemAppend($idCotizacion, $urlVar),
                    false
                )
            );
        }

        $value = constant($constant);

        if (empty($recordId)) {
            \JLog::add(\JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), \JLog::WARNING, 'jerror');
        } else {
            // Publish the items.
            try {
                $model->changeEstadoTangoCotizacion($recordId, $value);

                $errors = $model->getErrors();

                if ($errors) {
                    \JFactory::getApplication()->enqueueMessage(\JText::_($this->text_prefix . '_ITEM_FAILED_' . strtoupper($task)), 'error');
                } else {
                    $estadosTango = [SabullvialTableCotizacion::ESTADO_TANGO_SRL, SabullvialTableCotizacion::ESTADO_TANGO_PRUEBA];
                    if (in_array($value, $estadosTango)) {
                        $vendedor = SabullvialHelper::getVendedor();
                        $params = JComponentHelper::getParams('com_sabullvial');
                        $aprobacionAutomatica = $vendedor->get('aprobar.presupuestosAutomaticamente', false);

                        if ($aprobacionAutomatica) {
                            $idEstadoAprobadoAutomatico = $params->get('cotizacion_estado_aprobado_automatico', 0);
                            $model->changeEstadoCotizacion($recordId, $idEstadoAprobadoAutomatico);

                            foreach ($recordId as $id) {
                                $table->load($id);
                                $table->tango_enviar = 1;
                                $table->store();
                            }
                        } else {
                            $table->load($idCotizacion);

                            if (!$table->id) {
                                $newIdEstadoCotizacion = Table::getInstance('EstadoCotizacion', 'SabullvialTable')->getEstadoPendienteId();
                            } else {
                                $cotizacionesHelper = new SabullvialCotizacionesHelper($params);
                                $newIdEstadoCotizacion = $cotizacionesHelper->getNewEstadoCotizacionFromSendToFacturacion($table->id_cliente, $table->id_condicionventa);
                            }

                            $model->changeEstadoCotizacion($recordId, $newIdEstadoCotizacion);
                        }
                    }

                    $ntext = $this->text_prefix . '_ITEM_' . strtoupper($task);
                    $this->setMessage(\JText::_($ntext));
                }
            } catch (\Exception $e) {
                $this->setMessage($e->getMessage(), 'error');
            }
        }

        return $this->setRedirect(
            \JRoute::_(
                'index.php?option=' . $this->option . '&view=' . $this->view_item
                . $this->getRedirectToItemAppend($idCotizacion, $urlVar),
                false
            )
        );
    }

    /**
     * Method to approve a list of items
     *
     * @return  void
     *
     * @since   1.6
     */
    public function changeEstadoCotizacionAjax()
    {
        $app = Factory::getApplication();
        $app->setHeader('Content-Type', 'application/json');

        if (!$this->checkToken('get', false)) {
            echo new JsonResponse(null, 'invalid token', true);
            die();
        }

        $id = $this->input->getInt('id');
        $idCotizacion = $id; // clono $id porque changeEstadoTangoCotizacion cambia los ids a array
        $idEstadoCotizacion = $this->input->getInt('id_estadocotizacion');

        if (!$id || !$idEstadoCotizacion) {
            echo new JsonResponse(null, 'invalid data', true);
            die();
        }

        /** @var SabullvialModelCotizacion $model */
        $model = $this->getModel();

        try {
            if (!$model->changeEstadoCotizacion($id, $idEstadoCotizacion)) {
                $app->enqueueMessage(Text::_('COM_SABULLVIAL_COTIZACION_CAMBIAR_ESTADO_ERROR'), 'error');
                $app->setBody(new JsonResponse(null, 'error', true));
                echo $app->toString(true);
                die();
            }

            $app->enqueueMessage(Text::_('COM_SABULLVIAL_COTIZACION_CAMBIAR_ESTADO_SUCCESS'), 'success');

            if (!$this->processEnviarATango($idCotizacion, $idEstadoCotizacion)) {
                $app->enqueueMessage(Text::_('COM_SABULLVIAL_COTIZACION_CAMBIAR_ESTADO_ERROR_ENVIAR_TANGO'), 'warning');
            }

            $estadoCotizacion = Table::getInstance('Cotizacion', 'SabullvialTable')->getLastEstadoCotizacionHistorico($idCotizacion);

            if ($estadoCotizacion->id) {
                $estadoCotizacion->created = JHtml::_('date', $estadoCotizacion->created, Text::_('DATE_FORMAT_LC5')).'hs';
            }

            $app->setBody(new JsonResponse($estadoCotizacion, null, false));
            echo $app->toString(true);
            die();
        } catch (\Exception $e) {
            $app->setBody(new JsonResponse($e, null, true));
            echo $app->toString(true);
            die();
        }
    }

    /**
     * Función que procesa el envío de la cotización a Tango.
     * Si el idEstadoCotizacion es aprobado y revisado (completo o con faltantes) y no fue enviado a Tango, se envía.
     *
     * @param array $idCotizacion
     * @param string $idEstadoCotizacion
     *
     * @return bool
     */
    public function processEnviarATango($idCotizacion, $idEstadoCotizacion)
    {
        $table = Table::getInstance('EstadoCotizacion', 'SabullvialTable');
        $table->load($idEstadoCotizacion);

        if (!$table->id || !$table->aprobado) {
            return true;
        }

        $isAprobadoCompleto = (int) $table->revisado == SabullvialTableEstadoCotizacion::ESTADO_REVISION_COMPLETA;
        $isAprobadoConFaltantes = (int) $table->revisado == SabullvialTableEstadoCotizacion::ESTADO_REVISION_COMPLETA_CON_FALTANTES;

        if (!$isAprobadoCompleto && !$isAprobadoConFaltantes) {
            return true;
        }

        /** @var SabullvialTableCotizacion $tableCotizacion */
        $tableCotizacion = Table::getInstance('Cotizacion', 'SabullvialTable');
        $tableCotizacion->load($idCotizacion);

        if ((int) $tableCotizacion->tango_enviar == 1) {
            return true;
        }

        $tableCotizacion->tango_enviar = 1;
        return $tableCotizacion->store();
    }
}

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

/**
 * Clientes Controller
 *
 * @package     Sabullvial.Administrator
 * @subpackage  com__sabullvial
 */
class SabullvialControllerClientes extends JControllerAdmin
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
    public function getModel($name = 'Cliente', $prefix = 'SabullvialModel', $config = ['ignore_request' => true])
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    /**
     * Method to approve a list of items
     *
     * @return  void
     *
     * @since   1.6
     */
    public function aprobado()
    {
        // Check for request forgeries
        $this->checkToken();

        // Get items to publish from the request.
        $cid = (array) $this->input->get('cid', [], 'int');
        $plazo = $this->input->get('modal_approve_plazo', null);
        $monto = (float)$this->input->get('modal_approve_monto', null);
        $task = $this->getTask();
        // Remove zero values resulting from input filter
        $cid = array_filter($cid);

        if (empty($cid)) {
            \JLog::add(\JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), \JLog::WARNING, 'jerror');
        } else {
            // Get the model.
            /** @var SabullvialModelCliente $model */
            $model = $this->getModel();

            // Publish the items.
            try {
                $aprobadoId = JTable::getInstance('EstadoCliente', 'SabullvialTable')->getAprobadoId();

                $model->changeEstado($cid, $aprobadoId, $plazo, $monto);

                $errors = $model->getErrors();

                if ($errors) {
                    \JFactory::getApplication()->enqueueMessage(\JText::plural($this->text_prefix . '_N_ITEMS_FAILED_' . strtoupper($task), count($cid)), 'error');
                } else {
                    $this->processEnviarATango($cid, $task);
                    $ntext = $this->text_prefix . '_N_ITEMS_' . strtoupper($task);
                    $this->setMessage(\JText::plural($ntext, count($cid)));
                }
            } catch (\Exception $e) {
                $this->setMessage($e->getMessage(), 'error');
            }
        }


        $this->setRedirect(\JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
    }

    /**
     * Method to approve a list of items
     *
     * @return  void
     *
     * @since   1.6
     */
    public function rechazado()
    {
        // Check for request forgeries
        $this->checkToken();

        // Get items to publish from the request.
        $cid = (array) $this->input->get('cid', [], 'int');
        $task = $this->getTask();
        // Remove zero values resulting from input filter
        $cid = array_filter($cid);

        if (empty($cid)) {
            \JLog::add(\JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), \JLog::WARNING, 'jerror');
        } else {
            // Get the model.
            /** @var SabullvialModelCliente $model */
            $model = $this->getModel();

            // Publish the items.
            try {
                $tableEstadoCliente = JTable::getInstance('EstadoCliente', 'SabullvialTable');
                $rechazadoId = $tableEstadoCliente->getRechazadoId();

                $model->changeEstado($cid, $rechazadoId);

                $errors = $model->getErrors();

                if ($errors) {
                    \JFactory::getApplication()->enqueueMessage(\JText::plural($this->text_prefix . '_N_ITEMS_FAILED_' . strtoupper($task), count($cid)), 'error');
                } else {
                    $ntext = $this->text_prefix . '_N_ITEMS_' . strtoupper($task);
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
        if ($task != 'aprobado') {
            return;
        }

        foreach ($cid as $id) {
            $table = JTable::getInstance('Cliente', 'SabullvialTable');
            $table->load($id);
            $table->tango_enviar = 1;
            $table->store();
        }
    }

    public function listClientes()
    {
        $app = JFactory::getApplication();
        $app->setHeader('Content-Type', 'application/json');

        /** @var SabullvialModelClientes $model */
        $model = $this->getModel('Clientes', 'SabullvialModel', ['ignore_request' => false]);
        $items = $model->getItems();
        $pagination = $model->getPagination();

        $app->setBody(new JResponseJson([
            'items' => $items,
            'pagination' => $pagination
        ]));
        echo $app->toString(true);
        die();
    }
}

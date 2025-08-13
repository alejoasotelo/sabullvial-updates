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
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Uri\Uri;

/**
 * Tarea Controller
 *
 * @package     Sabullvial.Administrator
 * @subpackage  com__sabullvial
 */
class SabullvialControllerTarea extends FormController
{
    protected $view_list = 'tareas';

    /**
     * Method to check if you can edit an existing record.
     *
     * Extended classes can override this if necessary.
     *
     * @param   array   $data  An array of input data.
     * @param   string  $key   The name of the key for the primary key; default is id.
     *
     * @return  boolean
     *
     * @since   1.6
     */
    protected function allowEdit($data = [], $key = 'id')
    {
        $vendedor = SabullvialHelper::getVendedor();

        if ($vendedor->get('ver.tareas', 0) != SabullvialHelper::VER_NINGUNA) {
            return true;
        }

        return \JFactory::getUser()->authorise('core.edit', $this->option);
    }

    public function reload($key = null, $urlVar = null)
    {
        $app = JFactory::getApplication();
        $subtask = $app->input->getCmd('subtask', '');

        if (!empty($subtask)) {
            $app->setUserState($this->option . '.edit.' . $this->context . '.subtask', $subtask);
        }

        return parent::reload();
    }

    public function saveNota()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->checkToken('get', false)) {
            echo new JsonResponse(null, Text::_('JINVALID_TOKEN'), true);
            die();
        }

        $app = Factory::getApplication();
        $input = $app->input;

        $itemId = $input->getInt('id');
        $body = $input->getString('body');

        $model = $this->getModel();
        $nota = $model->addNota($itemId, $body);

        if (!$nota) {
            $app->setBody(new JsonResponse(null, $model->getError(), true));
            echo $app->toString(true);
            die();
        }

        $nota->createdFormatted = SabullvialHelper::formatToChatDatetime($nota->created);

        $app->setBody(new JsonResponse($nota, null, false));
        echo $app->toString(true);
        die();
    }

    /**
     * Removes an item.
     *
     * @return  void
     *
     * @since   1.6
     */
    public function deleteNota()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->checkToken('get', false)) {
            echo new JsonResponse(null, Text::_('JINVALID_TOKEN'), true);
            die();
        }

        // Get items to remove from the request.
        $cid = (array) $this->input->get('cid', [], 'int');

        // Remove zero values resulting from input filter
        $cid = array_filter($cid);

        if (empty($cid)) {
            \JLog::add(\JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), \JLog::WARNING, 'jerror');
        } else {
            // Get the model.
            $model = ListModel::getInstance('TareaNotas', 'SabullvialModel');

            // Remove the items.
            if ($model->delete($cid)) {
                $this->setMessage(\JText::plural($this->text_prefix . '_N_ITEMS_DELETED', count($cid)));
            } else {
                $this->setMessage($model->getError(), 'error');
            }

            // Invoke the postDelete method to allow for the child class to access the model.
            $this->postDeleteHook($model, $cid);
        }

        $this->setRedirect(\JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
    }

    public function editCotizacion($key = null)
    {
        $this->checkToken();

        $model = $this->getModel();
        $table = $model->getTable();
        $context = "$this->option.edit.$this->context";

        if (empty($key)) {
            $key = $table->getKeyName();
        }

        $recordId = $this->input->getInt($key);

        $table->load($recordId);

        // Clean the session data and redirect.
        $this->releaseEditId($context, $recordId);
        \JFactory::getApplication()->setUserState($context . '.data', null);

        $uri = Uri::getInstance();
        $uri->setVar('layout', null);
        $uri->setVar('view', null);
        $uri->setVar('option', $this->option);
        $uri->setVar('task', 'tarea.edit');
        $uri->setVar('id', $recordId);
        // remove layout

        $url = 'index.php?option=' . $this->option . '&task=cotizacion.edit&id=' . $table->id_cotizacion
            . '&return=' . urlencode(base64_encode($uri->toString()));

        // Redirect to the list screen.
        $this->setRedirect(\JRoute::_($url, false));

        return true;
    }
}

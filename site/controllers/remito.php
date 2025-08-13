<?php
/**
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access to this file
defined('_JEXEC') or exit('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;

/**
 * Remito Controller.
 */
class SabullvialControllerRemito extends FormController
{
    /**
     * Method to save a record.
     *
     * @param string $key    the name of the primary key of the URL variable
     * @param string $urlVar the name of the URL variable if different from the primary key (sometimes required to avoid router collisions)
     *
     * @return bool true if successful, false otherwise
     *
     * @since   1.6
     */
    public function marcarComoEntregado($key = null, $urlVar = 'id')
    {
        // check token
        $this->checkToken();

        $app = Factory::getApplication();
        /** @var SabullvialModelRemito $model */
        $model = $this->getModel('Remito');
        $data = $this->input->post->get('jform', [], 'array');
        $context = "$this->option.edit.$this->context";

        $recordId = $app->input->getCwd('id');

        // Validate the posted data.
        // Sometimes the form needs some posted data, such as for plugins and modules.
        $form = $model->getForm($data, false);

        if (!$form) {
            $app->enqueueMessage($model->getError(), 'error');

            return false;
        }

        // Send an object which can be modified through the plugin event
        $objData = (object) $data;
        $app->triggerEvent(
            'onContentNormaliseRequestData',
            [$this->option.'.'.$this->context, $objData, $form]
        );
        $data = (array) $objData;

        $vendedor = SabullvialHelper::getVendedor();

        if (!$vendedor->get('modificar.remito.estado.qr')) {
            $this->setMessage(JText::_('COM_SABULLVIAL_REMITO_ERROR_NO_PERMISSION'), 'error');

            // Save the data in the session.
            $app->setUserState($context.'.data', $data);

            // Redirect back to the edit screen.
            $this->setRedirect(
                JRoute::_(
                    'index.php?option='.$this->option.'&view='.$this->view_item
                    .$this->getRedirectToItemAppend($recordId, $urlVar),
                    false
                )
            );

            return false;
        }

        // Test whether the data is valid.
        $validData = $model->validate($form, $data);

        // Check for validation errors.
        if (false === $validData) {
            // Get the validation messages.
            $errors = $model->getErrors();

            $captchaInvalidText = JText::sprintf('JLIB_FORM_VALIDATE_FIELD_INVALID', 'Captcha');

            // Push up to three validation messages out to the user.
            for ($i = 0, $n = count($errors); $i < $n && $i < 3; ++$i) {
                if ($errors[$i] instanceof Exception) {
                    $error = $errors[$i]->getMessage();

                    if ($error == $captchaInvalidText) {
                        continue;
                    }

                    $app->enqueueMessage($error, 'warning');
                } else {
                    $app->enqueueMessage($errors[$i], 'warning');
                }
            }

            // Save the data in the session.
            $app->setUserState($context.'.data', $data);

            // Redirect back to the edit screen.
            $this->setRedirect(
                JRoute::_(
                    'index.php?option='.$this->option.'&view='.$this->view_item
                    .$this->getRedirectToItemAppend($recordId, $urlVar),
                    false
                )
            );

            return false;
        }

        /* @var SabullvialTableRemito $table */
        Table::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.'/tables');
        $table = Table::getInstance('RemitoEstado', 'SabullvialTable');
        $table->load(['numero_remito' => $recordId]);

        /** @var SabullvialTableRemitoEstado $table */
        $estadoEntregado = Table::getInstance('EstadoRemito', 'SabullvialTable')->getEntregado();

        $result = false;
        if ($table->id_estadoremito != $estadoEntregado->id) {
            $created = date('Y-m-d H:i:s');

            $file = $this->input->files->get('jform', [], 'array');
            $imageResult = $model->uploadImage($recordId, $file);
            $table->upload_image = $imageResult !== false ? $imageResult : '';

            $table->numero_remito = $recordId;
            $table->id_estadoremito = $estadoEntregado->id;
            $table->delivery_date = $created;
            $result = $table->store();
        }

        // Load the parameters.
        $params = $app->getParams();
        $menuitem = (int) $params->get('redirect_menuitem');

        // Check for redirection after submission when creating a new article only
        if ($menuitem > 0 && $result) {
            $this->setMessage(null);
            $this->setRedirect(JRoute::_('index.php?Itemid='.$menuitem, false));
        }

        if (!$result) {
            // Redirect back to the edit screen.
            $this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
            $this->setMessage($this->getError(), 'error');

            $this->setRedirect(
                JRoute::_(
                    'index.php?option='.$this->option.'&view='.$this->view_item
                    .$this->getRedirectToItemAppend($recordId, $urlVar),
                    false
                )
            );
        }

        return $result;
    }
}

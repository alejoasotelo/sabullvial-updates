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

use Joomla\CMS\MVC\Controller\FormController;

/**
 * Configuracion Controller
 *
 * @package     Sabullvial.Administrator
 * @subpackage  com__sabullvial
 */
class SabullvialControllerConfiguracion extends FormController
{
    protected $view_list = 'configuraciones';


    protected function allowAdd($data = [])
    {
        return false;
    }

    protected function allowSave($data = [], $key = 'id')
    {
        return JFactory::getUser()->authorise('core.edit', $this->option);
    }

    public function save($key = null, $urlVar = null)
    {
        // Check for request forgeries.
        $this->checkToken();

        /** @var SabullvialModelConfiguracion $model */
        $model = $this->getModel();
        $data  = $this->input->post->get('jform', [], 'array');

        // Access check.
        if (!$this->allowSave($data)) {
            $this->setError(\JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
            $this->setMessage($this->getError(), 'error');

            $this->setRedirect(
                JRoute::_(
                    'index.php?option=' . $this->option . '&view=' . $this->view_item
                    . $this->getRedirectToItemAppend(),
                    false
                )
            );

            return false;
        }

        if (!$model->save($data)) {
            $this->setError($model->getError());
            $this->setMessage($this->getError(), 'error');

            $this->setRedirect(
                JRoute::_(
                    'index.php?option=' . $this->option . '&view=' . $this->view_item
                    . $this->getRedirectToItemAppend(),
                    false
                )
            );

            return false;
        }

        $this->setMessage(JText::_('COM_SABULLVIAL_CONFIGURACION_SAVE_SUCCESS'));

        $this->setRedirect(
            JRoute::_(
                'index.php?option=' . $this->option . '&view=' . $this->view_item
                . $this->getRedirectToItemAppend(),
                false
            )
        );

        return true;



        var_dump($data);
        die();
    }
}

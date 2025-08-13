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
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Model\ListModel;

/**
 * TareaNotas Controller
 *
 * @package     Sabullvial.Administrator
 * @subpackage  com__sabullvial
 */
class SabullvialControllerTareaNotas extends AdminController
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
    public function getModel($name = 'TareaNota', $prefix = 'SabullvialModel', $config = ['ignore_request' => true])
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    /**
     * Removes an item.
     *
     * @return  void
     *
     * @since   1.6
     */
    public function deleteAjax()
    {
        header('Content-Type: application/json; charset=utf-8');

        $app = Factory::getApplication();

        if (!$this->checkToken('get', false)) {
            $app->enqueueMessage(Text::_('JINVALID_TOKEN'), 'error');

            $app->setBody(new JsonResponse(null, null, true));
            echo $app->toString(true);
            die();
        }

        $cid = (array) $this->input->get('cid', [], 'int');

        // Remove zero values resulting from input filter
        $cid = array_filter($cid);

        if (empty($cid)) {
            $app->enqueueMessage(Text::_($this->text_prefix . '_NO_ITEM_SELECTED'), 'warning');

            $app->setBody(new JsonResponse(null, null, true));
            echo $app->toString(true);
            die();
        }

        $model = $this->getModel();

        if (!$model->delete($cid)) {
            $app->enqueueMessage($model->getError(), 'error');

            $app->setBody(new JsonResponse(null, null, true));
            echo $app->toString(true);
            die();
        }

        // $app->enqueueMessage(Text::plural($this->text_prefix . '_N_ITEMS_DELETED', count($cid)));

        $app->setBody(new JsonResponse(null, null, false));
        echo $app->toString(true);
        die();
    }
}

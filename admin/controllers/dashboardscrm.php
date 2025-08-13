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

/**
 * DashboardsCrm Controller
 *
 * @package     Sabullvial.Administrator
 * @subpackage  com__sabullvial
 */
class SabullvialControllerDashboardsCrm extends JControllerAdmin
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
    public function getModel($name = 'DashboardCrm', $prefix = 'SabullvialModel', $config = ['ignore_request' => true])
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    /**
     * Guarda el monto actual del presupuesto en la sesión del usuario.
     *
     * @return void
     */
    public function saveCurrentAmount()
    {
        $app = Factory::getApplication();
        $app->setHeader('Content-Type', 'application/json');

        if (!$this->checkToken('get', false)) {
            echo new JsonResponse(null, 'invalid token', true);
            die();
        }

        $key = $this->input->getString('key');
        $value = $this->input->getFloat('value', 0);

        if (empty($key)) {
            $app->setBody(new JsonResponse(null, 'Invalid key', true));
            echo $app->toString(true);
            die();
        }

        $stateKey = 'com_sabullvial.dashboardscrm.' . $key;
        $app->setUserState($stateKey, $value);

        $app->setBody(new JsonResponse(['value' => $value]));
        echo $app->toString(true);
        die();
    }
}

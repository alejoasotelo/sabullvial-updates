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
 * ClientesTango Controller
 *
 * @package     Sabullvial.Administrator
 * @subpackage  com__sabullvial
 */
class SabullvialControllerClientesTango extends JControllerAdmin
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
    public function getModel($name = 'ClienteTango', $prefix = 'SabullvialModel', $config = ['ignore_request' => true])
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    public function listClientes()
    {
        $app = JFactory::getApplication();
        $app->setHeader('Content-Type', 'application/json');

        /** @var SabullvialModelCotizaciones $model */
        $model = $this->getModel('ClientesTango', 'SabullvialModel', ['ignore_request' => false]);
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

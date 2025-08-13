<?php
/**
 * @package     Sabullvial.Administrator
 * @subpackage  com_sabullvial
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * TareasCalendario Controller
 *
 * @package     Sabullvial.Administrator
 * @subpackage  com__sabullvial
 */
class SabullvialControllerTareasCalendario extends JControllerAdmin
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
    public function getModel($name = 'Tareas', $prefix = 'SabullvialModel', $config = ['ignore_request' => true])
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    public function findTareas()
    {
        $app = Factory::getApplication();
        $app->setHeader('Content-Type', 'application/json');

        if (!$this->checkToken('get', false)) {
            $app->setBody(new JResponseJson(null, 'invalid token', true));
            echo $app->toString(true);
            die();
        }

        $start = $app->input->get('start', null); // ISO8601 date strings (like 2013-12-01T00:00:00-05:00)
        $end = $app->input->get('end', null); // ISO8601 date strings (like 2013-12-01T00:00:00-05:00)

        if (!$start || !$end) {
            $app->setBody(new JResponseJson(null, 'invalid params', true));
            echo $app->toString(true);
            die();
        }

        // ISO8601 to Y-m-d H:i:s
        $start = date('Y-m-d H:i:s', strtotime($start));
        $end = date('Y-m-d H:i:s', strtotime($end));

        $model = $this->getModel();
        $model->setState('list.limit', 1000);
        $model->setState('filter.start_date_from', $start);
        $model->setState('filter.start_date_to', $end);
        $tareas = $model->getItems();

        foreach ($tareas as &$tarea) {
            $tarea->content = JLayoutHelper::render('sabullvial.popover.tarea', ['tarea' => $tarea]);
        }

        $app->setBody(new JResponseJson($tareas));
        echo $app->toString(true);
        die();
    }
}

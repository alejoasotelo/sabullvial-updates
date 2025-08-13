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

use Joomla\CMS\MVC\Controller\BaseController;

/**
 * General Controller of Sabullvial component
 *
 * @package     Sabullvial.Administrator
 * @subpackage  com_sabullvial
 * @since       0.0.7
 */
class SabullvialController extends BaseController
{
    /**
     * The default view for the display method.
     *
     * @var string
     * @since 12.2
     */
    protected $default_view = 'cotizaciones';

    public function display($cachable = false, $urlparams = [])
    {
        $viewName = $this->input->get('view', $this->default_view);

        $isViewRemitos = $viewName == 'remito' || $viewName == 'remitos' || $viewName == 'hojasderuta' || $viewName == 'hojaderuta';
        $isViewPuntoDeVenta = $viewName == 'puntosdeventa';
        $isViewTareas = $viewName == 'tareas';
        $isViewReglas = $viewName == 'reglas';
        $isViewCRM = $viewName == 'dashboardscrm';

        if ($isViewRemitos) {
            $vendedor = SabullvialHelper::getVendedor();
            $canDo = JHelperContent::getActions('com_sabullvial', 'cotizacion');
            $isJoomlaAdmin = $canDo->get('core.admin');
            $isBullvialAdmin = $vendedor->get('tipo', '') == 'A';

            if (!$isJoomlaAdmin && !$isBullvialAdmin && !$vendedor->get('administrar.remitos', false)) {
                $this->setError(\JText::_('COM_SABULLVIAL_SIN_PERMISOS_' . strtoupper($viewName)));
                $this->setMessage($this->getError(), 'error');

                $option = $this->input->get('option');
                $this->setRedirect(\JRoute::_('index.php?option=' . $option, false));

                return false;
            }
        }

        if ($isViewPuntoDeVenta && SabullvialHelper::isUserLogistica()) {
            $this->setError(\JText::_('COM_SABULLVIAL_SIN_PERMISOS_' . strtoupper($viewName)));
            $this->setMessage($this->getError(), 'error');

            $option = $this->input->get('option');
            $this->setRedirect(\JRoute::_('index.php?option=' . $option, false));

            return false;
        }

        if ($isViewReglas) {
            $vendedor = SabullvialHelper::getVendedor();
            $isBullvialAdmin = $vendedor->get('tipo', '') == 'A';
            $verReglas = $vendedor->get('ver.reglas', false);

            if (!$isBullvialAdmin && !$verReglas) {
                $this->setError(\JText::_('COM_SABULLVIAL_SIN_PERMISOS_' . strtoupper($viewName)));
                $this->setMessage($this->getError(), 'error');

                $option = $this->input->get('option');
                $this->setRedirect(\JRoute::_('index.php?option=' . $option, false));

                return false;
            }
        }

        if ($isViewTareas || $isViewCRM) {
            $vendedor = SabullvialHelper::getVendedor();
            $isBullvialAdmin = $vendedor->get('tipo', '') == 'A';
            $verTareas = $vendedor->get('ver.tareas', 0) != SabullvialHelper::VER_NINGUNA;
            $verCRM = $vendedor->get('ver.crm', 0) != SabullvialHelper::VER_NINGUNA;

            $canViewTareas = $isViewTareas && $verTareas;
            $canViewCRM = $isViewCRM && $verCRM;

            if (!$isBullvialAdmin && !$canViewTareas && !$canViewCRM) {
                $this->setError(\JText::_('COM_SABULLVIAL_SIN_PERMISOS_' . strtoupper($viewName)));
                $this->setMessage($this->getError(), 'error');

                $option = $this->input->get('option');
                $this->setRedirect(\JRoute::_('index.php?option=' . $option, false));

                return false;
            }
        }

        $layout = $this->input->get('layout');
        $id     = $this->input->getInt('id');
        $views = [
            'cotizacion' => 'cotizaciones',
            'cliente' => 'clientes',
            'hojaderuta' => 'hojasderuta',
            'vehiculo' => 'vehiculos',
            'chofer' => 'choferes',
            'regla' => 'reglas',
            'tarea' => 'tareas'
        ];
        $viewSingular = array_keys($views);

        // Check for edit form.
        if (in_array($viewName, $viewSingular) && $layout == 'edit' && !$this->checkEditId('com_sabullvial.edit.' . $viewName, $id)) {
            // Somehow the person just went to the form - we don't allow that.
            $this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
            $this->setMessage($this->getError(), 'error');
            $this->setRedirect(JRoute::_('index.php?option=com_sabullvial&view=' . $views[$viewName], false));

            return false;
        }

        return parent::display($cachable, $urlparams);
    }
}

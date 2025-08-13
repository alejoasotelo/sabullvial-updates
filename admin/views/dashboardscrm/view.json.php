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
 * PuntosDeVenta View
 *
 * @since  0.0.1
 */
class SabullvialViewDashboardsCrm extends JViewLegacy
{
    /**
     * Display the Productos view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        $this->state			= $this->get('State');
        $this->filterForm    	= $this->get('FilterForm');
        $this->activeFilters 	= $this->get('ActiveFilters');

        $this->cotizaciones = $this->get('Cotizaciones');
        $this->cotizacionesRealizadas = $this->get('CotizacionesRealizadas');
        $this->cotizacionesVendidas = $this->get('CotizacionesVendidas');
        $this->cotizacionesRechazadas = $this->get('CotizacionesRechazadas');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            echo new JsonResponse(null, $errors, false);
            die();
        }

        $data = [
            // 'items' => $this->items,
            // 'pagination' => $this->pagination,
            'vendedor' => SabullvialHelper::getVendedor(),
            'state' => $this->state,
            'filterForm' => $this->filterForm,
            'activeFilters' => $this->activeFilters,
            'cotizaciones' => [
                'todas' => $this->cotizaciones,
                'realizadas' => $this->cotizacionesRealizadas,
                'vendidas' => $this->cotizacionesVendidas,
                'rechazadas' => $this->cotizacionesRechazadas
            ],
        ];

        Factory::getApplication()->setHeader('Content-Type', 'application/json');
        echo new JsonResponse($data);
    }
}

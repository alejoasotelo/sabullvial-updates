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

use Joomla\Registry\Registry;
use Joomla\CMS\Table\Table;
use Joomla\Utilities\ArrayHelper;

JLoader::register('SabullvialTableSitCondicionesVenta', JPATH_COMPONENT . '/tables/sitcondicionesventa.php');

/**
 * HelloWorld component helper.
 *
 * @param   string  $submenu  The name of the active view.
 *
 * @return  void
 *
 * @since   1.6
 */
class SabullvialCotizacionesHelper
{
    public const CONSUMIDOR_FINAL  = '000000';
    /**
     * Parámetros del componente
     *
     * @var Registry
     */
    protected $params;

    /**
     * Undocumented function
     *
     * @param Registry $cmpParams Parámetros del componente
     */
    public function __construct($cmpParams)
    {
        $this->params = $cmpParams;
    }

    /**
     * Función que procesa el envío a facturación con las reglas de bullvial
     * y devuelve el nuevo estado de la cotización
     *
     * Si el cliente es consumidor final, devuelve el estado pendiente
     * Si el cliente es una empresa:
     * - Si la condicion de venta del cliente no es transferencia, devuelve el estado pendiente
     * - Si la condicion de venta del cliente es transferencia y la de la cotizacion es transferencia, devuelve el estado pendiente
     * - Si la condicion de venta del cliente es transferencia y la de la cotizacion  no es transferencia:
     *     - Si la config. rechazar_por_condicion_de_venta_distina_a_transferencia esta activa, devuelve el estado rechazado
     *    - Si la config. rechazar_por_condicion_de_venta_distina_a_transferencia esta desactiva, devuelve el estado pendiente
     *
     * @param string $idCliente
     * @return integer
     */
    public function getNewEstadoCotizacionFromSendToFacturacion($idCliente, $idCondicionVenta)
    {
        /** @var SabullvialTableEstadoCotizacion $estadoCotizacion */
        $estadoCotizacion = Table::getInstance('EstadoCotizacion', 'SabullvialTable');

        // si es consumidor final salgo
        if ($idCliente == self::CONSUMIDOR_FINAL || empty($idCliente)) {
            return $estadoCotizacion->getEstadoPendienteId();
        }

        $cliente = JTable::getInstance('SitClientes', 'SabullvialTable');
        $cliente->loadByCodCliente($idCliente);
        $clienteHasTransferencia = $cliente->hasCondicionDeVenta(SabullvialTableSitCondicionesVenta::CONDICION_TRANSFERENCIA);

        if (!$clienteHasTransferencia) {
            return $estadoCotizacion->getEstadoPendienteId();
        }

        $cotizacionHasTransferencia = $idCondicionVenta == SabullvialTableSitCondicionesVenta::CONDICION_TRANSFERENCIA;

        if ($cotizacionHasTransferencia) {
            return $estadoCotizacion->getEstadoPendienteId();
        }

        $rechazarPorCambiarTransferencia = (bool)$this->params->get('cotizacion_rechazar_por_condicion_de_venta_distina_a_transferencia', false);

        if (!$rechazarPorCambiarTransferencia) {
            return $estadoCotizacion->getEstadoPendienteId();
        }

        return $estadoCotizacion->getEstadoRechazadoPorFormaDePagoId();
    }

    /**
     * Devuelve el IVA según los items de detalle
     *
     * @param bool $hasIVA
     * @param array $itemsDetalle
     * @return float
     */
    public static function calcIva($hasIVA, $itemsDetalle)
    {
        if ($hasIVA) {
            return 0;
        }

        return self::calcSubtotal($itemsDetalle) * 0.21;
    }

    /**
     * Devuelve el IIBB según los items de detalle
     *
     * @param float $porcentajeIIBB
     * @param array $itemsDetalle
     * @param bool $hasIVA
     * @return float
     */
    public static function calcIIBB($porcentajeIIBB, $itemsDetalle, $hasIVA)
    {
        $iibb = 0;

        if ($porcentajeIIBB > 0) {
            $porcentajeIIBB = (float)$porcentajeIIBB;
            $subtotal = self::calcSubtotal($itemsDetalle);
            $iibb = $hasIVA ? $subtotal / 1.21 : $subtotal;
            $iibb *= ($porcentajeIIBB / 100); // 0.1%
        }

        return $iibb;
    }

    /**
     * Devuelve el subtotal según los items de detalle
     *
     * @param array $itemsDetalle
     * @return float
     */
    public static function calcSubtotal($itemsDetalle)
    {
        $subtotal = 0;
        foreach ($itemsDetalle as $item) {
            $subtotal += ((float)$item['precio'] * (int)$item['cantidad']) * (1 - ((float)$item['descuento'] / 100));
        }

        return $subtotal;
    }

}

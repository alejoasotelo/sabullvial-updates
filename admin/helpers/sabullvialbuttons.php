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

/**
 * HelloWorld component helper.
 *
 * @param   string  $submenu  The name of the active view.
 *
 * @return  void
 *
 * @since   1.6
 */
abstract class SabullvialButtonsHelper extends JHelperContent
{
    protected static $cache = [];

    public static function canDuplicate($idEstadoCotizacion)
    {
        $cacheId = 'canDuplicate-' . $idEstadoCotizacion;

        if (isset(self::$cache[$cacheId])) {
            return self::$cache[$cacheId];
        }

        /** @var SabullvialTableEstadoCotizacion $estadoCotizacion */
        $estadoCotizacion = Table::getInstance('EstadoCotizacion', 'SabullvialTable');
        $idsEstadosAprobados = $estadoCotizacion->getEstadosAprobadosIds();

        self::$cache[$cacheId] = in_array($idEstadoCotizacion, $idsEstadosAprobados);
        return self::$cache[$cacheId];
    }

    public static function canEnviarAFacturacion($idEstadoCotizacion)
    {
        $cacheId = 'canEnviarAFacturacion-' . $idEstadoCotizacion;

        if (!isset(self::$cache[$cacheId])) {
            $isUserAdministrador = SabullvialHelper::isUserAdministrador();

            $vendedor = SabullvialHelper::getVendedor();
            $tipo = $vendedor->get('tipo', SabullvialHelper::USUARIO_TIPO_VENDEDOR);
            $isVendedor = $tipo == SabullvialHelper::USUARIO_TIPO_VENDEDOR;
            $isAdministrador = $tipo == SabullvialHelper::USUARIO_TIPO_ADMINISTRADOR;

            $canEnviarAFacturacion = $vendedor->get('enviarAFacturacion', true);

            if (!self::isEstadoCreado($idEstadoCotizacion)) {
                self::$cache[$cacheId] = false;
                return self::$cache[$cacheId];
            }

            if ($isUserAdministrador || $isAdministrador) {
                self::$cache[$cacheId] = true;
                return self::$cache[$cacheId];
            }

            if ($vendedor->get('esRevendedor', false)) {
                self::$cache[$cacheId] = false;
                return self::$cache[$cacheId];
            }

            if ($isVendedor && $canEnviarAFacturacion) {
                self::$cache[$cacheId] = true;
                return self::$cache[$cacheId];
            }

            self::$cache[$cacheId] = false;
        }

        return self::$cache[$cacheId];
    }

    public static function canRevisar($cotizacion)
    {
        $cacheId = 'canRevisar-' . $cotizacion->id_estado_tango;
        $cacheId .= '-' . $cotizacion->estadocotizacion_rechazado;
        $cacheId .= '-' . $cotizacion->estadocotizacion_cancelado;
        $cacheId .= '-' . $cotizacion->estadocotizacion_pendiente;

        if (!isset(self::$cache[$cacheId])) {
            $userAllowed = SabullvialHelper::isUserAdministrador() || SabullvialHelper::isUserLogistica();
            $hasCanceled = $cotizacion->estadocotizacion_rechazado || $cotizacion->estadocotizacion_cancelado;

            $estadosTango = [SabullvialTableCotizacion::ESTADO_TANGO_SRL, SabullvialTableCotizacion::ESTADO_TANGO_PRUEBA];
            $hasEstadoTango = in_array($cotizacion->id_estado_tango, $estadosTango);

            if (!$userAllowed || $hasCanceled || !$hasEstadoTango) {
                self::$cache[$cacheId] = false;
                return self::$cache[$cacheId];
            }

            self::$cache[$cacheId] = $cotizacion->estadocotizacion_pendiente;
        }

        return self::$cache[$cacheId];
    }

    public static function canVerRevision($cotizacion)
    {
        $cacheId = 'canVerRevision-' . $cotizacion->id_estado_tango;
        $cacheId .= '-' . $cotizacion->estadocotizacion_rechazado;
        $cacheId .= '-' . $cotizacion->estadocotizacion_cancelado;
        $cacheId .= '-' . $cotizacion->id_estadocotizacion;
        $cacheId .= '-' . $cotizacion->is_reviewed;

        if (!isset(self::$cache[$cacheId])) {
            $params = self::getComponentParams();
            $userAllowed = SabullvialHelper::isUserAdministrador() || SabullvialHelper::isUserLogistica();
            $estadosTango = [SabullvialTableCotizacion::ESTADO_TANGO_SRL, SabullvialTableCotizacion::ESTADO_TANGO_PRUEBA];
            $hasEstadoTango = in_array($cotizacion->id_estado_tango, $estadosTango);
            $hasCanceled = $cotizacion->estadocotizacion_rechazado || $cotizacion->estadocotizacion_cancelado;

            if (!$userAllowed || $hasCanceled || !$hasEstadoTango) {
                self::$cache[$cacheId] = false;
                return self::$cache[$cacheId];
            }

            if ($cotizacion->id_estadocotizacion == $params->get('cotizacion_estado_aprobado_automatico')) {
                self::$cache[$cacheId] = false;
                return self::$cache[$cacheId];
            }

            self::$cache[$cacheId] =  $cotizacion->is_reviewed;
        }

        return self::$cache[$cacheId];
    }

    public static function canVer($idEstadoCotizacion)
    {
        $cacheId = 'canVer-' . $idEstadoCotizacion;

        if (!isset(self::$cache[$cacheId])) {
            $params = self::getComponentParams();
            $isUserAdministrador = SabullvialHelper::isUserAdministrador();
            $isUserLogistica = SabullvialHelper::isUserLogistica();

            $isAllow = $isUserAdministrador || $isUserLogistica;
            $isAprobadoAutomatico = $idEstadoCotizacion == $params->get('cotizacion_estado_aprobado_automatico');

            if (!$isAllow || !$isAprobadoAutomatico) {
                self::$cache[$cacheId] = false;
                return self::$cache[$cacheId];
            }

            self::$cache[$cacheId] = true;
        }

        return self::$cache[$cacheId];
    }

    public static function canAprobarORechazar($idEstadoCotizacion, $idUserCotizacion)
    {
        $cacheId = 'canAprobarORechazar-' . $idEstadoCotizacion . '-' . $idUserCotizacion;

        if (isset(self::$cache[$cacheId])) {
            return self::$cache[$cacheId];
        }

        $isUserAdministrador = SabullvialHelper::isUserAdministrador();
        $isUserLogistica = SabullvialHelper::isUserLogistica();

        if (!$isUserAdministrador && !$isUserLogistica) {
            self::$cache[$cacheId] = false;
            return self::$cache[$cacheId];
        }

        $vendedor = SabullvialHelper::getVendedor();
        $params = self::getComponentParams();

        $hasEstadosToAprobarOrRechazar = $idEstadoCotizacion != $params->get('cotizacion_estado_creado')
            && $idEstadoCotizacion != $params->get('cotizacion_estado_aprobado_automatico')
            && $idEstadoCotizacion != $params->get('orden_de_trabajo_estado_creado');

        /** @var array<int> */
        $aprobarPresupuestosVendedores = $vendedor->get('aprobar.presupuestosVendedores', []);

        if (in_array($idUserCotizacion, $aprobarPresupuestosVendedores) && $hasEstadosToAprobarOrRechazar) {
            self::$cache[$cacheId] = true;
            return self::$cache[$cacheId];
        }

        if (!$hasEstadosToAprobarOrRechazar) {
            self::$cache[$cacheId] = false;
            return self::$cache[$cacheId];
        }

        $canAprobarPresupuestos = $vendedor->get('aprobar.presupuestos', false);

        if (!$isUserAdministrador && !$canAprobarPresupuestos) {
            self::$cache[$cacheId] = false;
            return self::$cache[$cacheId];
        }

        self::$cache[$cacheId] = true;

        return self::$cache[$cacheId];
    }

    /**
     * Undocumented function
     *
     * @param bool $esperarPagos
     * @return boolean
     */
    public static function canAprobarPago($esperarPagos, $isPagado)
    {
        $cacheId = 'canAprobarPago-' . (int) $esperarPagos . '-' . (int) $isPagado;

        if (isset(self::$cache[$cacheId])) {
            return self::$cache[$cacheId];
        }

        if (!$esperarPagos || !$isPagado) {
            self::$cache[$cacheId] = false;
            return self::$cache[$cacheId];
        }

        $vendedor = SabullvialHelper::getVendedor();
        $canAprobarPago = $vendedor->get('aprobar.pagos', false);

        if (!$canAprobarPago) {
            self::$cache[$cacheId] = false;
            return self::$cache[$cacheId];
        }

        self::$cache[$cacheId] = true;

        return self::$cache[$cacheId];
    }

    public static function canCancelar($idEstadoCotizacion)
    {
        $cacheId = 'canCancelar-' . $idEstadoCotizacion;

        if (isset(self::$cache[$cacheId])) {
            return self::$cache[$cacheId];
        }

        if (!self::isEstadoCreado($idEstadoCotizacion)) {
            self::$cache[$cacheId] = false;
            return self::$cache[$cacheId];
        }

        $vendedor = SabullvialHelper::getVendedor();
        $canCancelar = $vendedor->get('cancelar.cotizacion', true);

        self::$cache[$cacheId] = $canCancelar;

        return self::$cache[$cacheId];
    }

    public static function canAprobarORechazarCliente($idEstadoCliente)
    {
        $cacheId = 'canAprobarORechazarCliente-' . $idEstadoCliente;

        if (!isset(self::$cache[$cacheId])) {
            $vendedor = SabullvialHelper::getVendedor();

            $isAdministrador = $vendedor->get('tipo') == SabullvialHelper::USUARIO_TIPO_ADMINISTRADOR;
            $canAprobarClientes = SabullvialHelper::getVendedor()->get('aprobar.clientes', false);

            $tableEstado = JTable::getInstance('EstadoCliente', 'SabullvialTable');
            $tableEstado->load($idEstadoCliente);

            if (!$isAdministrador || !$canAprobarClientes || !$tableEstado->pendiente) {
                self::$cache[$cacheId] = false;
                return self::$cache[$cacheId];
            }

            self::$cache[$cacheId] = true;
        }

        return self::$cache[$cacheId];
    }

    protected static function getComponentParams()
    {
        $cacheId = 'getComponentParams';

        if (!isset(self::$cache[$cacheId])) {
            self::$cache[$cacheId] = JComponentHelper::getParams('com_sabullvial');
        }

        return self::$cache[$cacheId];
    }

    protected static function isEstadoCreado($idEstadoCotizacion)
    {
        $cacheId = 'isEstadoCreado-' . $idEstadoCotizacion;

        if (isset(self::$cache[$cacheId])) {
            return self::$cache[$cacheId];
        }

        $params = self::getComponentParams();

        $idsEstadosCotizacionCreado = [
            $params->get('cotizacion_estado_creado'),
            $params->get('orden_de_trabajo_estado_creado')
        ];

        self::$cache[$cacheId] = in_array($idEstadoCotizacion, $idsEstadosCotizacionCreado);

        return self::$cache[$cacheId];
    }
}

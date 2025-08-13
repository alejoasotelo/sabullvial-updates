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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

JLoader::register('SabullvialTableXXXConfig', JPATH_ADMINISTRATOR . '/components/com_sabullvial/tables/xxxconfig.php');

/**
 * HelloWorld component helper.
 *
 * @param   string  $submenu  The name of the active view.
 *
 * @return  void
 *
 * @since   1.6
 */
abstract class SabullvialHelper extends JHelperContent
{
    protected static $cache = [];

    public const LISTAS_DOLAR = ['l16', 'l17', '7', '10'];
    public const LISTAS_SIN_IVA = ['l2', 'l3', 'l4', 'l5', 'l12', 'l13', 'l14', 'l15', 'l16', 'l17'];
    public const LISTA_PRECIOS = [
        'l1' => 'c/IVA',
        'l2' => 's/IVA',
        'l3' => 'Reventa+IVA 30D',
        'l4' => 'Mayorista',
        'l5' => 'Empresas+IVA 30D',
        'l6' => 'Contado',
        'l8' => '6 cuotas sin interés',
        'l9' => 'MercadoLibre',
        'l11' => 'MercadoLibre Premium',

        'l12' => 'Reventa+IVA 60D',
        'l13' => 'Empresa+IVA 60D',
        'l14' => 'Empresa+IVA 75D',
        'l15' => 'Reventa+IVA 75D',
        'l16' => 'Reventa+IVA DOLAR',
        'l17' => 'Empresa+IVA DOLAR'
    ];
    public const LISTA_CONSUMIDOR_FINAL = 2;

    public const IVA_21 = 21;

    public const CLASIFICACION_PRODUCTOS_TODOS = 'TODOS';

    public const USUARIO_TIPO_ADMINISTRADOR = 'A';
    public const USUARIO_TIPO_VENDEDOR = 'V';

    public const BORRAR_TODOS = 1;
    public const BORRAR_PROPIOS = 2;

    public const VER_NINGUNA = 0;
    public const VER_TODAS = 1;
    public const VER_PROPIAS = 2;

    public const TANGO_FECHA_SINCRONIZACION_NULLS = [null, '0000-00-00 00:00:00', '1800-01-01 00:00:00'];

    /**
     * Configure the Linkbar.
     *
     * @return Bool
     */
    public static function addSubmenu($submenu)
    {
        $canDo = JHelperContent::getActions('com_sabullvial');
        $vendedor = self::getVendedor();

        $isRevendedor = $vendedor->get('esRevendedor', false);

        if (!self::isUserLogistica()) {
            JHtmlSidebar::addEntry(
                JText::_('COM_SABULLVIAL_PUNTOS_DE_VENTA_SUBMENU'),
                'index.php?option=com_sabullvial&view=puntosdeventa',
                $submenu == 'puntosdeventa'
            );
        }

        JHtmlSidebar::addEntry(
            JText::_('COM_SABULLVIAL_COTIZACIONES_SUBMENU'),
            'index.php?option=com_sabullvial&view=cotizaciones',
            $submenu == 'cotizaciones'
        );

        if (!$isRevendedor) {
            JHtmlSidebar::addEntry(
                JText::_('COM_SABULLVIAL_PRODUCTOS_SUBMENU'),
                'index.php?option=com_sabullvial&view=productos',
                $submenu == 'productos'
            );
        }

        JHtmlSidebar::addEntry(
            JText::_('COM_SABULLVIAL_CLIENTES_TANGO_SUBMENU'),
            'index.php?option=com_sabullvial&view=clientestango',
            $submenu == 'clientestango'
        );

        if (!$isRevendedor) {
            JHtmlSidebar::addEntry(
                JText::_('COM_SABULLVIAL_CLIENTES_SUBMENU'),
                'index.php?option=com_sabullvial&view=clientes',
                $submenu == 'clientes'
            );
        }

        if ($canDo->get('core.admin') || $vendedor->get('administrar.remitos', false)) {
            JHtmlSidebar::addEntry(
                JText::_('COM_SABULLVIAL_HOJAS_DE_RUTA_SUBMENU'),
                'index.php?option=com_sabullvial&view=hojasderuta',
                $submenu == 'hojasderuta'
            );

            JHtmlSidebar::addEntry(
                JText::_('COM_SABULLVIAL_REMITOS_SUBMENU'),
                'index.php?option=com_sabullvial&view=remitos',
                $submenu == 'remitos'
            );
        }

        if ($canDo->get('core.admin') || self::isUserAdministrador()) {
            JHtmlSidebar::addEntry(
                JText::_('COM_SABULLVIAL_VEHICULOS_SUBMENU'),
                'index.php?option=com_sabullvial&view=vehiculos',
                $submenu == 'vehiculos'
            );

            JHtmlSidebar::addEntry(
                JText::_('COM_SABULLVIAL_CHOFERES_SUBMENU'),
                'index.php?option=com_sabullvial&view=choferes',
                $submenu == 'choferes'
            );
        }

        if ($canDo->get('core.admin')) {
            JHtmlSidebar::addEntry(
                JText::_('COM_SABULLVIAL_HOJA_DE_RUTA_REMITOS_SUBMENU'),
                'index.php?option=com_sabullvial&view=hojaderutaremitos',
                $submenu == 'hojaderutaremitos'
            );

            JHtmlSidebar::addEntry(
                JText::_('COM_SABULLVIAL_COTIZACION_DETALLES_SUBMENU'),
                'index.php?option=com_sabullvial&view=cotizaciondetalles',
                $submenu == 'cotizaciondetalles'
            );

            JHtmlSidebar::addEntry(
                JText::_('COM_SABULLVIAL_REVISION_DETALLES_SUBMENU'),
                'index.php?option=com_sabullvial&view=revisiondetalles',
                $submenu == 'revisiondetalles'
            );

            JHtmlSidebar::addEntry(
                JText::_('COM_SABULLVIAL_REMITOS_ESTADO_SUBMENU'),
                'index.php?option=com_sabullvial&view=remitosestado',
                $submenu == 'remitosestado'
            );

            JHtmlSidebar::addEntry(
                JText::_('COM_SABULLVIAL_COTIZACION_HISTORICOS_SUBMENU'),
                'index.php?option=com_sabullvial&view=cotizacionhistoricos',
                $submenu == 'cotizacionhistoricos'
            );

            JHtmlSidebar::addEntry(
                JText::_('COM_SABULLVIAL_COTIZACION_TANGO_HISTORICOS_SUBMENU'),
                'index.php?option=com_sabullvial&view=cotizaciontangohistoricos',
                $submenu == 'cotizaciontangohistoricos'
            );

            JHtmlSidebar::addEntry(
                JText::_('COM_SABULLVIAL_REMITO_HISTORICOS_SUBMENU'),
                'index.php?option=com_sabullvial&view=remitohistoricos',
                $submenu == 'remitohistoricos'
            );

            JHtmlSidebar::addEntry(
                JText::_('COM_SABULLVIAL_COTIZACION_PAGOS_HISTORICOS_SUBMENU'),
                'index.php?option=com_sabullvial&view=cotizacionpagoshistoricos',
                $submenu == 'cotizacionpagoshistoricos'
            );

            JHtmlSidebar::addEntry(
                JText::_('COM_SABULLVIAL_ESTADOS_COTIZACION_SUBMENU'),
                'index.php?option=com_sabullvial&view=estadoscotizacion',
                $submenu == 'estadoscotizacion'
            );

            JHtmlSidebar::addEntry(
                JText::_('COM_SABULLVIAL_ESTADOS_COTIZACION_PAGOS_SUBMENU'),
                'index.php?option=com_sabullvial&view=estadoscotizacionpagos',
                $submenu == 'estadoscotizacionpagos'
            );

            JHtmlSidebar::addEntry(
                JText::_('COM_SABULLVIAL_ESTADOS_REMITO_SUBMENU'),
                'index.php?option=com_sabullvial&view=estadosremito',
                $submenu == 'estadosremito'
            );

            JHtmlSidebar::addEntry(
                JText::_('COM_SABULLVIAL_ESTADOS_CLIENTE_SUBMENU'),
                'index.php?option=com_sabullvial&view=estadoscliente',
                $submenu == 'estadoscliente'
            );

            JHtmlSidebar::addEntry(
                JText::_('COM_SABULLVIAL_FORMAS_PAGO_SUBMENU'),
                'index.php?option=com_sabullvial&view=formaspago',
                $submenu == 'formaspago'
            );
        }

        if ($vendedor->get('ver.reglas', false) || $canDo->get('core.admin') || self::isUserAdministrador()) {
            JHtmlSidebar::addEntry(
                JText::_('COM_SABULLVIAL_REGLAS_SUBMENU'),
                'index.php?option=com_sabullvial&view=reglas',
                $submenu == 'reglas'
            );
        }

        if ($vendedor->get('ver.tareas', 0) != SabullvialHelper::VER_NINGUNA || $canDo->get('core.admin') || self::isUserAdministrador()) {
            JHtmlSidebar::addEntry(
                JText::_('COM_SABULLVIAL_TAREAS_SUBMENU'),
                'index.php?option=com_sabullvial&view=tareas',
                $submenu == 'tareas'
            );
        }

        if ($vendedor->get('ver.tareasCalendario', 0) != SabullvialHelper::VER_NINGUNA || $canDo->get('core.admin') || self::isUserAdministrador()) {
            JHtmlSidebar::addEntry(
                JText::_('COM_SABULLVIAL_TAREAS_CALENDARIO_SUBMENU'),
                'index.php?option=com_sabullvial&view=tareascalendario',
                $submenu == 'tareascalendario'
            );
        }

        if ($vendedor->get('ver.crm', 0) != SabullvialHelper::VER_NINGUNA || $canDo->get('core.admin') || self::isUserAdministrador()) {
            JHtmlSidebar::addEntry(
                JText::_('COM_SABULLVIAL_DASHBOARD_CRM_SUBMENU'),
                'index.php?option=com_sabullvial&view=dashboardscrm',
                $submenu == 'dashboardscrm'
            );
        }

        if ($canDo->get('core.admin') || self::isUserAdministrador()) {
            JHtmlSidebar::addEntry(
                JText::_('COM_SABULLVIAL_DEPOSITOS_SUBMENU'),
                'index.php?option=com_sabullvial&view=depositos',
                $submenu == 'depositos'
            );

            JHtmlSidebar::addEntry(
                JText::_('COM_SABULLVIAL_VENDEDORES_SUBMENU'),
                'index.php?option=com_sabullvial&view=vendedores',
                $submenu == 'vendedores'
            );

            JHtmlSidebar::addEntry(
                JText::_('COM_SABULLVIAL_TAREAS_USUARIOS_SUBMENU'),
                'index.php?option=com_sabullvial&view=tareasusuarios',
                $submenu == 'tareasusuarios'
            );

            JHtmlSidebar::addEntry(
                JText::_('COM_SABULLVIAL_CONFIGURACION_SUBMENU'),
                'index.php?option=com_sabullvial&view=configuracion',
                $submenu == 'configuracion'
            );
        }

        // JHtmlSidebar::addEntry(
        // 	JText::_('COM_SABULLVIAL_CLIENTE_FORMAS_PAGO_SUBMENU'),
        // 	'index.php?option=com_sabullvial&view=clienteformaspago',
        // 	$submenu == 'clienteformaspago'
        // );
        ###__replace__###
    }

    /**
     * Undocumented function
     *
     * @param string $keys
     * @param mixed $value
     * @return mixed
     */
    public static function getConfig($key, $defValue = null)
    {
        $cacheKey = 'config-' . $key;

        if (!isset(self::$cache[$cacheKey])) {
            /** @var SabullvialTableXXXConfig $config */
            self::$cache[$cacheKey] = SabullvialTableXXXConfig::getValue($key, $defValue);
        }

        return self::$cache[$cacheKey] ?: $defValue;
    }

    /**
     * Undocumented function
     *
     * @param array $keys
     * @param array $defValues
     * @return array<string,mixed>
     */
    public static function getMultiConfig($keys, $defValues = null)
    {
        sort($keys);
        $cacheKey = 'config-' . implode('-', $keys);

        $defValues = is_null($defValues) ? array_fill(0, count($keys), null) : $defValues;

        if (!isset(self::$cache[$cacheKey])) {
            /** @var SabullvialTableXXXConfig $config */
            self::$cache[$cacheKey] = SabullvialTableXXXConfig::getValues($keys, $defValues);

            // Lleno los valores individuales
            foreach (self::$cache[$cacheKey] as $key => $value) {
                $cacheIndividualKey = 'config-' . $key;
                if (!isset(self::$cache[$cacheIndividualKey])) {
                    self::$cache[$cacheIndividualKey] = $value;
                }
            }
        }

        return self::$cache[$cacheKey];
    }

    public static function getVendedor($userId = null)
    {
        $cacheKey = 'getVendedor-' . (string) $userId;

        if (!isset(self::$cache[$cacheKey])) {
            $fields = self::getUserFields($userId);

            if (!self::hasFieldsVendedor($fields)) {
                return false;
            }

            $vendedor = new Registry();

            $condicionVenta = $fields['condicion-de-venta']->rawvalue;
            if (is_string($condicionVenta)) {
                $condicionVenta = empty($condicionVenta) ? [] : [$condicionVenta];
            }

            $clasificacionProductos = $fields['ver-productos']->rawvalue;
            if (is_string($clasificacionProductos)) {
                $clasificacionProductos = empty($clasificacionProductos) ? [] : [$clasificacionProductos];
            }

            $aprobarPresupuestosDeVendedores = $fields['aprobar-presupuestos-vendedores-joomla']->rawvalue;
            if (is_string($aprobarPresupuestosDeVendedores)) {
                $aprobarPresupuestosDeVendedores = empty($aprobarPresupuestosDeVendedores) ? [] : [$aprobarPresupuestosDeVendedores];
            }

            $aprobarPresupuestosDeVendedores = Joomla\Utilities\ArrayHelper::toInteger($aprobarPresupuestosDeVendedores);

            $clienteRevendedor = trim($fields['cliente-revendedor']->rawvalue);
            $vendedor->set('clienteRevendedor', $clienteRevendedor); // COD_CLIENT: string del Cliente de Tango
            $vendedor->set('esRevendedor', !empty($clienteRevendedor));
            $vendedor->set('codigo', trim($fields['codigo-vendedor']->rawvalue));
            $vendedor->set('condicionesDeVenta', count($condicionVenta) ? trim(implode(',', $condicionVenta)) : []);
            $vendedor->set('ver.stockReal', (int)$fields['ver-stock-real']->rawvalue == 1);
            $vendedor->set('ver.presupuestos', (int)$fields['ver-todos-los-presupuestos']->rawvalue == 1);
            $vendedor->set('ver.todosLosClientes', (int)$fields['ver-todos-los-clientes']->rawvalue == 1);
            $vendedor->set('ver.reglas', (int)$fields['ver-reglas']->rawvalue == 1);
            $vendedor->set('ver.tareas', (int)$fields['ver-tareas']->rawvalue);
            $vendedor->set('ver.tareasCalendario', (int)$fields['ver-tareas-calendario']->rawvalue);
            $vendedor->set('ver.productos', count($clasificacionProductos) ? $clasificacionProductos : []);
            $vendedor->set('ver.crm', (int)$fields['ver-crm']->rawvalue);
            $vendedor->set('aprobar.clientes', (int)$fields['aprobar-clientes']->rawvalue == 1);
            $vendedor->set('aprobar.presupuestos', (int)$fields['aprobar-presupuestos']->rawvalue == 1);
            $vendedor->set('aprobar.presupuestosVendedores', $aprobarPresupuestosDeVendedores);
            $vendedor->set('aprobar.presupuestosAutomaticamente', (int)$fields['aprobar-presupuestos-automaticamente']->rawvalue == 1);
            $vendedor->set('aprobar.pagos', (int)$fields['aprobar-pagos']->rawvalue == 1);
            $vendedor->set('modificar.precios', (int)$fields['modificar-precios']->rawvalue == 1);
            $vendedor->set('modificar.descripcion', (int)$fields['modificar-descripcion']->rawvalue == 1);
            $vendedor->set('administrar.remitos', (int)$fields['administrar-remitos']->rawvalue == 1);
            $vendedor->set('borrar.remito.imagen', (int)$fields['borrar-remito-imagen']->rawvalue == 1);
            $vendedor->set('borrar.hojasDeRuta', (int)$fields['borrar-hojas-de-ruta']->rawvalue);
            $vendedor->set('tipo', trim(count($fields['tipo']->rawvalue) ? $fields['tipo']->rawvalue[0] : ''));
            $vendedor->set('enviarAFacturacion', (int)$fields['enviar-a-facturacion']->rawvalue == 1);
            $vendedor->set('modificar.remito.estado.qr', (int)$fields['marcar-entrega-de-remito-con-qr']->rawvalue == 1);
            $vendedor->set('cancelar.cotizacion', (int)$fields['cancelar-cotizacion']->rawvalue == 1);
            $vendedor->set('crear.ordenDeTrabajo', (int)$fields['crear-orden-de-trabajo']->rawvalue == 1);

            $idDeposito = $fields['deposito']->rawvalue;
            if (empty($idDeposito)) {
                $config = self::getComponentParams();
                $idDeposito = $config->get('cotizacion_deposito_default');
            }

            Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_sabullvial/tables');
            $deposito = Table::getInstance('Deposito', 'SabullvialTable');

            if ($deposito && $deposito->load($idDeposito)) {
                $vendedor->set('id_deposito', (int) $idDeposito);
                $vendedor->set('id_deposito_tango', (int) $deposito->id_tango);
            }

            self::$cache[$cacheKey] = $vendedor;
        }

        return self::$cache[$cacheKey];
    }

    /**
     * Undocumented function
     *
     * @param Array $fields
     * @return boolean
     */
    protected static function hasFieldsVendedor($fields)
    {
        if (!isset($fields['ver-stock-real'])) {
            return false;
        }

        if (!isset($fields['ver-todos-los-presupuestos'])) {
            return false;
        }

        if (!isset($fields['ver-todos-los-clientes'])) {
            return false;
        }

        if (!isset($fields['ver-productos'])) {
            return false;
        }

        if (!isset($fields['condicion-de-venta'])) {
            return false;
        }

        if (!isset($fields['aprobar-presupuestos'])) {
            return false;
        }

        if (!isset($fields['aprobar-presupuestos-automaticamente'])) {
            return false;
        }

        if (!isset($fields['modificar-precios'])) {
            return false;
        }

        if (!isset($fields['administrar-remitos'])) {
            return false;
        }

        if (!isset($fields['tipo'])) {
            return false;
        }

        if (!isset($fields['codigo-vendedor'])) {
            return false;
        }

        return true;
    }

    public static function getUserFields($userId = null)
    {
        $cacheKey = 'getUserFields-' . (string) $userId;

        if (!isset(self::$cache[$cacheKey])) {
            $user = !is_null($userId) ? JFactory::getUser($userId) : JFactory::getUser();
            $customFields = FieldsHelper::getFields('com_users.user', $user, true);
            self::$cache[$cacheKey] = Joomla\Utilities\ArrayHelper::pivot($customFields, 'name');
        }

        return self::$cache[$cacheKey];
    }

    public static function array_diff($A, $B)
    {
        $intersect = array_intersect($A, $B);
        return array_merge(array_diff($A, $intersect), array_diff($B, $intersect));
    }

    public static function loadEstadosCotizacionTangoStylesheet()
    {
        require_once JPATH_COMPONENT . '/models/fields/estadocotizaciontango.php';
        $estados = JFormFieldEstadoCotizacionTango::getItems();

        $style = '.chzn-color-state.chzn-estadocotizaciontango.chzn-single{color: white;}';
        $style .= '.chzn-color-state.chzn-estadocotizaciontango.chzn-single[rel="value_"]{color: #444;}';
        foreach ($estados as $estado) {
            $style .= '.chzn-color-state.chzn-estadocotizaciontango.chzn-single[rel="value_' . $estado['id'] . '"]{color: ' . $estado['color'] . ' !important; background-color: ' . $estado['background_color'] . ' !important;}';
        }

        JFactory::getDocument()->addStyleDeclaration($style);
    }

    public static function loadEstadosCotizacionStylesheet()
    {
        self::loadEstadosStylesheet('EstadoCotizacion', 'estadocotizacion', false);
    }

    public static function loadEstadosCotizacionPagoStylesheet()
    {
        self::loadEstadosStylesheet('EstadoCotizacionPago', 'estadocotizacionpago', true);
    }

    public static function loadEstadosClienteStylesheet()
    {
        self::loadEstadosStylesheet('EstadoCliente', 'estadocliente', false);
    }

    public static function loadEstadosRemitoStylesheet()
    {
        self::loadEstadosStylesheet('EstadoRemito', 'estadoremito', true);
    }

    /**
     * Carga el stylesheet para los estados de una tabla.
     *
     * @param string $tableName
     * @param string $prefix
     * @param boolean $addColor
     * @return string
     */
    public static function loadEstadosStylesheet($tableName, $prefix, $addColor = false)
    {
        $table = Table::getInstance($tableName, 'SabullvialTable');
        $estados = $table->getAll();

        $style = '.chzn-color-state.chzn-' . $prefix . '.chzn-single{color: white;}';
        $style .= '.chzn-color-state.chzn-' . $prefix . '.chzn-single[rel="value_"]{color: #444;}';

        foreach ($estados as $estado) {
            if (!empty($estado->color)) {
                $style .= '.chzn-color-state.chzn-' . $prefix . '.chzn-single[rel="value_' . $estado->id . '"]{';

                if ($addColor) {
                    $style .= 'color: ' . $estado->color_texto . ' !important;';
                }

                $style .= 'background-color: ' . $estado->color . ' !important;}';
            }
        }

        Factory::getDocument()->addStyleDeclaration($style);
    }

    public static function isUserVendedor()
    {
        $cacheKey = __METHOD__;

        if (!isset(self::$cache[$cacheKey])) {
            $params = JComponentHelper::getParams('com_sabullvial');
            $idGroup = $params->get('grupo_vendedor');

            $user = JFactory::getUser();
            $userGroups = $user->getAuthorisedGroups();

            self::$cache[$cacheKey] = in_array($idGroup, $userGroups);
        }

        return self::$cache[$cacheKey];
    }

    public static function isUserAdministrador()
    {
        $cacheKey = __METHOD__;

        if (!isset(self::$cache[$cacheKey])) {
            $params = JComponentHelper::getParams('com_sabullvial');
            $idGroup = $params->get('grupo_administrador');

            $user = JFactory::getUser();
            $userGroups = $user->getAuthorisedGroups();

            self::$cache[$cacheKey] = in_array($idGroup, $userGroups);
        }

        return self::$cache[$cacheKey];
    }

    public static function isUserLogistica()
    {
        $cacheKey = __METHOD__;

        if (!isset(self::$cache[$cacheKey])) {
            $params = JComponentHelper::getParams('com_sabullvial');
            $idGroup = $params->get('grupo_logistica');

            $user = JFactory::getUser();
            $userGroups = $user->getAuthorisedGroups();

            self::$cache[$cacheKey] = in_array($idGroup, $userGroups);
        }

        return self::$cache[$cacheKey];
    }

    public static function isUserSuperAdministrador()
    {
        $cacheKey = __METHOD__;

        if (!isset(self::$cache[$cacheKey])) {
            $user = JFactory::getUser();
            self::$cache[$cacheKey] = $user->authorise('core.admin');
        }

        return self::$cache[$cacheKey];
    }

    public static function getLabelListaDePrecio($idListaPrecio)
    {
        $nullValues = ['1', '2', '7', '10'];
        if (is_null($idListaPrecio) || in_array($idListaPrecio, $nullValues)) {
            return null;
        }

        if (isset(self::LISTA_PRECIOS[$idListaPrecio])) {
            return self::LISTA_PRECIOS[$idListaPrecio];
        }

        return $idListaPrecio;
    }

    /**
     * Verifica si tiene la condicion "Todos".
     *
     * @param string $condiciones
     * @return boolean
     */
    public static function hasAllCondiciones($condiciones)
    {
        if (is_array($condiciones) && count($condiciones) == 0) {
            return true;
        }

        $condiciones = explode(',', $condiciones);
        $condiciones = Joomla\Utilities\ArrayHelper::toInteger($condiciones);

        foreach ($condiciones as $condicion) {
            if ($condicion == 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Arma la query sql para buscar palabras sin orden en la $sqlQuery
     * pasada como parametro. En $sqlQuery tiene que estar elvalor #query#
     * para luego reemplazarlo por las palabras a buscar. Ejemplo:
     * - search: alfajor jorgito
     * - sqlQuery: nombre like #query# OR descripcion like #query#
     * Devuelve:
     * 	(nombre like '%alfajor%' OR descripcion like '%alfajor%') AND
     * 	(nombre like '%jorgito%' OR descripcion like '%jorgito%')
     *
     * @param string $search
     * @param string $sqlQuery
     * @param JDatabaseDriver $db
     * @return void
     */
    public static function unorderedSearch($search, $sqlQuery, $db, $replace = '#query#')
    {
        $words = explode(' ', $search);
        $where = [];
        foreach ($words as $word) {
            $word = trim($word);
            if (empty($word)) {
                continue;
            }

            $like = $db->quote('%' . $word . '%');

            $where[] = '(' . str_replace($replace, $like, $sqlQuery) . ')';
        }
        return implode(' AND ', $where);
    }

    public static function getAssetVersion()
    {
        $store = self::getStoreId(__METHOD__);

        if (isset(self::$cache[$store])) {
            return self::$cache[$store];
        }

        $extension = Table::getInstance('extension');
        $extension->load(['element' => 'com_sabullvial']);
        $manifest = new Registry($extension->get('manifest_cache'));
        self::$cache[$store] = $manifest->get('version');

        return self::$cache[$store];
    }

    public static function isTangoFechaSincronizacionNull($fechaSincronizacionTango)
    {
        return in_array($fechaSincronizacionTango, self::TANGO_FECHA_SINCRONIZACION_NULLS);
    }

    /**
     * Formatea una fecha en formato Y-m-d H:i:s a un string en formato d/m/Y H:i
     *
     * @param string $date string en formato Y-m-d H:i:s
     * @return string
     */
    public static function formatToChatDatetime($date)
    {
        // si es hoy muestro solo la hora, si es ayer muestro "ayer" y la hora, si no muestro la fecha con el nombre del mes y la hora
        $date = new DateTime($date);

        $today = new DateTime('today');
        if ($date->format('Y-m-d') == $today->format('Y-m-d')) {
            return Text::sprintf('COM_SABULLVIAL_TAREA_NOTA_HOY', $date->format('H:i'));
        }

        $yesterday = new DateTime('yesterday');
        if ($date->format('Y-m-d') == $yesterday->format('Y-m-d')) {
            return Text::sprintf('COM_SABULLVIAL_TAREA_NOTA_AYER', $date->format('H:i'));
        }

        //si no muestro la fecha, el nombre del mes en texto y la hora
        return $date->format('d') . ' de ' . Text::_('COM_SABULLVIAL_TAREA_NOTA_' . strtoupper($date->format('F'))) . ' ' . $date->format('H:i\h\s');
    }

    public static function getComponentParams()
    {
        $cacheKey = __METHOD__;

        if (!isset(self::$cache[$cacheKey])) {
            $params = JComponentHelper::getParams('com_sabullvial');
            self::$cache[$cacheKey] = $params;
        }

        return self::$cache[$cacheKey];
    }

    /**
     * Method to get a store id
     *
     * @param   string  $id  A prefix for the store id.
     *
     * @return  string  A store id.
     *
     */
    protected static function getStoreId($id = '')
    {
        return md5($id);
    }
}

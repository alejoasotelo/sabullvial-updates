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

use Joomla\CMS\Table\Table;

/**
 * Producto Table class
 *
 * @since  0.0.1
 */
class SabullvialTableSitCondicionesVenta extends Table
{
    public const CONDICION_CONTADO_PUBLICO = 49;
    public const CONDICION_CONTADO_SIN_CTA_CTE = 50;
    public const CONDICION_TRANSFERENCIA = 51;

    /**
     * Constructor
     *
     * @param   JDatabaseDriver  &$db  A database connector object
     */
    public function __construct(&$db)
    {
        parent::__construct('SIT_CONDICIONES_VENTA', 'ID_SIT_CONDICIONES_VENTA', $db);
    }

    /**
     * Carga un artículos por el código.
     *
     * @param string $codigo
     * @return SabullvialTableSitCondicionesVenta
     */
    public function loadByCondicionVenta($idCondVenta)
    {
        return $this->load(['COND_VTA' => $idCondVenta]);
    }

    public function hasIVA()
    {
        return (int)$this->COND_VTA == self::CONDICION_CONTADO_PUBLICO || (int)$this->COND_VTA == self::CONDICION_CONTADO_SIN_CTA_CTE;
    }

    public function getLabelById($idCondVenta)
    {
        $this->loadByCondicionVenta($idCondVenta);
        return $this->DESC_COND;
    }
}

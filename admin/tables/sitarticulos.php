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
class SabullvialTableSitArticulos extends Table
{
    protected $data = [];

    /**
     * Constructor
     *
     * @param   JDatabaseDriver  &$db  A database connector object
     */
    public function __construct(&$db)
    {
        //$params = JComponentHelper::getParams('com_sabullvial');
        //$dbName = $params->get('database_name_tango');
        parent::__construct('SIT_ARTICULOS', 'ID_SIT_ARTICULOS', $db);
    }

    /**
     * Carga un artículos por el código.
     *
     * @param string $codigo
     * @return SabullvialTableSitArticulos
     */
    public function loadByCodArticulo($codigo)
    {
        return $this->load(['COD_ARTICU' => $codigo]);
    }

    public function getPrecio($idCodigoLista = SabullvialHelper::LISTA_CONSUMIDOR_FINAL)
    {
        $table = Table::getInstance('SitArticulosPrecios', 'SabullvialTable');
        $table->load([
            'COD_ARTICU' => $this->COD_ARTICU,
            'COD_LISTA' => (int)$idCodigoLista
        ]);
        return (float)$table->PRECIO;
    }
}

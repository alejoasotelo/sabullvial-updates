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
use Joomla\CMS\Language\Text;

/**
 * HojaDeRutaRemito Table class
 *
 * @since  0.0.1
 */
class SabullvialTableHojaDeRutaRemito extends Table
{
    /**
     * Constructor
     *
     * @param   JDatabaseDriver  &$db  A database connector object
     */
    public function __construct(&$db)
    {
        parent::__construct('#__sabullvial_hojaderutaremito', 'id', $db);
    }

    /**
     * Stores a contact.
     *
     * @param   boolean  $updateNulls  True to update fields even if they are null.
     *
     * @return  boolean  True on success, false on failure.
     *
     * @since   1.6
     */
    public function store($updateNulls = true)
    {
        $date   = JFactory::getDate()->toSql();
        $userId = JFactory::getUser()->id;

        // Set created date if not set.
        if (!(int) $this->created) {
            $this->created = $date;
        }

        if ($this->id) {
            // Existing item
            $this->modified_by = $userId;
            $this->modified    = $date;
        } else {
            // Field created_by field can be set by the user, so we don't touch it if it's set.
            if (empty($this->created_by)) {
                $this->created_by = $userId;
            }

            if (empty($this->created_by_alias)) {
                $this->created_by_alias = JFactory::getUser()->username;
            }

            if (!(int) $this->modified) {
                $this->modified = $date;
            }

            if (empty($this->modified_by)) {
                $this->modified_by = $userId;
            }
        }

        return parent::store($updateNulls);
    }

    public function loadByIdHojaDeRuta($idHojaDeRuta)
    {
        $db = $this->_db;

        // Initialise the query.
        $query = $this->_db->getQuery(true)
            ->select('h.*')
            ->from($db->qn($this->_tbl, 'h'))
            ->where('h.id_hojaderuta = ' . (int)$idHojaDeRuta);

        $query->select('pr.nombre_tra expreso, pr.direccion, pr.nro_pedido numero_pedido')
            ->innerJoin($db->qn('SIT_PEDIDOS_REMITOS', 'pr') . ' ON (pr.N_REMITO = h.numero_remito)');

        $query->select('c.razon_soci cliente')
            ->innerJoin($db->qn('SIT_CLIENTES', 'c') . ' ON (pr.cod_client = c.cod_client)');

        $query->select('DOM_TRANS transporte_direccion')
            ->leftJoin($db->qn('#__sabullvial_cotizacion', 'sc') . ' ON (sc.id = pr.ID_PEDIDO_WEB)')
            ->leftJoin($db->qn('SIT_TRANSPORTES', 't') . ' ON (t.COD_TRANSP = sc.id_transporte)');

        $query->select('re.id_estadoremito, er.nombre estadoremito, er.color estadoremito_bg_color, er.color_texto estadoremito_color')
            ->select('re.image estadoremito_image')
            ->leftJoin($db->qn('#__sabullvial_remitoestado', 're') . ' ON (re.numero_remito = pr.N_REMITO)')
            ->leftJoin($db->qn('#__sabullvial_estadoremito', 'er') . ' ON (er.id = re.id_estadoremito)')
            ->group('h.numero_remito');

        $this->_db->setQuery($query);

        $rows = $this->_db->loadAssocList();

        foreach ($rows as &$row) {
            $articulos = $this->getArticulosByNumeroPedido($row['numero_pedido']);
            $monto = 0;
            $mercaderia = [];
            foreach ($articulos as $articulo) {
                $cantidad = (int)$articulo['cantidad'];
                $mercaderia[] = '<li>' . Text::sprintf('COM_SABULLVIAL_HOJA_DE_RUTA_REMITO_N', $cantidad, $articulo['nombre']) . '</li>';
                $monto += $cantidad * (float)$articulo['precio'];
            }

            $row['mercaderia'] = '<ul>' . rtrim(implode('', $mercaderia)) . '</ul>';
            $row['monto'] = $monto;
        }

        return $rows;
    }

    public function getArticulosByNumeroPedido($sitNumeroPedido)
    {
        $db = $this->_db;

        // Initialise the query.
        $query = $this->_db->getQuery(true)
            ->select('a.COD_ARTICU, a.DESCRIPCIO nombre, r.CANT_REM cantidad, ap.PRECIO precio')
            ->from($db->qn('SIT_PEDIDOS_REMITOS', 'r'))
            ->leftJoin($db->qn('SIT_ARTICULOS', 'a') . ' ON (a.COD_ARTICU = r.COD_ARTICU)')
            ->leftJoin($db->qn('SIT_ARTICULOS_PRECIOS', 'ap') . ' ON (ap.COD_ARTICU = r.COD_ARTICU AND ap.COD_LISTA = 2)')
            ->where('r.NRO_PEDIDO = ' . $db->q($sitNumeroPedido));

        /*$query->select('a.cod_articu cod_articulo, a.descripcio articulo, a.marca articulo_marca')
        ->leftJoin($db->qn($dbName  . '.SIT_ARTICULOS', 'a') . ' ON (a.cod_articu = prf.cod_articu)');*/
        $this->_db->setQuery($query);

        return $this->_db->loadAssocList();
    }
}

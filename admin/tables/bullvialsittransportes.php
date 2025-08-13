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

use Joomla\CMS\Language\Text;

/**
 * Producto Table class
 *
 * @since  0.0.1
 */
class SabullvialTableBullvialSitTransportes extends JTable
{
    public const CODIGO_TRANSPORTE_CLIENTE_RETIRA_DEL_DEPOSITO = '00154';
    public const CODIGO_TRANSPORTE_BULLVIAL_RAMA = '00147';

    /**
     * Constructor
     *
     * @param   JDatabaseDriver  &$db  A database connector object
     */
    public function __construct(&$db)
    {
        parent::__construct('SIT_TRANSPORTES', 'ID_SIT_TRANSPORTES', $db);
    }

    public function getAll()
    {
        $orderFirstItems = [
            self::CODIGO_TRANSPORTE_CLIENTE_RETIRA_DEL_DEPOSITO,
            self::CODIGO_TRANSPORTE_BULLVIAL_RAMA
        ];

        $subQuery = $this->_db->getQuery(true);
        $subQuery->select('id_transporte, count(*) cantidad_busquedas')
            ->from($this->_db->qn('#__sabullvial_cotizacion'))
            ->where('id_transporte != "" AND id_transporte != "undefined"')
            ->group('id_transporte HAVING count(*) > 0')
            ->order('cantidad_busquedas DESC');

        $query = $this->_db->getQuery(true)
            ->select('t.*, t.COD_TRANSP id, concat("(", t.COD_TRANSP, ")", " ", t.DOM_TRANS, " - ", t.NOMBRE_TRA) name')
            ->select('IF(t.COD_TRANSP IN ('.implode(',', $orderFirstItems).'), 1, 0) primera_posicion, IFNULL(c.cantidad_busquedas, 0) cantidad_busquedas')
            ->from($this->_db->qn($this->_tbl, 't'))
            ->leftJoin('('.$subQuery.') c ON (c.id_transporte = t.COD_TRANSP)')
            ->order('primera_posicion DESC, cantidad_busquedas DESC, TRIM(DOM_TRANS) ASC');

        $this->_db->setQuery($query);

        return $this->_db->loadObjectList();
    }

    public function getFullName()
    {
        if (!$this->COD_TRANSP) {
            return '';
        }

        return Text::sprintf('COM_SABULLVIAL_SIT_TRANSPORTES_FULL_NAME', $this->COD_TRANSP, $this->DOM_TRANS, $this->NOMBRE_TRA);
    }

    public function loadByCodigoTransporte($codigoTransporte)
    {
        return $this->load(['COD_TRANSP' => $codigoTransporte]);
    }
}

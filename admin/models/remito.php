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

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;

/**
 * Remito Model
 *
 * @since  0.0.1
 */
class SabullvialModelRemito extends AdminModel
{
    protected $batch_commands = [
        'hojaderuta' => 'batchGenerarHojaDeRuta',
        'entregadopormostrador' => 'batchMarcarComoEntregadoPorMostrador',
    ];

    /**
     * Method to get a table object, load it if necessary.
     *
     * @param   string  $type    The table name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  JTable  A JTable object
     *
     * @since   1.6
     */
    public function getTable($type = 'BullvialSitPedidosRemitos', $prefix = 'SabullvialTable', $config = [])
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to get the record form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  mixed    A JForm object on success, false on failure
     *
     * @since   1.6
     */
    public function getForm($data = [], $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm(
            'com_sabullvial.remito',
            'remito',
            [
                'control' => 'jform',
                'load_data' => $loadData
            ]
        );

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed  The data for the form.
     *
     * @since   1.6
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState(
            'com_sabullvial.edit.remito.data',
            []
        );

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    /**
     * Devuelve la lista de productos del remito.
     *
     * @return Array<Object>
     */
    public function getProductos($numeroRemito)
    {
        if (empty($numeroRemito)) {
            throw new InvalidArgumentException('Número de remito inválido');
        }

        // Initialize variables.
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
            ->select('r.COD_ARTICU codigo_articulo')
            ->select('0 cantidad_pedido, r.CANT_REM cantidad_remito, 0 cantidad_factura')
            ->select('(r.IMPORTE_SIN_IVA / r.CANT_REM) precio_unitario')
            ->from('SIT_PEDIDOS_REMITOS r')

            ->select('a.DESCRIPCIO descripcion, a.DESC_AMPLIADA descripcion_adicional, a.MARCA marca')
            ->leftJoin($db->qn('SIT_ARTICULOS', 'a') . ' ON (a.COD_ARTICU = r.COD_ARTICU)')

            ->where('r.N_REMITO = ' . $db->q($numeroRemito));

        $db->setQuery($query);

        return $db->loadObjectList();
    }

    public function getHistorico($numeroRemito)
    {
        $db    = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('rh.*');
        $query->from('#__sabullvial_remitohistorico rh');

        $query->select('er.nombre estado, er.color bg_color, er.color_texto color, er.entregado, er.entregado_mostrador')
            ->leftJoin('#__sabullvial_estadoremito er ON (er.id = rh.id_estadoremito)');

        $query->where('rh.numero_remito = ' . $db->q($numeroRemito))
            ->order('rh.created DESC');
        $db->setQuery($query);
        $items = $db->loadObjectList();

        return $items;
    }

    /**
     * Method to perform batch operations on an item or a set of items.
     *
     * @param   array  $commands  An array of commands to perform.
     * @param   array  $pks       An array of item ids.
     * @param   array  $contexts  An array of item contexts.
     *
     * @return  boolean  Returns true on success, false on failure.
     *
     * @since   1.7
     */
    public function batch($commands, $pks, $contexts)
    {
        // Sanitize ids.
        $pks = array_unique($pks);
        //$pks = ArrayHelper::toString($pks);

        // Remove any values of zero.
        if (array_search('', $pks, true)) {
            unset($pks[array_search('', $pks, true)]);
        }

        if (empty($pks)) {
            $this->setError(Text::_('JGLOBAL_NO_ITEM_SELECTED'));

            return false;
        }

        $done = false;

        // Initialize re-usable member properties
        $this->initBatch();

        if ($this->batch_copymove && !empty($commands[$this->batch_copymove])) {
            $cmd = ArrayHelper::getValue($commands, 'move_copy', 'c');

            if ($cmd === 'c') {
                $result = $this->batchCopy($commands[$this->batch_copymove], $pks, $contexts);

                if (is_array($result)) {
                    foreach ($result as $old => $new) {
                        $contexts[$new] = $contexts[$old];
                    }

                    $pks = array_values($result);
                } else {
                    return false;
                }
            } elseif ($cmd === 'm' && !$this->batchMove($commands[$this->batch_copymove], $pks, $contexts)) {
                return false;
            }

            $done = true;
        }

        foreach ($this->batch_commands as $identifier => $command) {
            // como la función batch en joomla es para hacer todo desde una sola modal
            // y aca lo usamos en varias modales diferentes hay que ver qué valores se envían y
            // ejecutar una sola acción.
            if (!$this->hasEmptyValues($commands[$identifier])) {
                if (!$this->$command($commands[$identifier], $pks, $contexts)) {
                    return false;
                }

                $done = true;
            }
        }

        if (!$done) {
            $this->setError(Text::_('JLIB_APPLICATION_ERROR_INSUFFICIENT_BATCH_INFORMATION'));

            return false;
        }

        // Clear the cache
        $this->cleanCache();

        return true;
    }

    /**
     * Batch language changes for a group of rows.
     *
     * @param   string  $value     The new value matching a language.
     * @param   array   $pks       An array of row IDs.
     * @param   array   $contexts  An array of item contexts.
     *
     * @return  boolean  True if successful, false otherwise and internal error is set.
     *
     * @since   2.5
     */
    public function batchGenerarHojaDeRuta($value, $pks, $contexts)
    {
        /** @var SabullvialTableHojaDeRuta $hojaDeRuta */
        $hojaDeRuta = Table::getInstance('HojaDeRuta', 'SabullvialTable');

        if (!$hojaDeRuta->save($value)) {
            $this->setError('<ul><li>' . implode("</li><li>", $hojaDeRuta->getErrors()) . '</li></ul>');
            return false;
        }

        $this->table = $this->getTable();

        $idEstadoRemitoEnPreparacion = Table::getInstance('EstadoRemito', 'SabullvialTable')
            ->getEnPreparacion()
            ->id;

        foreach ($pks as $pk) {
            $this->table->load($pk);

            $tableHojaDeRutaRemito = Table::getInstance('HojaDeRutaRemito', 'SabullvialTable');
            $data = [
                'id_hojaderuta' => $hojaDeRuta->id,
                'numero_remito' => $this->table->N_REMITO,
            ];

            if (!$tableHojaDeRutaRemito->save($data)) {
                $this->setError($tableHojaDeRutaRemito->getError());

                return false;
            }

            $data = [
                'id_estadoremito' => $idEstadoRemitoEnPreparacion,
                'numero_remito' => $this->table->N_REMITO,
                'delivery_date' => $value['delivery_date']
            ];

            /** @var SabullvialTableRemitoEstado $remitoEstado */
            $remitoEstado = Table::getInstance('RemitoEstado', 'SabullvialTable');

            // Lo cargo por si existe.
            $remitoEstado->load(['numero_remito' => $this->table->N_REMITO]);

            if (!$remitoEstado->save($data)) {
                $this->setError($remitoEstado->getError());

                return false;
            }
        }

        // Clean the cache
        $this->cleanCache();

        return true;
    }

    /**
     * Batch language changes for a group of rows.
     *
     * @param   string  $value     The new value matching a language.
     * @param   array   $pks       An array of row IDs.
     * @param   array   $contexts  An array of item contexts.
     *
     * @return  boolean  True if successful, false otherwise and internal error is set.
     *
     * @since   2.5
     */
    public function batchMarcarComoEntregadoPorMostrador($value, $pks, $contexts)
    {
        // Initialize re-usable member properties, and re-usable local variables
        // $this->initBatch();
        $idEstadoRemitoEntregadoPorMostrador = Table::getInstance('EstadoRemito', 'SabullvialTable')
            ->getEntregadoMostrador()
            ->id;

        foreach ($pks as $pk) {
            /*if (!$this->user->authorise('core.edit', $contexts[$pk]))
            {
                $this->setError(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT') . ' - ' . $pk);
                return false;
            }*/

            /** @var SabullvialTableRemitoEstado $remitoEstado */
            $remitoEstado = Table::getInstance('RemitoEstado', 'SabullvialTable');
            // Lo cargo por si existe.
            $remitoEstado->load(['numero_remito' => $pk]);

            $data = [
                'numero_remito' => $pk,
                'id_estadoremito' => $idEstadoRemitoEntregadoPorMostrador,
                'delivery_date' => Joomla\CMS\Factory::getDate($value['delivery_date'])->toSql()
            ];

            if (!$remitoEstado->save($data)) {
                $this->setError($remitoEstado->getError());

                return false;
            }
        }

        // Clean the cache
        $this->cleanCache();

        return true;
    }

    public function batchAddRemitosToHojaDeRuta($value, $pks, $contexts)
    {
        /** @var SabullvialTableHojaDeRuta $hojaDeRuta */
        $hojaDeRuta = Table::getInstance('HojaDeRuta', 'SabullvialTable');
        $hojaDeRuta->load($value['id_hojaderuta']);

        if (!$hojaDeRuta->id) {
            $this->setError('Hoja de ruta no encontrada');

            return false;
        }

        $idEstadoRemitoEnPreparacion = Table::getInstance('EstadoRemito', 'SabullvialTable')
            ->getEnPreparacion()
            ->id;

        $this->table = $this->getTable();
        foreach ($pks as $pk) {
            $this->table->load($pk);

            $tableHojaDeRutaRemito = Table::getInstance('HojaDeRutaRemito', 'SabullvialTable');
            $tableHojaDeRutaRemito->load(['numero_remito' => $this->table->N_REMITO]);

            if ($tableHojaDeRutaRemito->id) {
                $this->setError(Text::sprintf('COM_SABULLVIAL_REMITO_REMITO_YA_TIENE_HOJA_DE_RUTA_ASIGNADA', $this->table->N_REMITO));

                return false;
            }

            $data = [
                'id_hojaderuta' => $hojaDeRuta->id,
                'numero_remito' => $this->table->N_REMITO,
            ];

            if (!$tableHojaDeRutaRemito->save($data)) {
                $this->setError($tableHojaDeRutaRemito->getError());

                return false;
            }

            $data = [
                'id_estadoremito' => $idEstadoRemitoEnPreparacion,
                'numero_remito' => $this->table->N_REMITO,
                'delivery_date' => $hojaDeRuta->delivery_date
            ];

            /** @var SabullvialTableRemitoEstado $remitoEstado */
            $remitoEstado = Table::getInstance('RemitoEstado', 'SabullvialTable');

            // Lo cargo por si existe.
            $remitoEstado->load(['numero_remito' => $this->table->N_REMITO]);

            if (!$remitoEstado->save($data)) {
                $this->setError($remitoEstado->getError());

                return false;
            }
        }

        // Clean the cache
        $this->cleanCache();

        return true;

    }

    public function batchDeleteRemitosFromHojaDeRuta($value, $pks, $contexts)
    {
        $db = Factory::getDbo();

        $idEstadoRemitoEnProceso = Table::getInstance('EstadoRemito', 'SabullvialTable')
            ->getEnProceso()
            ->id;

        foreach ($pks as $numeroRemito) {
            $query = $db->getQuery(true)
                ->delete('#__sabullvial_hojaderutaremito')
                ->where('numero_remito = ' . $db->q($numeroRemito));

            $db->setQuery($query);

            if (!$db->execute()) {
                $this->setError($db->getErrorMsg());

                return false;
            }

            $data = [
                'id_estadoremito' => $idEstadoRemitoEnProceso,
                'numero_remito' => $numeroRemito,
            ];

            /** @var SabullvialTableRemitoEstado $remitoEstado */
            $remitoEstado = Table::getInstance('RemitoEstado', 'SabullvialTable');

            // Lo cargo por si existe.
            $remitoEstado->load(['numero_remito' => $numeroRemito]);

            if (!$remitoEstado->save($data)) {
                $this->setError($remitoEstado->getError());

                return false;
            }
        }

        // Clean the cache
        $this->cleanCache();

        return true;
    }

    /**
     * Elimina la imagen actual del remito (tabla: #__sabullvial_remitoestado),
     * en el último estado del historico de estados (#__sabullvial_remitohistorico)
     * y el archivo del servidor.
     *
     * @param string $idRemito
     * @return bool
     */
    public function deleteImage($idRemito)
    {
        $db = Factory::getDbo();

        // obtengo primero el último estado del remito
        $table = Table::getInstance('RemitoEstado', 'SabullvialTable');
        $table->load(['numero_remito' => $idRemito]);

        $image = $table->image;
        $idEstadoRemito = $table->id_estadoremito;

        $table->image = '';

        $db->transactionStart();
        if (!$table->store()) {
            $this->setError($table->getError());
            $db->transactionRollback();
            return false;
        }

        // elimino la imagen del último estado del historico
        $table = Table::getInstance('RemitoHistorico', 'SabullvialTable');
        $table->load([
            'numero_remito' => $idRemito,
            'id_estadoremito' => $idEstadoRemito,
            'image' => $image
        ]);

        $table->image = '';
        if (!$table->store()) {
            $this->setError($table->getError());
            $db->transactionRollback();
            return false;
        }

        $db->transactionCommit();

        // elimino la imagen del servidor
        $path = JPATH_SITE . $image;
        if (File::exists($path)) {
            File::delete($path);
        }

        return true;
    }

    public function deleteImageByIdRemitoHistorico($idRemitoHistorico)
    {
        $db = Factory::getDbo();

        $table = Table::getInstance('RemitoHistorico', 'SabullvialTable');
        $table->load($idRemitoHistorico);

        if (!$table->id) {
            $this->setError('No se encontró el remito historico');

            return false;
        }

        $image = $table->image;

        $db->transactionStart();

        if ($this->isLastRemitoHistorico($idRemitoHistorico, $table->numero_remito)) {
            $tableRemitoEstado = Table::getInstance('RemitoEstado', 'SabullvialTable');
            $tableRemitoEstado->load(['numero_remito' => $table->numero_remito]);
            $tableRemitoEstado->image = '';

            if (!$tableRemitoEstado->store()) {
                $this->setError($tableRemitoEstado->getError());
                $db->transactionRollback();
                return false;
            }
        }

        $table->image = '';
        if (!$table->store()) {
            $this->setError($table->getError());
            $db->transactionRollback();
            return false;
        }

        $db->transactionCommit();

        $path = JPATH_SITE . $image;
        if (File::exists($path)) {
            File::delete($path);
        }

        return true;
    }

    protected function isLastRemitoHistorico($idRemitoHistorico, $numeroRemito)
    {
        $db = Factory::getDbo();
        // find the last remito historico by $numeroRemito
        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__sabullvial_remitohistorico')
            ->where('numero_remito = ' . $db->q($numeroRemito))
            ->order('created DESC')
            ->setLimit(1);

        $db->setQuery($query);
        $lastRemitoHistorico = $db->loadResult();

        return (int)$lastRemitoHistorico == (int)$idRemitoHistorico;
    }

    protected function hasEmptyValues($values)
    {
        foreach ($values as $value) {
            if (!empty($value)) {
                return false;
            }
        }

        return true;
    }
}

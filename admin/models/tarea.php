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
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\Table\Table;

/**
 * Tarea Model
 *
 * @since  0.0.1
 */
class SabullvialModelTarea extends AdminModel
{
    /**
     * Internal memory based cache array of data.
     *
     * @var    array
     * @since  1.6
     */
    protected $cache = [];

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
    public function getTable($type = 'Tarea', $prefix = 'SabullvialTable', $config = [])
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
            'com_sabullvial.tarea',
            'tarea',
            [
                'control' => 'jform',
                'load_data' => $loadData
            ]
        );

        if (empty($form)) {
            return false;
        }

        $vendedor = SabullvialHelper::getVendedor();
        if ($vendedor->get('tipo') == SabullvialHelper::USUARIO_TIPO_VENDEDOR) {
            $this->processFormForVendedor($form);
        }

        if (!SabullvialHelper::isUserVendedor()) {
            return $form;
        }

        $form->setFieldAttribute('id_regla', 'type', 'hidden');
        $form->setFieldAttribute('group_id', 'type', 'hidden');
        $form->setFieldAttribute('id_cliente', 'type', 'hidden');

        return $form;
    }

    /**
     * Procesa el formulario para el vendedor
     *
     * @param JForm $form
     * @return void
     */
    protected function processFormForVendedor($form)
    {
        $formData = $form->getData();
        $idCotizacion = $formData->get('id_cotizacion');
        $cotizacion = $this->getCotizacion($idCotizacion);

        if (!$cotizacion) {
            return;
        }

        $canCancelar = SabullvialButtonsHelper::canCancelar((int) $cotizacion->id_estadocotizacion);
        if (!$canCancelar) {
            $form->setFieldAttribute('id_estadocotizacion', 'readonly', true);
        } else {
            $idEstadosCotizacion = array_map(function ($estado) {
                return $estado->id;
            }, Table::getInstance('EstadoCotizacion', 'SabullvialTable')->getEstadosCancelado());
            $idEstadosCotizacion[] = $cotizacion->id_estadocotizacion;
            $form->setFieldAttribute('id_estadocotizacion', 'excludeallexcept', implode(',', $idEstadosCotizacion));
        }
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
        $app = Factory::getApplication();
        $data = $app->getUserState(
            'com_sabullvial.edit.tarea.data',
            []
        );

        if (empty($data)) {
            $data = $this->getItem();

            if ($data->task_type == SabullvialTableTarea::TASK_TYPE_LLAMADA) {
                $data->task_value_llamada = $data->task_value;
            } elseif ($data->task_type == SabullvialTableTarea::TASK_TYPE_NOTIFICAR_POR_EMAIL) {
                $data->task_value_email = $data->task_value;
            } elseif ($data->task_type == SabullvialTableTarea::TASK_TYPE_ACTION) {
                $data->task_value_action = $data->task_value;
            }

            $data->usuarios = $this->loadFormDataUsuarios($data->id);
        }

        $input = $app->input;
        $view = $input->getCmd('view', '');

        $subtask = $app->getUserState('com_sabullvial.edit.' . $view . '.subtask', '');
        $app->setUserState('com_sabullvial.edit.tarea.subtask', '');

        if ($view == 'tarea') {
            switch ($subtask) {
                case 'tarea.changeCotizacion':
                    if (!isset($data['id_cotizacion']) || !$data['id_cotizacion']) {
                        break;
                    }

                    $cotizacion =  JTable::getInstance('Cotizacion', 'SabullvialTable');
                    $cotizacion->load($data['id_cotizacion']);

                    if (!$cotizacion->id) {
                        break;
                    }

                    $data['usuarios'] = array_unique([$cotizacion->created_by, Factory::getUser()->id]);
                    $data['codigo_cliente'] = $cotizacion->id_cliente;
                    $data['codigo_cliente_name'] = $cotizacion->cliente;
                    $data = $this->processTaskTypeAndValue($data, $cotizacion);
                    break;
            }
        }

        return $data;
    }

    protected function loadFormDataUsuarios($idTarea)
    {
        $isNew = is_null($this->getState($this->getName() . '.id'));

        if ($isNew) {
            return [Factory::getUser()->id];
        }

        /** @var SabullvialTableTareaUsuario $table */
        $table = JTable::getInstance('TareaUsuario', 'SabullvialTable');
        $tareasUsuario = $table->loadByIdTarea($idTarea);

        $idsUsuarios = array_map(function ($tareaUsuario) {
            return $tareaUsuario['user_id'];
        }, $tareasUsuario);

        return $idsUsuarios;
    }

    protected function processTaskTypeAndValue($data, $cotizacion)
    {
        if (!$cotizacion->id_direccion) {
            return $data;
        }

        JTable::getInstance('Tarea', 'SabullvialTable');

        /** @var SabullvialTableCotizacion $table */
        $tableDireccionEntrega = Table::getInstance('SitClientesDireccionEntrega', 'SabullvialTable');
        $direccion = $tableDireccionEntrega->findByIdDireccionEntrega($cotizacion->id_direccion);

        if (!$direccion && !empty($direccion->TELEFONO1)) {
            return $data;
        }

        $data['task_type'] = SabullvialTableTarea::TASK_TYPE_LLAMADA;
        $data['task_value'] = $direccion->TELEFONO1;
        $data['task_value_llamada'] = $data['task_value'];

        return $data;
    }

    public function save($data)
    {
        $isSaved = parent::save($data);

        if (!$isSaved) {
            return false;
        }

        $idTarea = $this->getState($this->getName() . '.id');

        $this->processTareaUsuarios($idTarea, $data);

        return true;
    }

    /*
    * @param   int  $idTarea
    * @param   string  $body
    *
    * @return  SabullvialTableTareaNota|false
    */
    public function addNota($idTarea, $body)
    {
        $table = $this->getTable();
        $table->load($idTarea);

        $tableNota = Table::getInstance('TareaNota', 'SabullvialTable');

        $save = $tableNota->save([
            'id_tarea' => $idTarea,
            'body' => $body
        ]);

        if (!$save) {
            $this->setError($tableNota->getError());

            return false;
        }

        return $tableNota;
    }

    public function delete(&$pks)
    {
        $isDeleted = parent::delete($pks);

        if (!$isDeleted) {
            return false;
        }

        $pks = (array) $pks;

        /** @var SabullvialTableTareaUsuario $table */
        $table = JTable::getInstance('TareaUsuario', 'SabullvialTable');

        /** @var SabullvialTableTareaNota $table */
        $tableNota = JTable::getInstance('TareaNota', 'SabullvialTable'); /** @var SabullvialTableTarea $table */

        foreach ($pks as $pk) {
            $table->deleteByIdTarea($pk);
            $tableNota->deleteByIdTarea($pk);
        }

        return true;
    }

    protected function processTareaUsuarios($idTarea, $data)
    {
        $data['usuarios'] = $data['usuarios'] ? $data['usuarios'] : [];

        $table = $this->getTable();
        $table->load($idTarea);
        $tareasUsuarios = $table->getUsuarios();

        $current = [];

        foreach ($tareasUsuarios as $tareaUsuario) {
            if (!in_array($tareaUsuario['user_id'], $data['usuarios'])) {
                $tareaUsuarioTable = JTable::getInstance('TareaUsuario', 'SabullvialTable');
                $tareaUsuarioTable->deleteByIdTareaAndUserId($idTarea, $tareaUsuario['user_id']);
                continue;
            }

            $current[] = $tareaUsuario['user_id'];
        }

        $idsToAdd = array_diff($data['usuarios'], $current);

        foreach ($idsToAdd as $idUsuario) {
            /** @var SabullvialTableTareaUsuario $table */
            $tareaUsuarioTable = JTable::getInstance('TareaUsuario', 'SabullvialTable');
            $tareaUsuarioTable->save([
                'id_tarea' => $idTarea,
                'user_id' => $idUsuario
            ]);
        }
    }

    /**
     * Devuelve la cotización asociada a la tarea si existe
     *
     * @param int $idCotizacion
     * @return \JObject|false
     */
    public function getCotizacion($idCotizacion)
    {
        if (!$idCotizacion) {
            return false;
        }

        $storeId = 'getCotizacion-' . $idCotizacion;
        if (isset($this->cache[$storeId])) {
            return $this->cache[$storeId];
        }

        $model = ItemModel::getInstance('Cotizacion', 'SabullvialModel', ['ignore_request' => true]);
        $model->setState('cotizacion.id', $idCotizacion);
        $this->cache[$storeId] = $model->getItem();

        return $this->cache[$storeId];
    }
}

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
 * Cliente Model
 *
 * @since  0.0.1
 */
class SabullvialModelCliente extends JModelAdmin
{
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
    public function getTable($type = 'Cliente', $prefix = 'SabullvialTable', $config = [])
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
            'com_sabullvial.cliente',
            'cliente',
            [
                'control' => 'jform',
                'load_data' => $loadData
            ]
        );

        if (empty($form)) {
            return false;
        }

        $vendedor = SabullvialHelper::getVendedor();
        $isAprobarClientes = $vendedor->get('aprobar.clientes', false);

        // Si es admin permito editar sino no
        if ($vendedor->get('tipo') == SabullvialHelper::USUARIO_TIPO_ADMINISTRADOR) {
            $form->setFieldAttribute('codigo_categoria_iva', 'readonly', 'false');
            $form->setFieldAttribute('cupo_credito', 'readonly', 'false');
        }

        if ($isAprobarClientes) {
            $form->setFieldAttribute('monto', 'readonly', 'false');
            $form->setFieldAttribute('plazo', 'readonly', 'false');
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
            'com_sabullvial.edit.cliente.data',
            []
        );

        if (empty($data)) {
            $data = $this->getItem();

            if (!$data->id) {
                $estado = Table::getInstance('EstadoCliente', 'SabullvialTable')->getCreado();
                $data->id_estadocliente = $estado->id;

                $app = JFactory::getApplication();
                $isSite = $app->getName() == 'site';

                if ($isSite) {
                    $data->codigo_vendedor = $app->input->get('codigo_vendedor', '');
                    $params = $this->getState('params');
                    $data->id_condicionventa = $params->get('id_condicionventa_default', '');
                } else {
                    // Administrator
                    $vendedor = SabullvialHelper::getVendedor();
                    $data->codigo_vendedor = $vendedor->get('codigo', '');
                }
            }
        }

        return $data;
    }

    public function getItem($pk = null)
    {
        $item = parent::getItem($pk);

        if ($item->id > 0) {
            /** @var SabullvialTableClienteFormaPago $table */
            $table = JTable::getInstance('ClienteFormaPago', 'SabullvialTable');
            $formasDePago = $table->loadByIdCliente($item->id);

            foreach ($formasDePago as $formaDePago) {
                $item->formapago[] = $formaDePago['id_formapago'];
            }
        }

        return $item;
    }

    public function save($data)
    {
        $data = $this->processAprobacion($data);

        $isSaved = parent::save($data);

        if ($isSaved) {
            $idCliente = $this->getState($this->getName() . '.id');
            $table = $this->getTable();
            $table->load($idCliente);

            $formasDePago = $table->getFormasDePago();
            $clienteFormaPagoTable = JTable::getInstance('ClienteFormaPago', 'SabullvialTable');
            foreach ($formasDePago as $formaDePago) {
                $clienteFormaPagoTable->delete($formaDePago['id']);
            }

            foreach ($data['formapago'] as $idFormaPago) {
                $clienteFormaPagoTable = JTable::getInstance('ClienteFormaPago', 'SabullvialTable');
                $clienteFormaPagoTable->id_formapago = $idFormaPago;
                $clienteFormaPagoTable->id_cliente = $idCliente;
                $clienteFormaPagoTable->store();
            }

            return true;
        }

        return $isSaved;
    }

    /**
     * Procesa la aprobación automática de clientes
     * Si el cliente tiene una forma de pago que se aprueba automáticamente
     * se cambia el estado del cliente a aprobado
     * Si el cliente ya existe no se hace nada
     * Si el cliente no tiene ninguna forma de pago que se aprueba automáticamente
     * no se hace nada
     *
     * @param array $data
     * @return array
     */
    protected function processAprobacion($data)
    {
        $isNew = empty($data['id']);

        if (!$isNew) {
            return $data;
        }

        $params = SabullvialHelper::getComponentParams();
        $formaDePagoAprobacion = $params->get('cliente_formapago_aprobado_automatico', []);
        $aprobadoId = $params->get('cliente_id_estadocliente_aprobado_automatico', 0);

        /** @var SabullvialTableEstadoCliente $table  */
        $table = Table::getInstance('EstadoCliente', 'SabullvialTable');
        $pendienteId = $table->getPendienteId();

        if ($pendienteId > 0) {
            $data['id_estadocliente'] = $pendienteId;
        }

        foreach ($data['formapago'] as $idFormaPago) {
            if (in_array($idFormaPago, $formaDePagoAprobacion)) {
                if ($aprobadoId) {
                    $data['id_estadocliente'] = $aprobadoId;
                    $data['tango_enviar'] = 1;
                }
                break;
            }
        }

        return $data;
    }

    /**
     * Method to change the published state of one or more records.
     *
     * @param   array    &$pks   A list of the primary keys to change.
     * @param   integer  $value  The value of the published state.
     *
     * @return  boolean  True on success.
     *
     * @since   1.6
     */
    public function changeEstado(&$pks, $value, $plazo = null, $monto = null)
    {
        /** @var SabullvialTableCotizacion $table */
        $table = $this->getTable();
        $pks = (array) $pks;

        if (!$value) {
            JLog::add(JText::_('Falta el valor'), \JLog::WARNING, 'jerror');
        }

        // Access checks.
        foreach ($pks as $i => $pk) {
            $table->reset();

            if ($table->load($pk)) {
                if (!$this->canEditState($table)) {
                    // Prune items that you can't change.
                    unset($pks[$i]);

                    JLog::add(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), \JLog::WARNING, 'jerror');

                    return false;
                }
            }
        }

        // Check if there are items to change
        if (!count($pks)) {
            return true;
        }

        // Attempt to change the state of the records.
        if (!$table->changeEstado($pks, $value, $plazo, $monto)) {
            $this->setError($table->getError());

            return false;
        }

        // Clear the component's cache
        $this->cleanCache();

        return true;
    }

    /**
     * Method to delete one or more records.
     *
     * @param   array  &$pks  An array of record primary keys.
     *
     * @return  boolean  True if successful, false if an error occurs.
     *
     * @since   1.6
     */
    public function delete(&$pks)
    {
        $pks = (array)$pks;

        foreach ($pks as $idCliente) {
            $db = $this->getDbo();

            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__sabullvial_clienteformapago'))
                ->where($db->quoteName('id_cliente') . ' = ' . (int)$idCliente);

            $db->setQuery($query);
            $db->execute();
        }

        return parent::delete($pks);
    }
}

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
use Joomla\CMS\Table\Table;

/**
 * HojaDeRuta Model
 *
 * @since  0.0.1
 */
class SabullvialModelHojaDeRuta extends JModelAdmin
{
    public const ESTADO_ANULADO = 0;
    public const ESTADO_CREADO = 1;

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
    public function getTable($type = 'HojaDeRuta', $prefix = 'SabullvialTable', $config = [])
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
        /** @var Joomla\CMS\Form\Form $form */
        // Get the form.
        $form = $this->loadForm(
            'com_sabullvial.hojaderuta',
            'hojaderuta',
            [
                'control' => 'jform',
                'load_data' => $loadData
            ]
        );

        if (empty($form)) {
            return false;
        }

        $remitosHasImage = $this->hasImage($form->getData());

        if (!$remitosHasImage) {
            $this->removeFieldImage($form);
        } else {
            $this->enableDeleteButtomInFieldImage($form);
        }

        $layout = Factory::getApplication()->input->get('layout', 'edit');

        if ($layout == 'view') {
            foreach ($form->getFieldset() as $field) {
                $form->setFieldAttribute($field->fieldname, 'readonly', true);
            }

            // $form->setFieldAttribute('id_direccion', 'id_cliente', $formData->get('id_cliente', 0));
        }

        return $form;
    }

    /**
     * Devuelve true si alguno de los remitos tiene imagen
     *
     * @param Registry $data
     * @return boolean
     */
    protected function hasImage($data)
    {
        $remitos = $data->get('hojaderutaremito', []);

        foreach ($remitos as $remito) {
            if (!empty($remito['estadoremito_image'])) {
                return true;
            }
        }

        return false;
    }

    protected function removeFieldImage($form)
    {
        $elements = $form->getXML()->xpath("/form/fieldset/field[@name='hojaderutaremito']/form/field[@name='estadoremito_image']");

        if (!count($elements)) {
            return;
        }

        $dom = dom_import_simplexml($elements[0]);
        $dom->parentNode->removeChild($dom);
        return true;
    }

    protected function enableDeleteButtomInFieldImage($form)
    {
        $elements = $form->getXML()->xpath("/form/fieldset/field[@name='hojaderutaremito']/form/field[@name='estadoremito_image']");

        if (!count($elements)) {
            return;
        }

        $vendedor = SabullvialHelper::getVendedor();

        $element = $elements[0];
        $element['delete'] = $vendedor->get('borrar.remito.imagen', false);

        // para que ejecute syncPaths que es protected
        $form->setFieldAttribute('hojaderutaremito', 'multiple', true);
        return true;
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
        $data = Factory::getApplication()->getUserState(
            'com_sabullvial.edit.hojaderuta.data',
            []
        );

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    public function getItem($pk = null)
    {
        $item = parent::getItem($pk);
        $hasId = $item->id > 0;

        if (!$hasId) {
            return $item;
        }

        /** @var SabullvialTableHojaDeRutaRemito $table */
        $table = Table::getInstance('HojaDeRutaRemito', 'SabullvialTable');
        $item->hojaderutaremito = $table->loadByIdHojaDeRuta($item->id);

        return $item;
    }

    /**
     * Method to test whether a record can be deleted.
     *
     * @param   object  $record  A record object.
     *
     * @return  boolean  True if allowed to delete the record. Defaults to the permission for the component.
     *
     * @since   1.6
     */
    protected function canDelete($record)
    {
        $vendedor = SabullvialHelper::getVendedor();
        $canDelete = $vendedor->get('borrar.hojasDeRuta', 0);

        if ($canDelete == SabullvialHelper::BORRAR_PROPIOS) {
            $user = Factory::getUser();
            return (int)$record->created_by == (int)$user->id;
        }

        return $canDelete == SabullvialHelper::BORRAR_TODOS;
    }

    /**
     * Method to test whether a record can have its state changed.
     *
     * @param   object  $record  A record object.
     *
     * @return  boolean  True if allowed to change the state of the record. Defaults to the permission for the component.
     *
     * @since   1.6
     */
    protected function canEditState($record)
    {
        return $this->canDelete($record);
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

        foreach ($pks as $idHojaderuta) {
            $db = $this->getDbo();

            $this->deleteRemitosEstados($idHojaderuta);

            $this->deleteRemitosHistorico($idHojaderuta);

            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__sabullvial_hojaderutaremito'))
                ->where('id_hojaderuta = ' . $idHojaderuta);

            $db->setQuery($query);
            $db->execute();
        }

        return parent::delete($pks);
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
    public function publish(&$pks, $value = self::ESTADO_CREADO)
    {
        $res = parent::publish($pks, $value);

        if (!$res || $value != self::ESTADO_ANULADO) {
            return $res;
        }

        $pks = (array)$pks;

        $estadoEnProceso = JTable::getInstance('EstadoRemito', 'SabullvialTable')->getEnProceso();

        foreach ($pks as $idHojaderuta) {
            $this->setEstadoRemito($idHojaderuta, $estadoEnProceso->id);
        }

        return $res;
    }

    public function setEstadoRemito($idHojaderuta, $idEstadoRemito)
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sabullvial_remitoestado'))
            ->where('numero_remito IN (SELECT numero_remito FROM #__sabullvial_hojaderutaremito WHERE id_hojaderuta = ' . $idHojaderuta . ')');

        $remitosEstado = $db->setQuery($query)->loadObjectList();

        foreach ($remitosEstado as $remitoEstado) {
            $table = Table::getInstance('RemitoEstado', 'SabullvialTable');
            $table->load($remitoEstado->id);
            $table->id_estadoremito = $idEstadoRemito;
            $table->store();
        }
    }

    /**
     * Elimina los estados de los remitos de la hoja de ruta
     *
     * @param int $idHojaderuta
     */
    public function deleteRemitosEstados($idHojaderuta)
    {
        $db = $this->getDbo();

        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__sabullvial_remitoestado'))
            ->where('numero_remito IN (SELECT numero_remito FROM #__sabullvial_hojaderutaremito WHERE id_hojaderuta = ' . $idHojaderuta . ')');

        $db->setQuery($query);
        $db->execute();
    }

    /**
     * Elimina el historico de los remitos de la hoja de ruta
     *
     * @param int $idHojaderuta
     */
    public function deleteRemitosHistorico($idHojaderuta)
    {
        $db = $this->getDbo();

        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__sabullvial_remitohistorico'))
            ->where('numero_remito IN (SELECT numero_remito FROM #__sabullvial_hojaderutaremito WHERE id_hojaderuta = ' . $idHojaderuta . ')');

        $db->setQuery($query);
        $db->execute();
    }

    public function save($data)
    {
        $isSaved = parent::save($data);

        if (!$isSaved) {
            return false;
        }

        foreach ($data['hojaderutaremito'] as $hojaDeRutaRemito) {
            /** @var SabullvialTableRemitoEstado $table */
            $table = Table::getInstance('RemitoEstado', 'SabullvialTable');
            $table->load(['numero_remito' => $hojaDeRutaRemito['numero_remito']]);

            if ((int)$hojaDeRutaRemito['id_estadoremito'] != (int)$table->id_estadoremito) {
                $table->id_estadoremito = $hojaDeRutaRemito['id_estadoremito'];
                $table->store();
            }
        }

        return $isSaved;
    }
}

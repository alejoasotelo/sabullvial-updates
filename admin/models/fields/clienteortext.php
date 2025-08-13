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
use Joomla\CMS\Form\FormField;

/**
 * Cliente Form Field class for the Sabullvial component
 *
 * @since  0.0.1
 */
class JFormFieldClienteOrText extends FormField
{
    /**
     * The field type.
     *
     * @var         string
     */
    protected $type = 'ClienteOrText';

    /**
     * Layout to render
     *
     * @var   string
     * @since 3.5
     */
    protected $layout = 'joomla.form.field.clienteortext';

    /**
     * Get the data that is going to be passed to the layout
     *
     * @return  array
     *
     * @since   3.5
     */
    public function getLayoutData()
    {
        // Get the basic field data
        $data = parent::getLayoutData();

        $fieldCliente = isset($this->element['fieldcliente']) ?
            $this->getName($this->element['fieldcliente'])
            : substr_replace($this->name, '_name', strlen($this->name) - 1, 0);

        $fieldClienteValue = isset($this->element['fieldclientevalue']) ? $this->element['fieldclientevalue'] : '';
        $hideDesc = isset($this->element['hidedesc']) ? (bool)$this->element['hidedesc'] : false;
        $hideConsumidorFinal = isset($this->element['hide_consumidorfinal']) ? ((string)$this->element['hide_consumidorfinal'] == 'true') : false;

        $extraData = [
            'clienteName'  => \JText::_('COM_SABULLVIAL_COTIZACION_FIELD_SELECCIONAR_CLIENTE'),
            'cuit'  => '',
            'codigo_vendedor' => '',
            'cod'  => '',
            'saldo'  => '',
            'activo' => true,
            'promedio' => '',
            'fieldCliente' => $fieldCliente,
            'fieldClienteValue' => $fieldClienteValue,
            'hideDesc' => $hideDesc,
            'hideConsumidorfinal' => $hideConsumidorFinal
        ];

        if (!empty($this->value)) {
            $db    = JFactory::getDBO();
            $query = $db->getQuery(true);
            $query->select('COD_CLIENT codcli, RAZON_SOCI razon_social, SALDO_CC saldo, CUIT cuit')
                ->select('IF(INHABILITADO = "N", 1, 0) activo, PROMEDIO_ULT_REC promedio')
                ->select('c.COD_VENDED codigo_vendedor')
                ->from($db->quoteName('SIT_CLIENTES', 'c'))
                ->where('COD_CLIENT = ' . $db->q($this->value));
            $db->setQuery((string) $query);
            $item = $db->loadObject();

            if (!is_null($item) && !empty($item->codcli)) {
                $app = JFactory::getApplication();
                $input = $app->input;
                $option = $input->get('option');
                $view = $input->get('view');
                $context = $option . '.edit.' . $view . '.data';
                $dataContext = $app->getUserState($context);

                $idClienteName = $this->fieldname . '_name';

                $extraData['clienteName'] = is_array($dataContext) && isset($dataContext[$idClienteName]) && !empty($dataContext[$idClienteName]) ? $dataContext[$idClienteName] : $item->razon_social;
                $extraData['cuit'] = $item->cuit;
                $extraData['codigo_vendedor'] = $item->codigo_vendedor;
                $extraData['cod'] = $item->codcli;
                $extraData['saldo'] = $item->saldo;
                $extraData['activo'] = (bool)$item->activo;
                $extraData['promedio'] = $item->promedio;
            }
        }

        // User lookup went wrong, we assign the value instead.
        if ($extraData['clienteName'] === null && !empty($this->value)) {
            $extraData['clienteName'] = $this->value;
        }

        return array_merge($data, $extraData);
    }
}

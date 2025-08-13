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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\FormHelper;

FormHelper::loadFieldClass('list');

/**
 * Cliente Form Field class for the Sabullvial component
 *
 * @since  0.0.1
 */
class JFormFieldCotizacionAjax extends JFormFieldList
{
    /**
     * The field type.
     *
     * @var         string
     */
    public $type = 'CotizacionAjax';

    /**
     * Method to get the field input for a tag field.
     *
     * @return  string  The field input.
     *
     * @since   3.1
     */
    protected function getInput()
    {
        // Load the ajax-chosen customised field
        self::ajaxField('#' . $this->getSelectorId());

        if (strpos($this->class, 'no-select2') === false) {
            $this->class .= ' no-select2';
        }

        if (strpos($this->class, 'no-chosen') === false) {
            $this->class .= ' no-chosen';
        }

        return parent::getInput();
    }

    public function ajaxField($selector)
    {
        $url = 'index.php?option=com_sabullvial&task=cotizaciones.listCotizaciones&' . JSession::getFormToken() . '=1';

        $document = JFactory::getDocument();
        $document->addScriptOptions('com_sabullvial.cotizacionajax', [
            'selector' => $selector,
            'url' => $url,
        ]);

        JHtml::_('jquery.framework');

        $tagOptions = ['version' => 'auto', 'relative' => true];
        HTMLHelper::script('com_sabullvial/libs/select2.min.js', $tagOptions);
        HTMLHelper::script('com_sabullvial/libs/select2.es.js', $tagOptions);
        HTMLHelper::script('com_sabullvial/tools.js', $tagOptions);
        HTMLHelper::script('com_sabullvial/fields/cotizacionajax.js', $tagOptions);

        HTMLHelper::stylesheet('com_sabullvial/libs/select2.min.css', $tagOptions);
        HTMLHelper::stylesheet('com_sabullvial/libs/select2.bootstrap.min.css', $tagOptions);
    }

    protected function getSelectorId()
    {
        $id    = isset($this->element['id']) ? $this->element['id'] : null;
        return $this->getId($id, $this->element['name']);
    }

    /**
     * Method to get a list of tags
     *
     * @return  array  The field option objects.
     *
     * @since   3.1
     */
    protected function getOptions()
    {
        $options = [];
        $value = $this->value;
        if (!empty($value)) {
            $model = JModelLegacy::getInstance('Cotizaciones', 'SabullvialModel', ['ignore_request' => true]);
            $model->setState('filter.search', 'id:'.$value);
            $items = $model->getItems();
            foreach ($items as $item) {
                $cliente = (int)$item->id_cliente == 0 ? 'Consumidor final' : ($item->cliente . ' - ' . $item->id_cliente);
                $option = JHtml::_('select.option', $item->id, $cliente);
                $option->optionattr = [
                    'data-id_cliente' => $item->id_cliente,
                    'data-cliente' => $item->cliente,
                ];

                $options[] = $option;
            }

        }

        return array_merge(parent::getOptions(), $options);
    }
}

<?php

/**
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or exit('Restricted access');

use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;

FormHelper::loadFieldClass('list');
FormHelper::loadFieldClass('clienteajax');

/**
 * Cliente Form Field class for the Sabullvial component.
 *
 * @since  0.0.1
 */
class JFormFieldClienteTangoAjax extends JFormFieldClienteAjax
{
    /**
     * The field type.
     *
     * @var string
     */
    public $type = 'ClienteTangoAjax';

    protected $url = 'index.php?option=com_sabullvial&task=clientestango.listClientes';

    /**
     * Method to get a list of tags.
     *
     * @return array the field option objects
     *
     * @since   3.1
     */
    protected function getOptions()
    {
        $options = [];
        $value = $this->value;
        if (!empty($value)) {
            $model = JModelLegacy::getInstance('ClientesTango', 'SabullvialModel', ['ignore_request' => true]);
            $model->setState('filter.search', 'id:'.$value);
            $items = $model->getItems();
            foreach ($items as $item) {
                $option = JHtml::_('select.option', $item->id, $item->razon_social);
                $option->optionattr = [
                    'data-id' => $item->id,
                    'data-razon_social' => $item->razon_social,
                ];

                $options[] = $option;
            }
        }

        return array_merge(parent::getOptions(), $options);
    }
}

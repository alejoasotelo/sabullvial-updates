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

use Joomla\CMS\HTML\HTMLHelper;

JFormHelper::loadFieldClass('list');

/**
 * Cliente Form Field class for the Sabullvial component
 *
 * @since  0.0.1
 */
class JFormFieldCliente extends JFormFieldList
{
    /**
     * The field type.
     *
     * @var         string
     */
    protected $type = 'Cliente';

    /**
     * Method to get a list of options for a list input.
     *
     * @return  array  An array of JHtml options.
     */
    protected function getOptions()
    {
        $params = JComponentHelper::getParams('com_sabullvial');
        $dbName = $params->get('database_name_tango');

        $db    = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('id, codcli, razon_social, saldo, cuit')
            ->from($db->quoteName($dbName . '.clientes', 'c'));
        $db->setQuery((string) $query);
        $items = $db->loadObjectList();
        $options  = [];

        if ($items) {
            foreach ($items as $item) {
                $option = new stdClass();
                $option->id = $item->id;
                $option->value = $item->id;
                //$option->label = '<b>' . $item->razon_social . '</b></br>Cod: ' . $item->codcli . ' | Cuit: ' . $item->cuit . ' | Saldo: $' . number_format($item->saldo, 2, ',', '.');
                $option->label = '<b>' . $item->razon_social . '</b> - Cod: ' . $item->codcli . ' | Cuit: ' . $item->cuit . ' | Saldo: $' . number_format($item->saldo, 2, ',', '.');
                $option->selected = $item->id == $this->value;
                $options[] = $option;
            }
        }

        return $options;
    }

    /**
     * Method to get the field input markup for a generic list.
     * Use the multiple attribute to enable multiselect.
     *
     * @return  string  The field input markup.
     *
     * @since   3.7.0
     */
    protected function getInput()
    {
        $html = [];
        $attr = '';

        // Initialize some field attributes.
        $attr .= ' class="field-cliente-choices ' . $this->class . '"';
        $attr .= !empty($this->size) ? ' size="' . $this->size . '"' : '';
        $attr .= $this->multiple ? ' multiple' : '';
        $attr .= $this->required ? ' required aria-required="true"' : '';
        $attr .= $this->autofocus ? ' autofocus' : '';

        // To avoid user's confusion, readonly="true" should imply disabled="true".
        if ((string) $this->readonly == '1' || (string) $this->readonly == 'true' || (string) $this->disabled == '1' || (string) $this->disabled == 'true') {
            $attr .= ' disabled="disabled"';
        }

        // Initialize JavaScript field attributes.
        $attr .= $this->onchange ? ' onchange="' . $this->onchange . '"' : '';

        // Get the field options.
        $options = (array) $this->getOptions();

        $document = JFactory::getDocument();
        $document->addScriptOptions('com_sabullvial.fields.cliente', [
            'id_cliente' => $this->value,
            'options' => $options,
        ]);

        HTMLHelper::script('com_sabullvial/choices.min.js', ['relative' => true, 'version' => 'auto']);
        HTMLHelper::stylesheet('com_sabullvial/choices.min.css', ['relative' => true, 'version' => 'auto']);


        // Create a read-only list (no name) with hidden input(s) to store the value(s).
        if ((string) $this->readonly == '1' || (string) $this->readonly == 'true') {
            $html[] = JHtml::_('select.genericlist', $options, '', trim($attr), 'value', 'text', $this->value, $this->id);

            // E.g. form field type tag sends $this->value as array
            if ($this->multiple && is_array($this->value)) {
                if (!count($this->value)) {
                    $this->value[] = '';
                }

                foreach ($this->value as $value) {
                    $html[] = '<input type="hidden" name="' . $this->name . '" value="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '"/>';
                }
            } else {
                $html[] = '<input type="hidden" name="' . $this->name . '" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"/>';
            }
        } else {
            // Create a regular list passing the arguments in an array.
            $listoptions = [];
            $listoptions['option.key'] = 'value';
            $listoptions['option.text'] = 'text';
            $listoptions['list.select'] = $this->value;
            $listoptions['id'] = $this->id;
            $listoptions['list.translate'] = false;
            $listoptions['option.attr'] = 'optionattr';
            $listoptions['list.attr'] = trim($attr);
            $listoptions['option.text.toHtml'] = false;

            $opts = [];
            foreach ($options as $option) {
                $option->text = $option->label;
                $opts[] = $option;
            }

            $parentOptions = parent::getOptions();

            foreach ($parentOptions as &$opt) {
                $opt->label = $opt->text;
                $opts[] = $opt;
            }

            $html[] = JHtml::_('select.genericlist', $opts, $this->name, $listoptions);
        }

        return implode($html);
    }
}

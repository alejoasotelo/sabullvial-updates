<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Make thing clear
 *
 * @var JForm   $tmpl             The Empty form for template
 * @var array   $forms            Array of JForm instances for render the rows
 * @var bool    $multiple         The multiple state for the form field
 * @var int     $min              Count of minimum repeating in multiple mode
 * @var int     $max              Count of maximum repeating in multiple mode
 * @var string  $fieldname        The field name
 * @var string  $control          The forms control
 * @var string  $label            The field label
 * @var string  $description      The field description
 * @var array   $buttons          Array of the buttons that will be rendered
 * @var bool    $groupByFieldset  Whether group the subform fields by it`s fieldset
 */
extract($displayData);

$doc = JFactory::getDocument();

// Build heading
$table_head = '';

if (!empty($groupByFieldset)) {
    foreach ($tmpl->getFieldsets() as $fieldset) {
        $table_head .= '<th>' . JText::_($fieldset->label);

        if ($fieldset->description) {
            $table_head .= '<br /><small style="font-weight:normal">' . JText::_($fieldset->description) . '</small>';
        }

        $table_head .= '</th>';
    }

    $sublayout = 'section-byfieldsets';
} else {
    foreach ($tmpl->getGroup('') as $field) {
        $class = [];
        $style = [];

        $classHeader = $field->getAttribute('class_header', '');
        if (!empty($classHeader)) {
            $class[] = $classHeader;
        }

        if (strtolower($field->type) == 'hidden') {
            $style[] = 'display: none;';
        }

        if ($field->fieldname == 'numero_remito') {
            $widthTh = $field->getAttribute('width_th', null);

            if ($widthTh > 0) {
                $style[] = 'width: ' . $widthTh . 'px;';
            }
        }

        $table_head .= '<th style="'.implode(';', $style).'" class="'.implode(' ', $class).'">' . strip_tags($field->label);

        if ($field->description) {
            $table_head .= '<br /><small style="font-weight:normal">' . JText::_($field->description) . '</small>';
        }

        $table_head .= '</th>';
    }

    $sublayout = 'section';

    // Label will not be shown for sections layout, so reset the margin left
    $doc->addStyleDeclaration(
        '.subform-table-sublayout-section .controls { margin-left: 0px }'
    );
}

// Create the modal id.
$modalId = $fieldname . '_' . $unique_subform_id;
?>
<div class="row-fluid">
	<div class="subform-repeatable-wrapper subform-table-layout subform-table-sublayout-<?php echo $sublayout; ?> form-vertical">
		<div
			class="cotizaciondetalle-repeatable">
			<table class="adminlist table table-striped table-bordered">
				<thead>
					<tr>
						<?php echo $table_head; ?>
					</tr>
				</thead>
				<tbody class="rows-container-<?php echo $unique_subform_id; ?>">
					<?php foreach ($forms as $k => $form):
					    echo $this->sublayout(
					        $sublayout,
					        [
					            'form' => $form,
					            'basegroup' => $fieldname,
					            'group' => $fieldname . $k,
					            'unique_subform_id' => $unique_subform_id,
					        ]
					    );
					endforeach; ?>
				</tbody>
			</table>

			<?php if ($multiple) : ?>
				<template class="subform-repeatable-template-section" style="display: none;"><?php
					// Use str_replace to escape HTML in a simple way, it need for IE compatibility, and should be removed later
					echo str_replace(
					    ['<', '>'],
					    ['SUBFORMLT', 'SUBFORMGT'],
					    trim(
					        $this->sublayout(
					            $sublayout,
					            [
					                'form' => $tmpl,
					                'basegroup' => $fieldname,
					                'group' => $fieldname . 'X',
					                'unique_subform_id' => $unique_subform_id,
					            ]
					        )
					    )
					);
			    ?></template>
			<?php endif; ?>
		</div>
	</div>
</div>

<?php

// Setup variables for display.
$linkArticles = 'index.php?option=com_sabullvial&amp;view=productos&amp;layout=modal&amp;tmpl=component&amp;' . JSession::getFormToken() . '=1';
$modalTitle    = JText::_('COM_SABULLVIAL_FIELD_PRODUCTO_SELECCIONAR_PRODUCTOS');
$urlSelect = $linkArticles . '&amp;function=jSelectCotizacionDetalle_' . $unique_subform_id;
$title = JText::_('COM_SABULLVIAL_SELECCIONAR_PRODUCTO');
?>

<?php echo JHtml::_(
    'bootstrap.renderModal',
    'ModalSelect' . $modalId,
    [
        'title' => $modalTitle,
        'url' => $urlSelect,
        'data-url' => $urlSelect,
        'data-url-original' => $urlSelect,
        'data-selected_id' => '',
        'height' => '400px',
        'width' => '800px',
        'bodyHeight' => '70',
        'modalWidth' => '50',
        'footer' => '<button type="button" class="btn" data-dismiss="modal">' . JText::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>',
    ]
);
/*
$doc->addScriptDeclaration("
    jQuery(document).on('ready', function() {
        var modal = jQuery('#ModalSelect$modalId');

        modal.on('hide.bs.modal', function(event) {
            window.loadProducts();
        });
    });
");*/
?>

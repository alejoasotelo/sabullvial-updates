/**
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Field clienteortext
 */
;(function($){
	'use strict';

	$.fieldClienteOrText = function(container, options){
		var self = this;
		// Merge options with defaults
		this.options = $.extend({}, $.fieldClienteOrText.defaults, options);

		// Set up elements
		this.$container = $(container);
		this.$modal = this.$container.find(this.options.modal);
		this.$modalBody = this.$modal.children('.modal-body');
		this.$input = this.$container.find(this.options.input);
		this.$inputName = this.$container.find(this.options.inputName);
		this.$cuit = this.$container.find(this.options.cuit);
		this.$codigoVendedor = this.$container.find(this.options.codigoVendedor);
		this.$cod = this.$container.find(this.options.cod);
		this.$saldo = this.$container.find(this.options.saldo);
		this.$buttonSelect = this.$container.find(this.options.buttonSelect);
		this.$buttonClear = this.$container.find(this.options.buttonClear);
		this.$buttonConsumidorFinal = this.$container.find(this.options.$buttonConsumidorFinal);

		// Bind events
		this.$buttonSelect.on('click', this.modalOpen.bind(this));
		this.$buttonConsumidorFinal.on('click', function() {
			self.setValue('000000', Joomla.JText._('COM_SABULLVIAL_CONSUMIDOR_FINAL'), '', '000000', '');
		});
		this.$buttonClear.on('click', function () {
			self.setValue('000000', '', '', '000000', '');
		});
		this.$modal.on('hide', this.removeIframe.bind(this));

		// Check for onchange callback,
		var onchangeStr =  this.$input.attr('data-onchange'), onchangeCallback;
		if(onchangeStr) {
			onchangeCallback = new Function(onchangeStr);
			this.$input.on('change', onchangeCallback.bind(this.$input));
		}

	};

	// display modal for select the file
	$.fieldClienteOrText.prototype.modalOpen = function() {
		var $iframe = $('<iframe>', {
			name: 'field-clienteortext-modal',
			src: this.options.url.replace('{field-clienteortext-id}', this.$input.attr('id')),
			width: this.options.modalWidth,
			height: this.options.modalHeight
		});
		this.$modalBody.append($iframe);
		this.$modal.modal('show');
		$('body').addClass('modal-open');

		var self = this; // save context
		$iframe.load(function(){
			var content = $(this).contents();

			// handle value select
			content.on('click', '.button-select', function(){
				self.setValue($(this).data('cliente-value'), $(this).data('cliente-name'), $(this).data('cliente-cuit'), $(this).data('cliente-codigo_vendedor'), $(this).data('cliente-cod'), $(this).data('cliente-saldo'));
				self.modalClose();
				$('body').removeClass('modal-open');
			});
		});
	};

	// close modal
	$.fieldClienteOrText.prototype.modalClose = function() {
		this.$modal.modal('hide');
		this.$modalBody.empty();
		$('body').removeClass('modal-open');
	};

	// close modal
	$.fieldClienteOrText.prototype.removeIframe = function() {
		this.$modalBody.empty();
		$('body').removeClass('modal-open');
	};

	// set the value
	$.fieldClienteOrText.prototype.setValue = function(value, name, cuit, codigoVendedor, cod, saldo) {
		this.$inputName.val(name).trigger('change');
		this.$input.val(value).trigger('change');
		this.$cuit.text(cuit);
		this.$codigoVendedor.text(codigoVendedor);
		this.$cod.text(cod);
		this.$saldo.text('$' + saldo);
	};

	// default options
	$.fieldClienteOrText.defaults = {
		buttonSelect: '.button-select', // selector for button to change the value
		buttonClear: '.button-clear',
		$buttonConsumidorFinal: '.button-consumidor-final',
		input: '.field-clienteortext-input', // selector for the input for the cliente id
		inputName: '.field-clienteortext-input-name', // selector for the input for the cliente name
		cuit: '.field-clienteortext-cuit',
		codigoVendedor: '.field-clienteortext-codigo_vendedor',
		cod: '.field-clienteortext-cod',
		saldo: '.field-clienteortext-saldo',
		modal: '.modal', // modal selector
		url : 'index.php?option=com_sabullvial&view=clientes&layout=modal&tmpl=component',
		modalWidth: '100%', // modal width
		modalHeight: '300px' // modal height
	};

	$.fn.fieldClienteOrText = function(options){
		return this.each(function(){
			var $el = $(this), instance = $el.data('fieldClienteOrText');
			if(!instance){
				var options = options || {},
					data = $el.data();

				// Check options in the element
				for (var p in data) {
					if (data.hasOwnProperty(p)) {
						options[p] = data[p];
					}
				}

				instance = new $.fieldClienteOrText(this, options);
				$el.data('fieldClienteOrText', instance);
			}
		});
	};

	// Initialise all defaults on load and again when subform rows are added
	$(function($) {
		initClienteOrText();
		$(document).on('subform-row-add', initClienteOrText);

		function initClienteOrText (event, container)
		{
			$(container || document).find('.field-clienteortext-wrapper').fieldClienteOrText();
		}
 	});

})(jQuery);

// Compatibility with mootools modal layout
function jSelectUser(element) {
	var $el = jQuery(element),
		value = $el.data('cliente-value'),
		name  = $el.data('cliente-name'),
		cuit  = $el.data('cliente-cuit'),
		codigoVendedor  = $el.data('cliente-codigo_vendedor'),
		cod  = $el.data('cliente-cod'),
		saldo  = $el.data('cliente-saldo'),
		fieldId = $el.data('cliente-field'),
		$inputValue = jQuery('#' + fieldId + '_id'),
		$inputName  = jQuery('#' + fieldId);
		$cuit  = jQuery('#' + fieldId + '_cuit');
		$codigoVendedor  = jQuery('#' + fieldId + '_codigo_vendedor');
		$cod  = jQuery('#' + fieldId + '_cod');
		$saldo  = jQuery('#' + fieldId + '_saldo');

	if (!$inputValue.length) {
		// The input not found
		return;
	}

	// Update the value
	$inputValue.val(value).trigger('change');
	$inputName.val(name || value).trigger('change');
	$cuit.text(cuit);
	$codigoVendedor.text(codigoVendedor);
	$cod.text(cod);
	$saldo.text('$' + saldo);

	// Check for onchange callback,
	var onchangeStr = $inputValue.attr('data-onchange'), onchangeCallback;
	if(onchangeStr) {
		onchangeCallback = new Function(onchangeStr);
		onchangeCallback.call($inputValue[0]);
	}
	jModalClose();
}

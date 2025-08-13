/**
 * @copyright  (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

;(function($){
	"use strict";
	$.cotizacionDetalleRepeatable = function(container, options){
		this.$container = $(container);

		// To avoid scope issues,
		var self = this;

		// check if already exist
		if(this.$container.data("cotizacionDetalleRepeatable")){
			return self;
		}

		// Add a reverse reference to the DOM object
		this.$container.data("cotizacionDetalleRepeatable", self);

		// merge options
		this.options = $.extend({}, $.cotizacionDetalleRepeatable.defaults, options);

		// template for the repeating group
		this.template = '';

		// prepare a row template, and find available field names
		this.prepareTemplate();

		// check rows container
		this.$containerRows = this.options.rowsContainer ? this.$container.find(this.options.rowsContainer) : this.$container;

		// Keep track of amount of rows, this is important to avoid a name duplication
		this.lastRowNum = this.$containerRows.find(this.options.repeatableElement).length;

		// bind add button
		/*this.$container.on('click', this.options.btAdd, function (e) {
			e.preventDefault();





			var after = $(this).parents(self.options.repeatableElement);
			if(!after.length){
				after = null;
			}
			self.addRow(after);
		});*/

		// bind remove button
		this.$container.on('click', this.options.btRemove, function (e) {
			e.preventDefault();
			var $row = $(this).parents(self.options.repeatableElement);
			self.removeRow($row);
		});

		// bind move button
		if(this.options.btMove){
			this.$containerRows.sortable({
				items: this.options.repeatableElement,
				handle: this.options.btMove,
				tolerance: 'pointer'
			});
		}

		// tell all that we a ready
		this.$container.trigger('subform-ready');
	};

	// prepare a template that we will use repeating
	$.cotizacionDetalleRepeatable.prototype.prepareTemplate = function(){
		// create from template
		if (this.options.rowTemplateSelector) {
			// Find the template element and get its HTML content, this is our template.
			var $tmplElement = this.$container.find(this.options.rowTemplateSelector).last();

			this.template = $.trim($tmplElement.html()) || '';

			// This is IE fix for <template>
			$tmplElement.css('display', 'none'); // Make sure it not visible
			var map = {'SUBFORMLT': '<', 'SUBFORMGT': '>'};
			this.template = this.template.replace(/(SUBFORMLT)|(SUBFORMGT)/g, function(match){
				return map[match];
			});
		}
		// create from existing rows
		else {
			//find first available
			var row = this.$container.find(this.options.repeatableElement).get(0),
				$row = $(row).clone();

			// clear scripts that can be attached to the fields
			try {
				this.clearScripts($row);
			} catch (e) {
				if(window.console){
					console.log(e);
				}
			}

			this.template = $row.prop('outerHTML');
		}
	};

	// add new row
	$.cotizacionDetalleRepeatable.prototype.addRow = function(after){
		// count how much we already have
		var count = this.$containerRows.find(this.options.repeatableElement).length;
		if(count >= this.options.maximum){
			return null;
		}

		// make new from template
		var row = $.parseHTML(this.template);

		//add to container
		if(after){
			$(after).after(row);
		} else {
			this.$containerRows.append(row);
		}

		var $row = $(row);
		//add marker that it is new
		$row.attr('data-new', 'true');
		// fix names and id`s, and reset values
		this.fixUniqueAttributes($row, count);

		// try find out with related scripts,
		// tricky thing, so be careful
		try {
			this.fixScripts($row);
		} catch (e) {
			if(window.console){
				console.log(e);
			}
		}

		// tell everyone about the new row
		this.$container.trigger('subform-row-add', $row);
		return $row;
	};

	// remove row
	$.cotizacionDetalleRepeatable.prototype.removeRow = function($row){
		// count how much we have
		var count = this.$containerRows.find(this.options.repeatableElement).length;
		if(count <= this.options.minimum){
			return;
		}

		if ($row == undefined) {
			return;
		}

		// tell everyoune about the row will be removed
		this.$container.trigger('subform-row-remove', $row);
		$row.remove();
		this.$container.trigger('subform-row-remove-after');
	};

	// fix names and id`s for fields in $row
	$.cotizacionDetalleRepeatable.prototype.fixUniqueAttributes = function(
		$row, // the jQuery object to do fixes in
		_count, // existing count of rows
		_group, // current group name, e.g. 'optionsX'
		_basename, // group base name, without count, e.g. 'options'
		isNested
	) {
		var group = (typeof _group === 'undefined' ? $row.attr('data-group') : _group),
			basename = (typeof _basename === 'undefined' ? $row.attr('data-base-name') : _basename),
			count    = (typeof _count === 'undefined' ? 0 : _count),
			countnew = Math.max(this.lastRowNum, count),
			groupnew = basename + countnew;

		$row.attr('data-group', groupnew);

		// Fix inputs that have a "name" attribute
		var haveName = $row.find('[name]'),
			ids = {}; // Collect id for fix checkboxes and radio

		for (var i = 0, l = haveName.length; i < l; i++) {
			var $el     = $(haveName[i]),
				name    = $el.attr('name'),
				id      = name.replace(/(\[\]$)/g, '').replace(/(\]\[)/g, '__').replace(/\[/g, '_').replace(/\]/g, ''), // id from name
				nameNew = name.replace('[' + group + '][', '['+ groupnew +']['), // New name
				idNew   = id.replace(group, groupnew).replace(/\W/g, '_'), // Count new id
				countMulti = 0, // count for multiple radio/checkboxes
				forOldAttr = id; // Fix "for" in the labels

			if ($el.prop('type') === 'checkbox' && name.match(/\[\]$/)) { // <input type="checkbox" name="name[]"> fix
				// Recount id
				countMulti = ids[id] ? ids[id].length : 0;
				if (!countMulti) {
					// Set the id for fieldset and group label
					$el.closest('fieldset.checkboxes').attr('id', idNew);
					$row.find('label[for="' + id + '"]').attr('for', idNew).attr('id', idNew + '-lbl');
				}
				forOldAttr = forOldAttr + countMulti;
				idNew = idNew + countMulti;
			}
			else if ($el.prop('type') === 'radio') { // <input type="radio"> fix
				// Recount id
				countMulti = ids[id] ? ids[id].length : 0;
				if (!countMulti) {
					// Set the id for fieldset and group label
					$el.closest('fieldset.radio').attr('id', idNew);
					$row.find('label[for="' + id + '"]').attr('for', idNew).attr('id', idNew + '-lbl');
				}
				forOldAttr = forOldAttr + countMulti;
				idNew = idNew + countMulti;
			}

			// Cache already used id
			if (ids[id]) {
				ids[id].push(true);
			} else {
				ids[id] = [true];
			}

			// Replace the name to new one
			$el.attr('name', nameNew);
			// Set new id
			$el.attr('id', idNew);
			// Guess there a label for this input
			$row.find('label[for="' + forOldAttr + '"]').attr('for', idNew).attr('id', idNew + '-lbl');
		}

		/**
		 * Recursively replace our basename + old group with basename + new group
		 * inside of nested subform template elements. First we try to find such
		 * template elements, then we iterate through them and do the same replacements
		 * that we have made here inside of them.
		 */
		var nestedTemplates = $row.find(this.options.rowTemplateSelector);
		// If we found it, iterate over the found ones (might be more than one!)
		for (var j = 0; j < nestedTemplates.length; j++) {
			// Get the nested templates content (as DocumentFragment) and cast it
			// to a jQuery object
			var nestedTemplate = $($(nestedTemplates[j]).prop('content'));
			// Fix the attributes for this nested template.
			this.fixUniqueAttributes(nestedTemplate, count, group, basename, true);
		}

		// Increment a row counter for current instance only
		if (!isNested) {
			this.lastRowNum = countnew + 1;
		}
	};

	// remove scripts attached to fields
	// @TODO: make thing better when something like that will be accepted https://github.com/joomla/joomla-cms/pull/6357
	$.cotizacionDetalleRepeatable.prototype.clearScripts = function($row){
		// destroy chosen if any
		if($.fn.chosen){
			$row.find('select.chzn-done').each(function(){
				var $el = $(this);
				$el.next('.chzn-container').remove();
				$el.show().addClass('fix-chosen');
			});
		}
	};

	// method for hack the scripts that can be related
	// to the one of field that in given $row
	// @TODO Stop using this function. Elements within subforms should initialize themselves
	$.cotizacionDetalleRepeatable.prototype.fixScripts = function($row){
		// fix media field
		$row.find('a[onclick*="jInsertFieldValue"]').each(function(){
				var $el = $(this),
				inputId = $el.siblings('input[type="text"]').attr('id'),
				$select = $el.prev(),
				oldHref = $select.attr('href');
			// update the clear button
			$el.attr('onclick', "jInsertFieldValue('', '" + inputId + "');return false;")
			// update select button
			$select.attr('href', oldHref.replace(/&fieldid=(.+)&/, '&fieldid=' + inputId + '&'));
		});
	};

	// defaults
	$.cotizacionDetalleRepeatable.defaults = {
		// button selector for "add" action, must be unique per nested subform!
		btAdd: ".group-add",
		// button selector for "remove" action, must be unique per nested subform!
		btRemove: ".group-remove",
		// button selector for "move" action, must be unique per nested subform!
		btMove: ".group-move",
		// minimum repeating
		minimum: 0,
		// maximum repeating
		maximum: 10,
		// selector for the repeatable element inside the main container,
		// must be unique per nested subform!
		repeatableElement: ".subform-repeatable-group",
		// selector for the row template element with URL-encoded template inside it,
		// must *NOT* be unique per nested subform!
		rowTemplateSelector: 'template.subform-repeatable-template-section',
		// container for rows, same as main container by default
		rowsContainer: null
	};

	$.fn.cotizacionDetalleRepeatable = function(options){
		return this.each(function(){
			var options = options || {},
				data = $(this).data();

			if(data.cotizacionDetalleRepeatable){
				// Alredy initialized, nothing to do here
				return;
			}

			for (var p in data) {
				// check options in the element
				if (data.hasOwnProperty(p)) {
					options[p] = data[p];
				}
			}

			var inst = new $.cotizacionDetalleRepeatable(this, options);
			$(this).data('cotizacionDetalleRepeatable', inst);
		});
	};

	// initialise all available on load and again within any added row
	$(function ($) {
		initSubform();
		var $document = $(document);
		$document.on('subform-row-add', initSubform);
		//$document.on('subform-row-remove-after', window.loadProducts);

		function initSubform (event, container) {
			$(container || document).find('div.cotizaciondetalle-repeatable').cotizacionDetalleRepeatable();
			//window.loadProducts();
		}
	});

	window.addProduct = function (product) {
		if (!window.cotizacionDetalles) {
			window.cotizacionDetalles = [];
		}

		if (window.hasProduct(product.id_producto)){
			return true;
		}

		window.cotizacionDetalles.push(product);

		window.addRowProduct(product);

		window.loadProducts();

		return true;
	}

	window.removeProduct = function (id) {
		var index = window.cotizacionDetalles.findIndex(function(product) {
			return product.id_producto == id;
		});
		
		if (index >= 0) {
			window.removeRowProduct(id); 

			window.loadProducts();
			// Luego de eliminar la tr se ejecuta el evento subform-row-remove-after
			// que ejecuta a su vez window.loadProducts
			//window.cotizacionDetalles.splice(index, 1);
			return true;
		}

		return false;
	}

	window.hasProduct = function (id) {
		var index = window.cotizacionDetalles.findIndex(function(product) {
			return product.id_producto == id;
		});

		return index >= 0;
	}

	window.getProducts = function () {
		return window.cotizacionDetalles ? window.cotizacionDetalles : [];
	}

	/**
	 * Leo los trs y obtengo el objecto {id_producto: 1, nombre: ''...
	 */
	window.loadProducts = function () {
		var cotizacionDetalles = [];

		jQuery('.cotizaciondetalle-repeatable table tbody tr').each(function(i, row) {

			if (!cotizacionDetalles[i]) {
				cotizacionDetalles[i] = {};
			}
			
			jQuery(row).find('td').each(function(j, td) {
				td = jQuery(td);
				var fieldName = td.data('fieldname');
				if (!fieldName) {
					return false;
				}
				var fieldValue = td.find('input,select').val();
				cotizacionDetalles[i][fieldName] = fieldValue;		
			});
		});

		window.cotizacionDetalles = cotizacionDetalles;

		jQuery(document).trigger('products-loaded', window.cotizacionDetalles);
	}

	window.addRowProduct = function(product) {
		var $cotizacionDetalle = jQuery('input[name="jform[cotizaciondetalle]"]').parent().find('div.cotizaciondetalle-repeatable');
		var subform = $cotizacionDetalle.cotizacionDetalleRepeatable();
		var row = jQuery(subform.data().cotizacionDetalleRepeatable.addRow());

		row.attr('data-parent-id_product', product.id_producto);

		// Seteo el id_producto en la fila (row)
		var $input = row.find('input[name$="[id_producto]"]');
		$input.val(product.id_producto);
		$input.parent().attr('data-value', product.id_producto);
		//$input.parent().parent().parent().attr('data-value', product.id_producto);

		var inputs = row.find('input, select');

		for (var i = 0, l = inputs.length; l > i; i++) {
			var input = inputs[i];
			var parts = input.id.split('__');
			var fieldName = parts[parts.length - 1];

			if (fieldName.toLowerCase() == 'id') {
				continue;
			}

			var fieldValue = product[fieldName];
			if (fieldValue) {
				input.value = fieldValue;
			}
		}
	}

	window.removeRowProduct = function (idProducto) {	
		var $cotizacionDetalle = jQuery('input[name="jform[cotizaciondetalle]"]').parent().find('div.cotizaciondetalle-repeatable');
		/*var $td = $cotizacionDetalle.find('td[data-fieldname="id_producto"][data-value="' + idProducto + '"]');
		var $tr = $td.parent();*/

		var $tr = $cotizacionDetalle.find('tr[data-parent-id_product='+idProducto+']');
		
		var subform = $cotizacionDetalle.cotizacionDetalleRepeatable();
		subform.data().cotizacionDetalleRepeatable.removeRow($tr[0]);
	}



})(jQuery);

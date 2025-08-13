/**
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(function() {
	"use strict";

	/**
	 * Process modal fields in parent.
	 *
	 * @param   string  fieldPrefix  The fields to be updated prefix.
	 * @param   string  id           The new id for the item.
	 * @param   string  title        The new title for the item.
	 * @param   string  catid        Future usage.
	 * @param   object  object       Future usage.
	 * @param   string  url          Future usage.
	 * @param   string  language     Future usage.
	 *
	 * @return  boolean
	 *
	 * @since   3.7.0
	 */
	window.processModalProductoParent = function (fieldName, fieldPrefix, id, codigoSap, nombre, marca, precio, stock)
	{
		var fieldId = document.getElementById(fieldPrefix + '_id'), fieldTitle = document.getElementById(fieldPrefix + '_name');

		// Default values.
		id       = id || '';
		codigoSap    = codigoSap || '';
		nombre    = nombre || '';
		marca      = marca || '';
		precio = precio || '';
		stock   = stock || '';

		if (id)
		{
			/*var ids = fieldId.value.split(',').filter(function(_id) {return _id > 0});
			ids.push(id);
			fieldId.value    = ids.join(',');*/

			fieldTitle.value = codigoSap;

			if (document.getElementById(fieldPrefix + '_select'))
			{
				//jQuery('#' + fieldPrefix + '_select').addClass('hidden');
			}
			if (document.getElementById(fieldPrefix + '_new'))
			{
				jQuery('#' + fieldPrefix + '_new').addClass('hidden');
			}
			if (document.getElementById(fieldPrefix + '_edit'))
			{
				jQuery('#' + fieldPrefix + '_edit').removeClass('hidden');
			}
			if (document.getElementById(fieldPrefix + '_clear'))
			{
				//jQuery('#' + fieldPrefix + '_clear').removeClass('hidden');
			}
			if (document.getElementById(fieldPrefix + '_propagate'))
			{
				jQuery('#' + fieldPrefix + '_propagate').removeClass('hidden');
			}

			jQuery('#' + fieldPrefix + '_alert').addClass('hidden');
			var $table = jQuery('#' + fieldPrefix + '_table');
			$table.removeClass('hidden');
			$table.find('tbody').append(`
				<tr id="${fieldPrefix}_row_${id}">
					<td class="center">
						${codigoSap}
					</td>
					<td>
						${nombre}
					</td>
					<td class="small hidden-phone">
						${marca}
					</td>
					<td class="nowrap small hidden-phone">
						${precio}
					</td>
					<td class="center hidden-phone">
						${stock}
					</td>
					<td class="center hidden-phone">
						<button class="btn btn-default btn-remove" onclick="this.parentNode.parentNode.remove();">Eliminar</button>
						<input type="hidden" name="${fieldName}" value="${id}">
					</td>
				</tr>
			`);
		}
		else
		{
			//fieldId.value    = '';
			fieldTitle.value = fieldId.getAttribute('data-text');

			if (document.getElementById(fieldPrefix + '_select'))
			{
				jQuery('#' + fieldPrefix + '_select').removeClass('hidden');
			}
			if (document.getElementById(fieldPrefix + '_new'))
			{
				jQuery('#' + fieldPrefix + '_new').removeClass('hidden');
			}
			if (document.getElementById(fieldPrefix + '_edit'))
			{
				jQuery('#' + fieldPrefix + '_edit').addClass('hidden');
			}
			if (document.getElementById(fieldPrefix + '_clear'))
			{
				jQuery('#' + fieldPrefix + '_clear').addClass('hidden');
			}
			if (document.getElementById(fieldPrefix + '_propagate'))
			{
				jQuery('#' + fieldPrefix + '_propagate').addClass('hidden');
			}

			jQuery('#' + fieldPrefix + '_alert').removeClass('hidden');
			jQuery('#' + fieldPrefix + '_table').addClass('hidden');
		}

		if (fieldId.getAttribute('data-required') == '1')
		{
			document.formvalidator.validate(fieldId);
			document.formvalidator.validate(fieldTitle);
		}

		return false;
	}

	/**
	 * Process select modal fields in child.
	 *
	 * @param   string  itemType     The item type (Article, Contact, etc).
	 * @param   string  fieldPrefix  The fields to be updated prefix.
	 * @param   string  id           The new id for the item.
	 * @param   string  title        The new title for the item.
	 * @param   string  catid        Future usage.
	 * @param   object  object       Future usage.
	 * @param   string  url          Future usage.
	 * @param   string  language     Future usage.
	 *
	 * @return  boolean
	 *
	 * @since   3.7.0
	 */
	window.processModalProductoSelect = function(itemType, fieldName, fieldPrefix, id, codigoSap, nombre, marca, precio, stock) {
		window.processModalProductoParent(fieldName, fieldPrefix, id, codigoSap, nombre, marca, precio, stock);
		//jQuery('#ModalSelect' + itemType + '_' + fieldPrefix).modal('hide');

		return false;
	}
})();
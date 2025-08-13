/**
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(function () {
	"use strict";

	document.addEventListener('DOMContentLoaded', function () {
		// Get the elements
		var rows = document.querySelectorAll('.select-row');

		window.parent.loadProducts();

		var productsSelected = window.parent.getProducts();

		for (var i = 0, l = rows.length; l > i; i++) {
			var tds = rows[i].querySelectorAll('td');

			var idProduct = rows[i].getAttribute('data-id');

			var isSelected = productsSelected.findIndex(function (product) {
				return product.id_producto == idProduct;
			}) >= 0;

			if (isSelected) {
				rows[i].classList.add('success');
			}

			for (var j = 0; j < tds.length; j++) {
				// Listen for click event
				tds[j].addEventListener('click', function (event) {
					event.preventDefault();

					//window.parent.loadProducts();

					var id = event.target.parentNode.getAttribute('data-id'),
						codigo_sap = event.target.parentNode.getAttribute('data-codigo_sap'),
						nombre = event.target.parentNode.getAttribute('data-nombre'),
						marca = event.target.parentNode.getAttribute('data-marca'),
						precio = event.target.parentNode.getAttribute('data-precio'),
						stock = event.target.parentNode.getAttribute('data-stock');
						
					var product = { id_producto: id, codigo_sap, nombre, marca, precio, stock, cantidad: 1, subtotal: precio };

					// Si existe lo elimino
					if (window.parent.hasProduct(product.id_producto)) {
						window.parent.removeProduct(id);
						this.parentNode.classList.remove('success');
						return true;
					}

					// como no existe, lo agrego.
					if (window.parent.addProduct(product)) {
						this.parentNode.classList.add('success');
					}

					//var functionName = event.target.parentNode.getAttribute('data-function');


					//window.parent[functionName](id, codigo_sap, nombre, marca, precio, stock);

					//Joomla.JText.load({ success: Joomla.JText._('COM_SABULLVIAL_FIELD_PRODUCTO_SUCCESS_TITLE') });
					//var message = Joomla.JText._('COM_SABULLVIAL_FIELD_PRODUCTO_SUCCESS_MESSAGE').replace('%s', nombre);
					//Joomla.renderMessages({ 'success': [message] });
				})

			}
		}
	});
})();
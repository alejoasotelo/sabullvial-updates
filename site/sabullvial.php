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

JLoader::register('SabullvialHelper', JPATH_COMPONENT_ADMINISTRATOR . '/helpers/sabullvial.php');
JLoader::register('SabullvialButtonsHelper', JPATH_COMPONENT_ADMINISTRATOR . '/helpers/sabullvialbuttons.php');
JLoader::register('SabullvialCotizacionesHelper', JPATH_COMPONENT_ADMINISTRATOR . '/helpers/sabullvialcotizaciones.php');
JLoader::register('PriceHelper', JPATH_COMPONENT_ADMINISTRATOR . '/helpers/price.php');

// Get an instance of the controller prefixed by HelloWorld
$controller = JControllerLegacy::getInstance('Sabullvial');

// Perform the Request task
$controller->execute(JFactory::getApplication()->input->getCmd('task'));

// Redirect if set by the controller
$controller->redirect();

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
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;

// Access check: is this user allowed to access the backend of this component?
if (!Factory::getUser()->authorise('core.manage', 'com_sabullvial')) {
    throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'));
}

require_once __DIR__ . '/vendor/autoload.php';

// Require helper file
JLoader::register('SabullvialHelper', JPATH_COMPONENT . '/helpers/sabullvial.php');
JLoader::register('SabullvialButtonsHelper', JPATH_COMPONENT . '/helpers/sabullvialbuttons.php');
JLoader::register('SabullvialCotizacionesHelper', JPATH_COMPONENT . '/helpers/sabullvialcotizaciones.php');
JLoader::register('PriceHelper', JPATH_COMPONENT . '/helpers/price.php');

$app = Factory::getApplication();

// Get an instance of the controller prefixed by HelloWorld
$controller = BaseController::getInstance('Sabullvial');

// Perform the Request task
$controller->execute($app->input->get('task'));

// Redirect if set by the controller
$controller->redirect();

// Si composer esta en modo dev, se agrega el hotreloader
if (class_exists('HotReloader\HotReloader') && $app->input->get('format') != 'json') {
    ob_start();
    new HotReloader\HotReloader('//localhost/administrator/components/com_sabullvial/phrwatcher.php');
    $content = ob_get_clean();
    Factory::getDocument()->addScriptDeclaration(str_replace(['<script>', '</script>'], '', $content));
}

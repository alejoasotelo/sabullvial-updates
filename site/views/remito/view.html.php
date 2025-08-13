<?php
/**
 * @package     Sabullvial.Site
 * @subpackage  com_sabullvial
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Remito View
 *
 * @since  0.0.1
 */
class SabullvialViewRemito extends JViewLegacy
{
    protected $captchaEnabled = false;

    /**
     * Display the Remito view.
     *
     * @param string $tpl the name of the template file to parse; automatically searches through the template paths
     *
     * @return void
     */
    public function display($tpl = null)
    {
        $app = JFactory::getApplication();

        $this->state = $this->get('State');
        $this->item = $this->get('Item');
        $this->form = $this->get('Form');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode('<br />', $errors));

            return false;
        }

        if (Factory::getUser()->get('guest')) {
            $app = Factory::getApplication();
            $return = base64_encode(Uri::getInstance()->toString());
            $app->redirect(Route::_('index.php?option=com_users&view=login&return='.$return, false));
        }

        if (!$this->hasPermission()) {
            JError::raiseError(500, JText::_('COM_SABULLVIAL_REMITO_ERROR_NO_PERMISSION'));
            return false;
        }

        $this->captchaEnabled = false;

        // Load the parameters.
        $appParams = $app->getParams();

        $captchaSet = $appParams->get('captcha', $app->get('captcha', '0'));

        foreach (JPluginHelper::getPlugin('captcha') as $plugin) {
            if ($captchaSet === $plugin->name) {
                $this->captchaEnabled = true;
                break;
            }
        }

        $this->_prepareDocument();

        // Display the template
        parent::display($tpl);
    }

    protected function hasPermission()
    {
        $vendedor = SabullvialHelper::getVendedor();

        if (!$vendedor) {
            return false;
        }

        return $vendedor->get('modificar.remito.estado.qr');
    }

    /**
     * Prepares the document
     *
     * @return  void
     */
    protected function _prepareDocument()
    {
        $this->document->setMetadata('robots', 'noindex, nofollow');

        if (!$this->item->entregado) {
            HTMLHelper::script('com_sabullvial/libs/dropzone.min.js', ['version' => '5.9.3', 'relative' => true]);
        }
    }
}

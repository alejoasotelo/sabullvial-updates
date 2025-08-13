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

use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * HojaDeRuta View
 *
 * @since  0.0.1
 */
class SabullvialViewHojaDeRuta extends JViewLegacy
{
    /**
     * View form
     *
     * @var         form
     */
    protected $form = null;

    /**
     * Display the HojaDeRuta view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        // Get the Data
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode('<br />', $errors));

            return false;
        }


        // Set the toolbar
        $this->addToolBar();

        // Display the template
        parent::display($tpl);

        // Set the document
        $this->setDocument();
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function addToolBar()
    {
        $input = JFactory::getApplication()->input;
        $layout = $input->get('layout', 'edit');

        // Hide Joomla Administrator Main menu
        $input->set('hidemainmenu', true);

        $isNew = ($this->item->id == 0);

        if ($layout == 'edit') {
            $title = JText::_('COM_SABULLVIAL_HOJA_DE_RUTA_MANAGER_' . ($isNew ? 'NEW' : 'EDIT'));
        } else {
            $title = JText::_('COM_SABULLVIAL_HOJA_DE_RUTA_MANAGER_VIEW');
        }

        JToolbarHelper::title($title, 'file-2');

        if ($layout == 'edit' && $this->item->published == SabullvialModelHojaDeRuta::ESTADO_CREADO) {
            JToolbarHelper::apply('hojaderuta.apply');
            JToolbarHelper::save('hojaderuta.save');

            $bar = Toolbar::getInstance('toolbar');
            $bar->appendButton('Confirm', 'JTOOLBAR_ANULATE_MSG', 'remove', 'JTOOLBAR_ANULATE', 'hojaderuta.unpublish', false);
        }

        JToolbarHelper::cancel(
            'hojaderuta.cancel',
            $isNew && $layout == 'edit' ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE'
        );
    }

    /**
     * Method to set up the document properties
     *
     * @return void
     */
    protected function setDocument()
    {
        $isNew = ($this->item->id < 1);

        $input = JFactory::getApplication()->input;
        $layout = $input->get('layout', 'edit');

        if ($layout == 'edit') {
            $title = JText::_('COM_SABULLVIAL_HOJA_DE_RUTA_' . ($isNew ? 'CREATING' : 'EDITING'));
        } else {
            $title = JText::_('COM_SABULLVIAL_HOJA_DE_RUTA_MANAGER_VIEW');
        }
        $document = JFactory::getDocument();
        $document->setTitle($title);

        $document->addScriptOptions('com_sabullvial', [
            'url' => JUri::base(),
            'token' => JSession::getFormToken(),
        ]);

        HTMLHelper::stylesheet('com_sabullvial/hojaderuta.min.css', ['version' => 'auto', 'relative' => true]);

        Text::script('COM_SABULLVIAL_HOJA_DE_RUTA_CONFIRM_DELETE_IMAGE');
    }
}

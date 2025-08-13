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

/**
 * HojaDeRutaRemito View
 *
 * @since  0.0.1
 */
class SabullvialViewHojaDeRutaRemito extends JViewLegacy
{
    /**
     * View form
     *
     * @var         form
     */
    protected $form = null;

    /**
     * Display the HojaDeRutaRemito view
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

        // Hide Joomla Administrator Main menu
        $input->set('hidemainmenu', true);

        $isNew = ($this->item->id == 0);

        if ($isNew) {
            $title = JText::_('COM_SABULLVIAL_HOJA_DE_RUTA_REMITO_MANAGER_NEW');
        } else {
            $title = JText::_('COM_SABULLVIAL_HOJA_DE_RUTA_REMITO_MANAGER_EDIT');
        }

        JToolbarHelper::title($title, 'hojaderutaremito');
        JToolbarHelper::apply('hojaderutaremito.apply');
        JToolbarHelper::save('hojaderutaremito.save');
        JToolbarHelper::save2new('hojaderutaremito.save2new');
        JToolbarHelper::cancel(
            'hojaderutaremito.cancel',
            $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE'
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
        $document = JFactory::getDocument();
        $document->setTitle($isNew ? JText::_('COM_SABULLVIAL_HOJA_DE_RUTA_REMITO_CREATING') :
                JText::_('COM_SABULLVIAL_HOJA_DE_RUTA_REMITO_EDITING'));
    }
}

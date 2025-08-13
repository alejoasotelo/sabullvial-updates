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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * Tarea View
 *
 * @since  0.0.1
 */
class SabullvialViewTarea extends JViewLegacy
{
    /**
     * View form
     *
     * @var         form
     */
    protected $form = null;

    /**
     * Display the Tarea view
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
            $title = JText::_('COM_SABULLVIAL_TAREA_MANAGER_NEW');
        } else {
            $title = JText::_('COM_SABULLVIAL_TAREA_MANAGER_EDIT');
        }

        JToolbarHelper::title($title, 'tarea');
        JToolbarHelper::apply('tarea.apply');
        JToolbarHelper::save('tarea.save');
        JToolbarHelper::save2new('tarea.save2new');

        if ($this->canEditCotizacion()) {
            JToolbarHelper::custom('tarea.editCotizacion', 'edit', '', 'COM_SABULLVIAL_TAREA_EDIT_COTIZACION', false, false);
        }

        $return = $input->get('return', '', 'base64');

        if (empty($return)) {
            JToolbarHelper::cancel('tarea.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
        } else {
            JToolbarHelper::cancel('tarea.cancel', 'JTOOLBAR_BACK');
        }
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
        $document->setTitle($isNew ? JText::_('COM_SABULLVIAL_TAREA_CREATING') :
                JText::_('COM_SABULLVIAL_TAREA_EDITING'));

        $vendedor = SabullvialHelper::getVendedor();

        $allowEditEstadoCotizacion = false;
        if ($vendedor->get('tipo') == SabullvialHelper::USUARIO_TIPO_ADMINISTRADOR) {
            $allowEditEstadoCotizacion = true;
        } else {
            $cotizacion = $this->getModel()->getCotizacion($this->item->id_cotizacion);
            $canCancelar = SabullvialButtonsHelper::canCancelar((int) $cotizacion->id_estadocotizacion);
            $allowEditEstadoCotizacion = $canCancelar;
        }

        $document->addScriptOptions('com_sabullvial.tarea', [
            'assignedIdCotizacion' => $this->form->getValue('id_cotizacion'),
            'url' => JUri::base(),
            'token' => JSession::getFormToken(),
            'allowEditEstadoCotizacion' => $allowEditEstadoCotizacion,
        ]);

        JHtml::_('jquery.framework');
        HTMLHelper::script('com_sabullvial/tarea.js', ['version' => 'auto', 'relative' => true]);

        Text::script('JTOOLBAR_APPLY');
    }

    /**
     * Devuelve true si el usuario puede editar la cotización
     *
     * @return boolean
     */
    protected function canEditCotizacion()
    {
        if (!isset($this->item->id_cotizacion) || !$this->item->id_cotizacion) {
            return false;
        }

        $canDo = JHelperContent::getActions('com_sabullvial', 'cotizacion');

        return $canDo->get('core.edit') || $canDo->get('core.edit.own');
    }
}

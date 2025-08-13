<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use SabullvialHelper;

FormHelper::loadFieldClass('list');

/**
 * Form Field to load a list of content authors
 *
 * @since  3.2
 */
class AutorField extends \JFormFieldList
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  3.2
     */
    public $type = 'Autor';

    protected $context = '#__content';

    /**
     * Cached array of the category items.
     *
     * @var    array
     * @since  3.2
     */
    protected static $options = [];

    public function setup($element, $value, $group = null)
    {
        // Call the parent setup method.
        parent::setup($element, $value, $group);

        $this->context = empty($element['context']) ? 'content' : $element['context'];

        return true;
    }

    /**
     * Method to get the options to populate list
     *
     * @return  array  The field option objects.
     *
     * @since   3.2
     */
    protected function getOptions()
    {
        // Accepted modifiers
        $hash = md5($this->element);

        if (!isset(static::$options[$hash])) {
            static::$options[$hash] = parent::getOptions();

            $db = Factory::getDbo();

            // Construct the query
            $query = $db->getQuery(true)
                ->select('DISTINCT u.id AS value, concat(u.name, " (", u.username, ")") AS text')
                ->from('#__users AS u')
                ->join('INNER', '#__' . $this->context . ' AS c ON (c.created_by = u.id)')
                // ->group('u.id, u.name')
                ->order('u.name');

            // Setup the query
            $db->setQuery($query);

            // Return the result
            if ($options = $db->loadObjectList()) {
                static::$options[$hash] = array_merge(static::$options[$hash], $options);
            }
        }

        return static::$options[$hash];
    }
}

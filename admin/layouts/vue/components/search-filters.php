<?php

/**
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or exit;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Registry\Registry;

extract($displayData);


if (!isset($fields) || !is_array($fields))
{
    echo 'No hay fields para mostrar';
    return;
}

HTMLHelper::script('com_sabullvial/tools.js', ['version' => 'auto', 'relative' => true]);
HTMLHelper::script('com_sabullvial/components/search-filters.js', ['version' => 'auto', 'relative' => true]);

Factory::getDocument()->addStyleDeclaration('
    .search-filters-container .search-filters-field {
        display: inline-block;
        margin: 0 5px 5px 0;
    }

    .search-filters-container .search-filters-field .input-prepend{
        margin-bottom: 0;
    }
');
?>

<script type="text/x-template" id="search-filters-template">
    <div class="search-filters-container">
        <?php foreach ($fields as $field): ?>
            <?php if ('search' != $field->fieldname): ?>
                <?php $field->onchange = "onChangeField(this, '$field->id', '$field->fieldname')"; ?>
                    
                <div class="search-filters-field">
                    <?php echo $field->input; ?>
                </div>

            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</script>
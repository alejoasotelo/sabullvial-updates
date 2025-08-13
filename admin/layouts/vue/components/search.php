<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\HTML\HTMLHelper;

$data = $displayData;

// Receive overridable options
$data = !empty($data) ? $data : [];

if (is_array($data)) {
    $data = new Registry($data);
}

$name = $data->get('name', 'name_' . uniqid());

HTMLHelper::script('com_sabullvial/tools.js', ['version' => 'auto', 'relative' => true]);
HTMLHelper::script('com_sabullvial/components/search.js', ['version' => 'auto', 'relative' => true]);
?>

<script type="text/x-template" id="search-template">
    <div>
        <label for="filter_<?php echo $name;?>" class="element-invisible"><?php echo JText::_('JSEARCH_FILTER'); ?></label>
        <div class="btn-wrapper input-append">
            <input type="text" v-model="query" @keyup="onKeyUp" @keypress.enter="search"
                class="js-stools-search-string" id="filter_<?php echo $name; ?>" name="filter_<?php echo $name; ?>"  placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>" autocomplete="off">
            <button type="button" class="btn hasTooltip" title="Buscar" aria-label="Buscar" @click="search">
                <span class="icon-search" aria-hidden="true"></span>
            </button>
        </div>
        <div v-if="showBtnFilters" class="btn-wrapper">
            <button type="button" class="btn hasTooltip" :class="{'btn-primary': showFilters}" title="Filtrar la lista de elementos" @click="toggleFilters">Herramientas de búsqueda <span class="caret"></span></button>
        </div>
        <div class="btn-wrapper">
            <button type="button" class="btn hasTooltip" title="Limpiar" @click="clear"><?php echo JText::_('JSEARCH_FILTER_CLEAR');?></button>
        </div>
    </div>
</script>
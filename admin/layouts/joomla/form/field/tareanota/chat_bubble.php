<?php
/**
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or exit;

extract($displayData);

if (!isset($author) || !isset($text) || !isset($created))
{
    throw new Exception('Invalid chat bubble data: "author", "text" and "created" date are required.');
}
?>

<div class="chat-bubble" <?php echo (!empty($id) ? 'id="' . $id . '"' : ''); ?>>
    <div class="chat-bubble-author"><?php echo $author; ?></div>
    <div class="chat-bubble-body">
        <button type="button" class="chat-bubble-btn-delete hasTooltip" title="<?php echo JText::_('JTOOLBAR_DELETE'); ?>">
            <span class="icon-cancel"></span>
        </button>

        <?php echo $text; ?>
    </div>
    <div class="chat-bubble-info">
        <?php echo $created == '{{created}}' ? $created : SabullvialHelper::formatToChatDatetime($created); ?>
    </div>
</div>
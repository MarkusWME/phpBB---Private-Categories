<?php

/**
 * @author    MarkusWME <markuswme@pcgamingfreaks.at>
 * @copyright 2016 MarkusWME
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 * @version   1.1.0
 */

if (!defined('IN_PHPBB'))
{
    exit;
}

if (empty($lang) || !is_array($lang))
{
    $lang = array();
}

// Merging language data with the other language data
$lang = array_merge($lang, array(
    'PCGF_PRIVATECATEGORIES_ERROR_VIEW_NOT_ALLOWED' => 'You are not allowed to view this post!',
    // New language data since version 1.1.0
    'PCGF_PRIVATECATEGORIES_ALLOWED_GROUPS'         => 'Allowed groups',
    'PCGF_PRIVATECATEGORIES_ALLOWED_USERS'          => 'Allowed users',
    'PCGF_PRIVATECATEGORIES_ADD_VIEWER'             => 'Allow user or group',
    'PCGF_PRIVATECATEGORIES_DELETE_VIEWER'          => 'Remove user or group',
    'PCGF_PRIVATECATEGORIES_ADD_ERROR'              => 'An error occurred while adding the permission!',
    'PCGF_PRIVATECATEGORIES_REMOVE_MESSAGE'         => 'Click the entry you want to remove. To abort click here.',
    'PCGF_PRIVATECATEGORIES_REMOVE_CONFIRMATION'    => 'Do you really want to remove the view permission?',
));

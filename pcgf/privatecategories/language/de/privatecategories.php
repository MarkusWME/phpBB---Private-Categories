<?php

/**
 * @author    MarkusWME <markuswme@pcgamingfreaks.at>
 * @copyright 2016 MarkusWME
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
    'PCGF_PRIVATECATEGORIES_ERROR_VIEW_NOT_ALLOWED' => 'Du hast nicht die Berechtigung dieses Thema zu sehen!',
));

<?php

/**
 * @author    MarkusWME <markuswme@pcgamingfreaks.at>
 * @copyright 2016 MarkusWME
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 * @version   1.0.0
 */

if (!defined('IN_PHPBB'))
{
    exit;
}

if (empty($lang) || !is_array($lang))
{
    $lang = array();
}

// Add language data for permissions
$lang = array_merge($lang, array(
    'ACL_CAT_PCGF_PRIVATECATEGORIES'          => 'Private Categories',
    'ACL_F_PCGF_PRIVATECATEGORIES_SEE_ALL'    => 'Can see all private topics',
    'ACL_F_PCGF_PRIVATECATEGORIES_INVITE_ALL' => 'Can make every topic in a private category visible for specified users and groups',
    'ACL_F_PCGF_PRIVATECATEGORIES_INVITE_OWN' => 'Can make his own topics in private categories visible for specified users and groups',
    'ACL_F_PCGF_PRIVATECATEGORIES_REMOVE_ALL' => 'Can remove users and groups from all private topics',
    'ACL_F_PCGF_PRIVATECATEGORIES_REMOVE_OWN' => 'Can remove users and groups from his own private topics',
));

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

// Merging language data for the ACP with the other language data
$lang = array_merge($lang, array(
    'ACP_PCGF_PRIVATECATEGORIES'                          => 'Private Categories',
    'ACP_PCGF_PRIVATECATEGORIES_INHERITED'                => 'Inherited private categories',
    'ACP_PCGF_PRIVATECATEGORIES_EXPLAIN'                  => 'Here it is possible to define which categories are private and how subcategories should be handled.',
    'ACP_PCGF_PRIVATECATEGORIES_SETTINGS'                 => 'Settings',
    'ACP_PCGF_PRIVATECATEGORIES_AUTO_INHERITANCE'         => 'Automatic inheritance',
    'ACP_PCGF_PRIVATECATEGORIES_AUTO_INHERITANCE_EXPLAIN' => 'Defines if subcategories also will get set to private.',
    'ACP_PCGF_PRIVATECATEGORIES_NOTHING_FOUND'            => 'There is no private category so far.',
    'ACP_PCGF_PRIVATECATEGORIES_INHERITED_NOTHING_FOUND'  => 'There are no inherited private categories so far.',
    'ACP_PCGF_PRIVATECATEGORIES_MAKE_PRIVATE'             => 'Change to private category',
    'ACP_PCGF_PRIVATECATEGORIES_CLEAN_PERMISSIONS'        => 'Cleanup database',
    'ACP_PCGF_PRIVATECATEGORIES_SETTING_PRIVATE_FAILED'   => 'The category could not be set to be private!',
    'ACP_PCGF_PRIVATECATEGORIES_UNSET_PRIVATE_FAILED'     => 'The category could not be set to be a public one!',
));

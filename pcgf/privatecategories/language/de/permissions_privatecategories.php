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
    'ACL_CAT_PCGF_PRIVATECATEGORIES'          => 'Private Kategorien',
    'ACL_F_PCGF_PRIVATECATEGORIES_SEE_ALL'    => 'Kann alle privaten Kategorien sehen',
    'ACL_F_PCGF_PRIVATECATEGORIES_INVITE_ALL' => 'Kann Beiträge in privaten Kategorien für jeden sichtbar schalten',
    'ACL_F_PCGF_PRIVATECATEGORIES_INVITE_OWN' => 'Kann seine eigenen Beiträge in privaten Kategorien für jeden sichtbar schalten',
    'ACL_F_PCGF_PRIVATECATEGORIES_REMOVE_ALL' => 'Kann Benutzer und Gruppen aus allen privaten Beiträgen entfernen',
    'ACL_F_PCGF_PRIVATECATEGORIES_REMOVE_OWN' => 'Kann Benutzer und Gruppen aus seinen eigenen privaten Beiträgen entfernen',
));

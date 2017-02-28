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
    'PCGF_PRIVATECATEGORIES_ERROR_VIEW_NOT_ALLOWED' => 'Du hast nicht die Berechtigung dieses Thema zu sehen!',
    // New language data since version 1.1.0
    'PCGF_PRIVATECATEGORIES_ALLOWED_GROUPS'         => 'Zugelassene Gruppen',
    'PCGF_PRIVATECATEGORIES_ALLOWED_USERS'          => 'Zugelassene Benutzer',
    'PCGF_PRIVATECATEGORIES_ADD_VIEWER'             => 'Benutzer oder Gruppe zulassen',
    'PCGF_PRIVATECATEGORIES_DELETE_VIEWER'          => 'Benutzer oder Gruppe entfernen',
    'PCGF_PRIVATECATEGORIES_ADD_ERROR'              => 'Es ist ein Fehler beim Hinzufügen der Berechtigung aufgetreten!',
    'PCGF_PRIVATECATEGORIES_REMOVE_MESSAGE'         => 'Klicken Sie auf den Eintrag den Sie entfernen möchten. Um abzubrechen klicke hier.',
    'PCGF_PRIVATECATEGORIES_REMOVE_CONFIRMATION'    => 'Wollen Sie die Anzeigeberechtigung wirklich entfernen?',
));

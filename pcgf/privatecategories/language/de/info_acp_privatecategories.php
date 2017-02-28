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

// Merging language data for the ACP with the other language data
$lang = array_merge($lang, array(
    'ACP_PCGF_PRIVATECATEGORIES'                          => 'Private Kategorien',
    'ACP_PCGF_PRIVATECATEGORIES_INHERITED'                => 'Geerbte private Kategorien',
    'ACP_PCGF_PRIVATECATEGORIES_EXPLAIN'                  => 'Hier kannst du festlegen welche Kategorien als privat gekennzeichnet werden sollen und wie Subkategorien zu behandeln sind.',
    'ACP_PCGF_PRIVATECATEGORIES_SETTINGS'                 => 'Einstellungen',
    'ACP_PCGF_PRIVATECATEGORIES_AUTO_INHERITANCE'         => 'Automatische Vererbung',
    'ACP_PCGF_PRIVATECATEGORIES_AUTO_INHERITANCE_EXPLAIN' => 'Legt fest ob Subkategorien ebenfalls als privat gesetzt werden.',
    'ACP_PCGF_PRIVATECATEGORIES_NOTHING_FOUND'            => 'Es wurden bisher noch keine privaten Kategorien gesetzt.',
    'ACP_PCGF_PRIVATECATEGORIES_INHERITED_NOTHING_FOUND'  => 'Es gibt keine Kategorie, welche durch Vererbung zur privaten Kategorie geworden ist.',
    'ACP_PCGF_PRIVATECATEGORIES_MAKE_PRIVATE'             => 'Zur privaten Kategorie machen',
    'ACP_PCGF_PRIVATECATEGORIES_CLEAN_PERMISSIONS'        => 'Datenbank bereinigen',
    'ACP_PCGF_PRIVATECATEGORIES_SETTINGS_SAVED'           => 'Die Einstellungen wurden erfolgreich gespeichert!',
    'ACP_PCGF_PRIVATECATEGORIES_SETTING_PRIVATE_FAILED'   => 'Die Kategorie konnte nicht als privat markiert werden!',
    'ACP_PCGF_PRIVATECATEGORIES_UNSET_PRIVATE_FAILED'     => 'Die Kategorie konnte nicht zur Standard-Kategorie geändert werden!',
    // New language data since version 1.1.0
    'ACP_PCGF_PRIVATECATEGORIES_CLEANUP_EXECUTED'         => 'Die Bereinigung der Berechtigungen wurde ausgeführt!',
));

<?php

/**
 * @author    MarkusWME <markuswme@pcgamingfreaks.at>
 * @copyright 2016, 2017 MarkusWME
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 */

namespace pcgf\privatecategories\migrations;

use phpbb\db\migration\migration;

/** @version 1.1.0 */
class release_1_0_0 extends migration
{
    const PRIVATECATEGORY_PERMISSION_TABLE = 'private_category_permissions';

    /**
     * Function that checks if the extension has been effectively installed
     *
     * @access public
     * @since  1.0.0
     * @return bool If the extension has been installed effectively
     */
    public function effectively_installed()
    {
        return isset($this->config['pcgf_privatecategories_auto_inheritance']);
    }

    /**
     * Function for building the dependency tree
     *
     * @access public
     * @since  1.0.0
     * @return array Dependency data
     */
    static public function depends_on()
    {
        return array('\phpbb\db\migration\data\v31x\v311');
    }

    /**
     * Function that adds neccessary tables and columns to the database
     *
     * @access public
     * @since  1.0.0
     * @return array Schema update data array
     */
    public function update_schema()
    {
        return array(
            'add_columns' => array(
                FORUMS_TABLE => array(
                    'private_category' => array('BOOL', 0),
                ),
            ),
            'add_tables'  => array(
                $this->table_prefix . self::PRIVATECATEGORY_PERMISSION_TABLE => array(
                    'COLUMNS'     => array(
                        'topic'    => array('UINT', 0),
                        'user'     => array('UINT', 0),
                        'is_group' => array('BOOL', 0),
                    ),
                    'PRIMARY_KEY' => 'topic, user, is_group',
                ),
            ),
        );
    }

    /**
     * Function that reverts the database updates
     *
     * @access public
     * @since  1.0.0
     * @return array Schema revert data array
     */
    public function revert_schema()
    {
        return array(
            'drop_columns' => array(
                FORUMS_TABLE => array(
                    'private_category',
                ),
            ),
            'drop_tables'  => array(
                $this->table_prefix . self::PRIVATECATEGORY_PERMISSION_TABLE,
            ),
        );
    }

    /**
     * Function that updates module data and adds needed permissions
     *
     * @access public
     * @since  1.0.0
     * @return array Update information array
     */
    public function update_data()
    {
        return array(
            array('config.add', array('pcgf_privatecategories_auto_inheritance', true)),
            array('permission.add', array('f_pcgf_privatecategories_see_all', false)),
            array('permission.add', array('f_pcgf_privatecategories_invite_all', false)),
            array('permission.add', array('f_pcgf_privatecategories_invite_own', false)),
            array('permission.add', array('f_pcgf_privatecategories_remove_all', false)),
            array('permission.add', array('f_pcgf_privatecategories_remove_own', false)),
            array('module.add', array(
                'acp',
                'ACP_MANAGE_FORUMS',
                array(
                    'module_basename' => '\pcgf\privatecategories\acp\privatecategories_module',
                    'modes'           => array('configuration'),
                ),
            )),
        );
    }
}

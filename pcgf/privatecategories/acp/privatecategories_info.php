<?php

/**
 * @author    MarkusWME <markuswme@pcgamingfreaks.at>
 * @copyright 2016 MarkusWME
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 */

namespace pcgf\privatecategories\acp;

/** @version 1.0.0 */
class privatecategories_info
{
    /**
     * Function that returns module information data
     *
     * @access public
     * @since  1.0.0
     * @return array The module information array
     */
    public function module()
    {
        return array(
            'filename' => '\pcgf\privatecategories\acp\privatecategories_module',
            'title'    => 'ACP_PCGF_PRIVATECATEGORIES',
            'modes'    => array(
                'configuration' => array(
                    'title' => 'ACP_PCGF_PRIVATECATEGORIES',
                    'auth'  => 'ext_pcgf/privatecategories',
                    'cat'   => array('ACP_MANAGE_FORUMS'),
                ),
            ),
        );
    }
}

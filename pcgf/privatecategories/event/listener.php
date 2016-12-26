<?php

/**
 * @author    MarkusWME <markuswme@pcgamingfreaks.at>
 * @copyright 2016 MarkusWME
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 */

namespace pcgf\privatecategories\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/** @version 1.0.0 */
class listener implements EventSubscriberInterface
{
    /** @var \phpbb\db\driver\factory $db Database object */
    protected $db;

    /** @var \phpbb\config\config $config Configuration object */
    protected $config;

    /** @var \phpbb\template\template $template Template object */
    protected $template;

    /**
     * Constructor for the extension listener
     *
     * @access public
     * @since  1.0.0
     *
     * @param \phpbb\db\driver\factory $db       Database object
     * @param \phpbb\config\config     $config   Configuration object
     * @param \phpbb\template\template $template Template object
     *
     * @return object Listener object of the extension
     */
    public function __construct(\phpbb\db\driver\factory $db, \phpbb\config\config $config, \phpbb\template\template $template)
    {
        $this->db = $db;
        $this->config = $config;
        $this->template = $template;
    }

    /**
     * Function that returns the subscribed events
     *
     * @access public
     * @since  1.0.0
     * @return array The subscribed event list
     */
    static public function getSubscribedEvents()
    {
        return array(
            'core.permissions' => 'add_permission_data',
        );
    }

    /**
     * Function that adds permission data to the ACP module when it is needed
     *
     * @access public
     * @since  1.0.0
     *
     * @param $event The event object which contains specific information
     *
     * @return null
     */
    public function add_permission_data($event)
    {
        $event['categories'] = array_merge($event['categories'], array('ACL_CAT_PCGF_PRIVATECATEGORIES' => 'ACL_CAT_PCGF_PRIVATECATEGORIES'));
        $event['permissions'] = array_merge($event['permissions'], array(
            'f_pcgf_privatecategories_see_all'    => array(
                'lang' => 'ACL_F_PCGF_PRIVATECATEGORIES_SEE_ALL',
                'cat'  => 'ACL_CAT_PCGF_PRIVATECATEGORIES',
            ),
            'f_pcgf_privatecategories_invite_all' => array(
                'lang' => 'ACL_F_PCGF_PRIVATECATEGORIES_INVITE_ALL',
                'cat'  => 'ACL_CAT_PCGF_PRIVATECATEGORIES',
            ),
            'f_pcgf_privatecategories_invite_own' => array(
                'lang' => 'ACL_F_PCGF_PRIVATECATEGORIES_INVITE_OWN',
                'cat'  => 'ACL_CAT_PCGF_PRIVATECATEGORIES',
            ),
            'f_pcgf_privatecategories_remove_all' => array(
                'lang' => 'ACL_F_PCGF_PRIVATECATEGORIES_REMOVE_ALL',
                'cat'  => 'ACL_CAT_PCGF_PRIVATECATEGORIES',
            ),
            'f_pcgf_privatecategories_remove_own' => array(
                'lang' => 'ACL_F_PCGF_PRIVATECATEGORIES_REMOVE_OWN',
                'cat'  => 'ACL_CAT_PCGF_PRIVATECATEGORIES',
            ),
        ));
    }
}

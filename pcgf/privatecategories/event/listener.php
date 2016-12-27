<?php

/**
 * @author    MarkusWME <markuswme@pcgamingfreaks.at>
 * @copyright 2016 MarkusWME
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 */

namespace pcgf\privatecategories\event;

use pcgf\privatecategories\acp\privatecategories_module;
use pcgf\privatecategories\migrations\release_1_0_0;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

global $phpbb_root_path;
require_once($phpbb_root_path . 'ext/pcgf/privatecategories/includes/functions.php');

/** @version 1.0.0 */
class listener implements EventSubscriberInterface
{
    /** @var \phpbb\db\driver\factory $db Database object */
    protected $db;

    /** @var \phpbb\config\config $config Configuration object */
    protected $config;

    /** @var \phpbb\template\template $template Template object */
    protected $template;

    /** @var  \phpbb\user $user The user object */
    protected $user;

    /** @var  \phpbb\auth\auth $auth The authentication object */
    protected $auth;

    /**
     * Constructor for the extension listener
     *
     * @access public
     * @since  1.0.0
     *
     * @param \phpbb\db\driver\factory $db       Database object
     * @param \phpbb\config\config     $config   Configuration object
     * @param \phpbb\template\template $template Template object
     * @param \phpbb\user              $user     The user object
     * @param \phpbb\auth\auth         $auth     The authentication object
     *
     * @return object Listener object of the extension
     */
    public function __construct(\phpbb\db\driver\factory $db, \phpbb\config\config $config, \phpbb\template\template $template, \phpbb\user $user, \phpbb\auth\auth $auth)
    {
        $this->db = $db;
        $this->config = $config;
        $this->template = $template;
        $this->user = $user;
        $this->auth = $auth;
        // Load language data of the extension
        $this->user->add_lang_ext('pcgf/privatecategories', 'privatecategories');
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
            'core.permissions'                  => 'add_permission_data',
            'core.viewtopic_get_post_data'      => 'check_topic_view_permissions',
            'core.viewforum_modify_topics_data' => 'check_category_view_permissions',
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

    /**
     * Function that handles the user view permission check
     *
     * @access public
     * @since  1.0.0
     *
     * @param $event The event object which contains specific information
     *
     * @return null
     */
    public function check_topic_view_permissions($event)
    {
        if (has_permissions($this->user->data['user_id'], $event['forum_id'], $event['topic_id'], $event['topic_data']['topic_poster'], $this->auth, $this->db))
        {
            return;
        }
        // The user wants to see a topic he is not allowed to see, so we prevent that by throwing an error
        trigger_error($this->user->lang['PCGF_PRIVATECATEGORIES_ERROR_VIEW_NOT_ALLOWED']);
    }

    public function check_category_view_permissions($event)
    {
        $topics = $event['topic_list'];
        for ($i = 0; $i < $event['total_topic_count']; $i++)
        {
            if (!has_permissions($this->user->data['user_id'], $event['rowset'][$topics[$i]]['forum_id'], $event['rowset'][$topics[$i]]['topic_id'], $event['rowset'][$topics[$i]]['topic_poster'], $this->auth, $this->db))
            {
                // If the user doesn't have the permissions to view the topic don't show it on the list
                unset($topics[$i--]);
            }
        }
        $event['topic_list'] = $topics;
    }
}

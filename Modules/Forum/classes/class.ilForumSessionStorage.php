<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilForumSessionStorage
 * @author Nadia Matuschek <nmatuschek@databay.de>t
 */
class ilForumSessionStorage
{
    private $session = [];
    
    /**
     * ilForumSessionStorage constructor.
     */
    public function __construct($session_key = 'frm_selected_post')
    {
        $this->session = \ilSession::get($session_key) ?? [];
    }
    
    public function get($thread_id)
    {
        $this->session = \ilSession::get('frm_selected_post') ?? [];
        
        if($thread_id > 0 && isset($this->session[$thread_id])) return $this->session[$thread_id];
        return 0;
    }
    
    public function set($thread_id, $post_id)
    {
        $this->session[$thread_id] = $post_id;
        \ilSession::set('frm_selected_post', $this->session);
    }
}

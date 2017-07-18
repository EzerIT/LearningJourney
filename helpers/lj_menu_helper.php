<?php

function lj_menu_add(&$head, &$content) {

    $CI =& get_instance();
    $CI->lang->load('lj_menu', $CI->language);
    $CI->load->model('Mod_users');

    $ix = count($head);
    $head [] = $CI->lang->line('learning_journey');
    $myID = $CI->Mod_users->my_id();
        
    if ($CI->Mod_users->is_teacher() == false)
    {
        $content[$ix][] = anchor(site_url('/lj/LJ_grading_system'), $CI->lang->line('grading_system'));
    }
    
    $content[$ix][] = anchor(site_url('/lj/LJ_gradebook_teacher'), $CI->lang->line('gradebook'));
    //$content[$ix][] = anchor(site_url('lj/LJ_international_ranking'), $CI->lang->line('international_ranking'));

    /*$content[$ix][] = anchor(site_url('logbook'), $this->lang->line('logbook'));
      $content[$ix][] = anchor(site_url('graph'), $this->lang->line('graph'));
      $content[$ix][] = anchor(site_url('badges'), $this->lang->line('badges'));*/
        
  }


<?php

/* Copyright 2013 by Ezer IT Consulting. All rights reserved. E-mail: claus@ezer.dk */

/**
 * This is a collection of functions to manipulate the statistics database.
 */
class Mod_LJ_useranalysis extends CI_Model {

    public function __construct()
    {
                $this->load->database();
    }
    
    public function get_userName($userid)
    {
        $query = $this->db->query("SELECT first_name, last_name FROM {PRE}user u WHERE id=$userid"); 
        return $query->result(); 
    }
    
   public function get_analysis($userid, $quizid) {
    
       $query = $this->db->query("SELECT txt, correct, value, answer FROM {PRE}sta_requestfeature f                         
                                    JOIN {PRE}sta_question as que ON f.questid=que.id
                                    JOIN {PRE}sta_quiz as q ON que.quizid=q.id
                                    JOIN {PRE}sta_quiztemplate as qt ON qt.id=q.templid
                                    WHERE f.userid=$userid AND qt.id=$quizid ORDER BY correct DESC");
       return $query->result(); 
   }
    
    
  
}





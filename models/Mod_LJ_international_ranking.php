<?php

/* Copyright 2013 by Ezer IT Consulting. All rights reserved. E-mail: claus@ezer.dk */

/**
 * This is a collection of functions to manipulate the statistics database.
 */
class Mod_LJ_international_ranking extends CI_Model {

    public function __construct()
    {
                $this->load->database();
    }
    
    public function get_users()
    {
    	$query = $this->db->query("SELECT u.id,first_name,last_name FROM {PRE}user u WHERE NOT isadmin ORDER BY last_name"); 
    	return $query->result();	
    }
    
    public function get_learningData($u)
    {
    	$query = $this->db->query("SELECT q.userid, q.id, SUM(rf.correct) cor, SUM(q.end-q.start) as sumdur, SUM(1-correct)                            AS wrong
							       FROM {PRE}sta_quiz AS q
								   JOIN {PRE}sta_question AS quest ON q.id = quest.quizid AND q.userid = quest.userid
								   JOIN {PRE}sta_requestfeature AS rf ON quest.id = rf.questid AND quest.userid = rf.userid
							       WHERE q.userid=$u->id
						           GROUP BY q.userid, q.id");
						           
		return $query->result();
    }
    
    public function get_duration($u)
    {
    	$query = $this->db->query("SELECT SUM(q.end-q.start) duration
							         FROM {PRE}sta_quiz AS q
							         WHERE q.userid=$u->id");
						          
		return $query->result();    
    }
    
    
}





<?php

/* Copyright 2013 by Ezer IT Consulting. All rights reserved. E-mail: claus@ezer.dk */

/**
 * This is a collection of functions to manipulate the statistics database.
 */
class Mod_LJ_userlogbook extends CI_Model {

    public function __construct()
    {
                $this->load->database();
    }
    
    public function get_userName($userid)
    {
        $query = $this->db->query("SELECT first_name, last_name FROM {PRE}user u WHERE id=$userid"); 
        return $query->result(); 
    }

    public function get_featuresAndCorrectness($userid)
    {
       $query = $this->db->query("SELECT userid, questid, name, value, answer, correct FROM {PRE}sta_requestfeature WHERE                                    userid=$userid"); 
       return $query->result(); 
    }
    
    public function get_correctForFeature_teacher($userid)
    {
         $this->db->query("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");
         $query = $this->db->query("SELECT name, SUM(correct) AS cor, SUM(1-correct) AS wrong, (end-start)/sum(correct) AS                              sec_per_cor FROM {PRE}sta_requestfeature AS rf
                                    JOIN {PRE}sta_question as q ON rf.questid=q.id
                                    JOIN {PRE}sta_quiz as qz ON qz.id=q.quizid
                                    WHERE rf.userid=$userid AND (grading=1 OR grading IS NULL)
                                    GROUP BY name"); 
         
        return $query->result(); 
    }
    
    public function get_correctForFeature_student($userid)
    {
         $this->db->query("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");
         $query = $this->db->query("SELECT name, SUM(correct) AS cor, SUM(1-correct) AS wrong, (end-start)/sum(correct) AS                              sec_per_cor FROM {PRE}sta_requestfeature AS rf
                                    JOIN {PRE}sta_question as q ON rf.questid=q.id
                                    JOIN {PRE}sta_quiz as qz ON qz.id=q.quizid
                                    WHERE rf.userid=$userid
                                    GROUP BY name"); 
         
        return $query->result();   
    }
    
    public function set_grade($user, $start, $grade, $feature) {
        
        $this->db->query("INSERT INTO {PRE}sta_gradingfeature (userid, start, grade, feature) VALUES ($user, $start, $grade, $feature)");
                            
    }
    
     public function get_maxTimestampPath($u, $path)
    {
        $query = $this->db->query("SELECT MAX(grade) grade FROM {PRE}sta_gradingpath WHERE userid=$u AND feature LIKE $path GROUP BY grade DESC LIMIT 1"); 

        return $query->result(); 
    }
    
    public function get_lastDatePath($u, $path)
    {
        
        $query = $this->db->query("SELECT * FROM {PRE}sta_gradingpath WHERE userid=$u AND feature LIKE $path ORDER BY id DESC LIMIT 1"); 

        return $query->result(); 
    }
    
    public function get_maxTimestampFeature($u, $feature)
    {
        $feature = '"'. $feature .'"' ;
        $query = $this->db->query("SELECT MAX(grade) grade FROM {PRE}sta_gradingfeature WHERE userid=$u AND feature=$feature GROUP BY grade DESC LIMIT 1"); 

        return $query->result(); 
    }
    
    public function get_lastDateFeature($u, $feature)
    {
        $feature = '"'. $feature .'"' ;
        $query = $this->db->query("SELECT * FROM {PRE}sta_gradingfeature WHERE userid=$u AND feature=$feature ORDER BY id DESC LIMIT 1"); 

        return $query->result(); 
    }
    
    public function get_allQuizzes_teacher($u){
        
        $query = $this->db->query("SELECT qt.id AS quiztempl, pathname, SUM(correct) AS correct, sum(1-correct) AS wrong,                               COUNT(correct) AS number_of_answers, (end- start)/sum(correct) AS sec_per_cor
                                    FROM {PRE}sta_quiztemplate qt JOIN {PRE}sta_quiz q ON qt.userid=q.userid AND qt.id=q.templid
                                    JOIN {PRE}sta_question que ON q.userid=que.userid AND q.id=que.quizid
                                    JOIN {PRE}sta_requestfeature rf ON que.userid=rf.userid AND que.id=rf.questid
                                    WHERE q.userid=$u AND (grading=1 OR grading IS NULL)
                                    GROUP BY pathname, quiztempl");
        
        return $query->result();

    }
    
    public function get_allQuizzes_student($u){
        
        $query = $this->db->query("SELECT qt.id AS quiztempl, pathname, SUM(correct) AS correct, sum(1-correct) AS wrong,                               COUNT(correct) AS number_of_answers, (end- start)/sum(correct) AS sec_per_cor
                                    FROM {PRE}sta_quiztemplate qt JOIN {PRE}sta_quiz q ON qt.userid=q.userid AND qt.id=q.templid
                                    JOIN {PRE}sta_question que ON q.userid=que.userid AND q.id=que.quizid
                                    JOIN {PRE}sta_requestfeature rf ON que.userid=rf.userid AND que.id=rf.questid
                                    WHERE q.userid=$u
                                    GROUP BY pathname, quiztempl");
        
        return $query->result();

    }
    
     public function set_gradepath($user, $start, $grade, $feature) {
        
        $this->db->query("INSERT INTO {PRE}sta_gradingpath (userid, start, grade, feature) VALUES ($user, $start, $grade, $feature)");
                            
    }
    
  
}



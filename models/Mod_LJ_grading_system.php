<?php

class Mod_LJ_grading_system extends CI_Model {

    public function __construct()
    {
                $this->load->database();
    }
    
    public function get_userResults_students($u)
    {
    	$query = $this->db->query("SELECT q.grading, q.userid, q.id, SUM(rf.correct) cor, SUM(q.end-q.start)                                             As sumdur, SUM(1-correct) AS wrong, (end- start)/sum(correct) AS sec_per_cor
                                    FROM {PRE}sta_quiz AS q
                                    JOIN {PRE}sta_question AS quest ON q.id = quest.quizid AND q.userid = quest.userid
                                    JOIN {PRE}sta_requestfeature AS rf ON quest.id = rf.questid AND quest.userid = rf.userid
                                    WHERE q.userid=$u->id
						            GROUP BY q.grading, q.userid, q.id");
						           
		return $query->result();
    }

    public function get_userForStudent($myID)
    {
     	$query = $this->db->query("SELECT classname, first_name, last_name, user.id FROM {PRE}user AS user
									JOIN {PRE}userclass AS userclass ON userclass.userid=user.id
									JOIN {PRE}class AS class ON class.id=userclass.classid
									WHERE NOT isadmin AND user.id=$myID ");
		
		return $query->result();
	}
    
    public function get_duration($u)
    {
    	$query = $this->db->query("SELECT SUM(q.end-q.start) duration
							         FROM {PRE}sta_quiz AS q
							         WHERE q.userid=$u->id");
						          
		return $query->result();    
    }
    
    public function get_start($u)
    {
       $query = $this->db->query("SELECT q.userid, q.id, q.start AS start
							       FROM {PRE}sta_quiz AS q
								    JOIN {PRE}sta_question AS quest ON q.id = quest.quizid AND q.userid = quest.userid
								    JOIN {PRE}sta_requestfeature AS rf ON quest.id = rf.questid AND quest.userid = rf.userid
							      WHERE q.userid=$u->id");

        return $query->result();
        
    }
    
    public function set_grade($user, $start, $grade) {
        
        $this->db->query("INSERT INTO {PRE}sta_grading (userid, start, grade)
                            VALUES ($user, $start, $grade)");
                            
    }
    
    public function get_grade($u){
        
       $query = $this->db->query("SELECT DISTINCT g.userid, g.id, g.start AS start, g.grade as grade
							       FROM {PRE}sta_grading AS g
							       WHERE g.userid=$u->id"); 
        return $query->result(); 
    }
    
    public function get_maxTimestamp($u)
    {
        $query = $this->db->query("SELECT MAX(grade) grade FROM {PRE}sta_grading WHERE userid=$u GROUP BY grade DESC LIMIT 1"); 

        return $query->result(); 
    }
    
    public function get_lastDate($u){
       $query = $this->db->query("SELECT * FROM {PRE}sta_grading WHERE userid=$u ORDER BY id DESC LIMIT 1"); 

        return $query->result(); 
    }
    
    public function get_suspiciousData($u){
        
        $unanswered = '"*Unanswered*"';
        
        $query = $this->db->query("SELECT COUNT(answer) as answer
                                    FROM {PRE}sta_requestfeature AS rf
                                    JOIN {PRE}sta_question AS quest ON quest.id=rf.questid AND quest.userid=rf.userid
                                    JOIN {PRE}sta_quiz AS q ON q.id=quest.quizid AND q.userid = quest.userid
                                    WHERE q.userid=$u->id AND answer=$unanswered");
        
        return $query->result(); 
    }
    
      public function get_sumAnswer($u){
        
        $query = $this->db->query("SELECT COUNT(answer) as answer
                                    FROM {PRE}sta_requestfeature AS rf
                                    JOIN {PRE}sta_question AS quest ON quest.id=rf.questid AND quest.userid=rf.userid
                                    JOIN {PRE}sta_quiz AS q ON q.id=quest.quizid AND q.userid = quest.userid
                                    WHERE q.userid=$u->id");
        
        return $query->result(); 
    }
    
    public function get_data_cron(){

        $query = $this->db->query("SELECT q.grading, q.userid, q.id, SUM(rf.correct)cor, SUM(q.end-q.start) As sumdur,                                   SUM(1-correct) AS wrong, (end-start)/sum(correct) AS sec_per_cor
                                    FROM {PRE}sta_quiz AS q
                                    JOIN {PRE}sta_question AS quest ON q.id = quest.quizid AND q.userid = quest.userid
                                    JOIN {PRE}sta_requestfeature AS rf ON quest.id = rf.questid AND quest.userid = rf.userid
                                    GROUP BY q.grading, q.userid, q.id");
        return $query->result(); 
        
        //For implementation of gradebook for the teachers
        //WHERE grading=1 OR grading IS NULL
    }

    public function get_usersCron()
    {
    	$query = $this->db->query("SELECT classname, first_name, last_name, user.id AS id FROM {PRE}user AS user
									JOIN {PRE}userclass AS userclass ON userclass.userid=user.id
									JOIN {PRE}class AS class ON class.id=userclass.classid"); 
    	
    	return $query->result();  
    }
    
    public function delete_all_cron()
    {
        $query = $this->db->query("DELETE FROM {PRE}sta_grading_system");
    }
    
     public function add_toDB($class, $answers, $percent, $grade, $time, $progress, $suspicious, $name, $userid) {
        
        $class = '"' . $class . '"';
        $time = '"' . $time . '"';
        $grade = '"' . $grade . '"';
        $progress = '"' . $progress . '"';
        $suspicious = '"' . $suspicious . '"';
        $name = '"' . $name . '"';
         
        $this->db->query("INSERT INTO {PRE}sta_grading_system (userclass, answers, percent, grade, trainingtime, progress, suspicious, name, userid) VALUES ($class, $answers, $percent, $grade, $time, $progress, $suspicious, $name, $userid)");
                                                 
    }
 
    public function select_from_cron_teacher($myID)
    {
        $query = $this->db->query("SELECT * FROM {PRE}sta_grading_system AS gs
									JOIN {PRE}userclass AS userclass ON userclass.userid=gs.userid
									JOIN {PRE}class AS class ON class.id=userclass.classid
                                    WHERE ownerid=$myID"); 
        return $query->result();  
    }
    
    public function select_from_cron_student($myID)
    {
        $query = $this->db->query("SELECT * FROM {PRE}sta_grading_system AS gs
									JOIN {PRE}userclass AS userclass ON userclass.userid=gs.userid
									JOIN {PRE}class AS class ON class.id=userclass.classid
                                    WHERE gs.userid=$myID"); 
        return $query->result();  
    }
   
}







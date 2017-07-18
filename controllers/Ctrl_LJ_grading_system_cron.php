<?php

error_reporting(E_WARNING | E_NOTICE); 

class Ctrl_LJ_grading_system_cron extends MY_Controller {

    public function index() {
        $this->get_gradingSystem();
	}
	
    public function get_grading_system_cron()
    {
    	$this->load->model('Mod_LJ_grading_system');
    	$this->load->model('Mod_users');
    	$this->load->helper('lj_Student');
    	$this->lang->load('lj_grading_system', $this->language);

        $this->Mod_LJ_grading_system->delete_all_cron();
        $users = $this->Mod_LJ_grading_system->get_usersCron();
			   	
    	$objectarray = array();

        foreach($users as $u)
        {
            $pi = new Student;
            $pi->set_userclass($u->classname);
            $pi->set_firstname($u->first_name);
            $pi->set_lastname($u->last_name);
            $pi->set_userid($u->id);
            $duration = $this->Mod_LJ_grading_system->get_duration($u);
            $graders = $this->Mod_LJ_grading_system->get_userResults_students($u);
            $start = $this->Mod_LJ_grading_system->get_start($u);
            $suspiciousData = $this->Mod_LJ_grading_system->get_suspiciousData($u);
            $sumData = $this->Mod_LJ_grading_system->get_sumAnswer($u);
            $sumRight = 0;
            $sumWrong = 0;

            //Einfügen: Wenn die Anzahl, der unbeantworteten quizze größer 40% ist, wird keine duration gesetzt.
            //Das ganze wird durch eine separate query getestet und dann hier als über die Duration entschieden.
            
            foreach($graders as $row)
            {
              $sumRight = $sumRight + $row->cor;
              $sumWrong = $sumWrong + $row->wrong;
              $pi->arraysecpercor($row->sec_per_cor);
            }
            
                            
            $pi->grading($sumRight, $sumWrong);
            
            foreach($sumData as $row){
                $count = $row->answer;
            }
            
            foreach($suspiciousData as $row){
                $avg = $row->answer;
                if ($count > 0){
                    $avgquery = $avg / $count;
                    $pi->set_suspiciousData($avgquery);   
                }
            }
            
            foreach($duration as $row){
              $pi->set_duration($row->duration);
            }
            
            unset($duration);
            
            foreach($start as $row){
                $pi->set_start($row->start);
            }

            

            $start = $pi->get_start();
            $grade = $pi->get_grade();
            $specificGrade = $grade[0];
            $user = $u->id;
            
            $this->set_grade($user, $start, $specificGrade);
            
            $gradeProgress = $this->compare_grade($user);
            $pi->set_progress($gradeProgress);
 
            $objectarray [] = $pi;
            
            $objectarray = array_map("unserialize", array_unique(array_map("serialize", $objectarray)));
        }

        foreach ($objectarray as $u)
        {
            $class = $u->get_userclass();
            $firstname = $u->get_firstname();
            $lastname = $u->get_lastname();
            $duration = $u->get_duration();
            $grade = $u->get_grade();
            $progress = $u->get_progress();
            $userid = $u->get_userid();
            $suspiciousData = $u->get_suspiciousData();
            $name = $firstname . " " . $lastname;
            
            $duration = $this->calc_duration($duration);
            
            echo $duration . "\n";
                
            $this->Mod_LJ_grading_system->add_toDB($class, $grade[2], round($grade[0], 2), $grade[1], $duration, $progress, $suspiciousData, $name, $userid);
        }

    }
    
    public function set_grade($user, $start, $grade)
    {
        if ($start != NULL)
                settype($start, "integer");
            
        settype($user, "integer");

        if ($grade>0)
        {
            $this->Mod_LJ_grading_system->set_grade($user, $start, $grade);
        }
    }
    
    public function compare_grade($user)
    {
        $max = $this->Mod_LJ_grading_system->get_maxTimestamp($user);
        $current = $this->Mod_LJ_grading_system->get_lastDate($user);
        
        $maximum = 0;
        $checkMax = false;
        $checkCur = false;
        $grade = '';
        
        foreach($max as $valueMax){
            
            $temp = $valueMax->grade;
            
            if($temp > $maximum){
                $maximum = $temp; 
                $checkMax = true;
            }
            
        }
        
        foreach($current as $valueCur){
            $currentGrade= $valueCur->grade;
            $checkCur = true;
        }
        
        
        if($checkCur == true && $checkMax == true) {
           
            $finalMax = $maximum;
            $finalCur = $currentGrade;
            
            if($finalMax > $finalCur)
            {
                $grade = "Student needs support";
            }
            else
            {
               $grade = "Student is progressing"; 
            }
        }
        
        $checkMax = false;
        $checkCur = false;
        
        $finalMax = 0;
        $finalCur = 0;
        
        return $grade;
         
    }
    
    public function calc_duration($duration)
    {
        $hours = floor($duration / 3600);
        $mins = floor($duration / 60 % 60);
        $secs = floor($duration % 60);
        
        if($hours < 10)
            $hours = "0" . $hours;
        if($mins < 10)
            $mins = "0" . $mins;
        if($secs < 10)
            $secs = "0" . $secs;
        
        return $duration = $hours . ":" . $mins . ":" . $secs;
    }
    
    

}


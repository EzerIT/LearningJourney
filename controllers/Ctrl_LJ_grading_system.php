<?php

error_reporting(E_WARNING | E_NOTICE); 

class Ctrl_LJ_grading_system extends MY_Controller {

    public function index() {
        $this->get_gradingSystem();
	}
	
    public function get_gradingSystem()
    {
    	$this->load->model('Mod_LJ_grading_system');
    	$this->load->model('Mod_users');
    	$this->load->helper('lj_Student');
    	$this->lang->load('lj_grading_system', $this->language);
        
        $myID = $this->Mod_users->my_id();
        
        $users = $this->Mod_LJ_grading_system->select_from_cron_student($myID); 
			   	
    	$objectarray = array();

        foreach($users as $u)
        {
            /*$pi = new Student;
            $pi->set_userclass($u->classname);
            $pi->set_firstname($u->first_name);
            $pi->set_lastname($u->last_name);
            $duration = $this->Mod_LJ_grading_system->get_duration($u);
            $graders = $this->Mod_LJ_grading_system->get_userResults($u);
            $start = $this->Mod_LJ_grading_system->get_start($u);
            $suspiciousData = $this->Mod_LJ_grading_system->get_suspiciousData($u);
            $sumData = $this->Mod_LJ_grading_system->get_sumAnswer($u);
            $sumRight = 0;
            $sumWrong = 0;
            
            $pi->set_userid($u->id);

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
            $pi->set_progress($gradeProgress);*/
 
            $objectarray [] = $u; 					 			
        }
        
        $data['data'] = $objectarray;

        $this->load->view('view_top1', array('title' => $this->lang->line('grading_title')));
        $this->load->view('view_top2');
        $this->load->view('view_menu_bar', array('langselect' => true));
        $center_text = $this->load->view('view_LJ_grading_system', array('data' => $objectarray), true); 
        $this->load->view('view_confirm_dialog');
        $left_text = $this->load->view('view_LJ_grading_left', array('name' => $this->Mod_users->my_name()), true);
        $this->load->view('view_main_page', array('left_title' => $this->lang->line('grading_title'),
                                                  'left' => $left_text,
                                                  'center' => $center_text));
        $this->load->view('view_bottom');

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
    
    

}


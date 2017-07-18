<?php

class Ctrl_LJ_userlogbook extends MY_Controller {
    
     public function __construct() {
        parent::__construct();
        $this->lang->load('lj_grading_logbook', $this->language);
        $this->load->model('Mod_users');
        $this->load->model('Mod_LJ_userlogbook');
        $this->load->helper('lj_Student');
    }
    
    public function index() {
        $this->logbook_for_user();
	}

    public function logbook_for_user() {
        

        $userid = $_GET['userid'];
        
        $objectarray = array();
        $pi = new Student;
        
        $pi->set_userid($userid);
        
        
        if ($this->Mod_users->is_teacher())
    	{
            $learningData = $this->Mod_LJ_userlogbook->get_correctForFeature_teacher($userid);
            $allQuizzes = $this->Mod_LJ_userlogbook->get_allQuizzes_teacher($userid);
    	}
        else
        {
            $learningData = $this->Mod_LJ_userlogbook->get_correctForFeature_student($userid);
            $allQuizzes = $this->Mod_LJ_userlogbook->get_allQuizzes_student($userid);
        }
        
        foreach($learningData as $d){
            
            $progressFeature = $this->compare_grade($userid, $d->name);
            $pi->set_grading_feature($d->name, $d->cor, $d->wrong, $progressFeature);
            $pi->arraysecpercor($d->sec_per_cor); 
        }
        
        foreach($allQuizzes as $d){
            
            $pathname = basename($d->pathname, ".3et");
            $pathname = "'%". $pathname ."%'";
            $progressPath = $this->compare_gradePath($userid, $pathname);
            $pi->set_grading_path($pathname, $d->correct, $d->wrong, $progressPath, $d->quiztempl);
            $pi->arraysecpercor($d->sec_per_cor);
        }
        
        $gradingarrPath = $pi->get_grading_path();
        
        foreach ($gradingarrPath as $gradePath){
            
            $path = '"'. $gradePath['feature'] .'"' ;
            $this->set_gradePath($userid, $gradePath['percent'], $path);
            
   
        }

        $gradingarr = $pi->get_grading_feature();
        
        foreach ($gradingarr as $grade){
            
            $feature = '"'. $grade['feature'] .'"' ;
            $this->set_grade($userid, $grade['percent'], $feature);
        }
  
        $objectarray[] = $pi; 
        
        $data['data'] = $objectarray;

        $this->load->view('view_top1', array('title' => $this->lang->line('grading_logbook_title')));
        $this->load->view('view_top2');
        $this->load->view('view_menu_bar', array('langselect' => true));

        //center_text weiter bearbeiten
        $center_text = $this->load->view('view_LJ_grading_logbook', array('data' => $objectarray), true); 
        $this->load->view('view_confirm_dialog');
        $left_text = $this->load->view('view_LJ_grading_logbook_left', array('name' => $this->Mod_users->my_name()), true);
        $this->load->view('view_main_page', array('left_title' => $this->lang->line('grading_logbook_title'),
                                                  'left' => $left_text,
                                                  'center' => $center_text));
        $this->load->view('view_bottom');
        
    }
    
     public function set_grade($userid, $grade, $feature)
     {
        if ($grade>0)
        {
            $start = time();
            $this->Mod_LJ_userlogbook->set_grade($userid, $start, $grade, $feature);
        }
     }
    
    public function set_gradePath($userid, $grade, $path)
    {
        if ($grade>0)
        {
            $start = time();
            $this->Mod_LJ_userlogbook->set_gradepath($userid, $start, $grade, $path);
        }
     }
    
     public function compare_grade($userid, $feature)
     {
        $max = $this->Mod_LJ_userlogbook->get_maxTimestampFeature($userid, $feature);
        $current = $this->Mod_LJ_userlogbook->get_lastDateFeature($userid, $feature);
        
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
            
            /*echo "Max: " . $finalMax . "\n";
            echo "Cur: " . $finalCur . "\n";*/
            
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
    
    public function compare_gradePath($userid, $path)
    {
        $max = $this->Mod_LJ_userlogbook->get_maxTimestampPath($userid, $path);
        $current = $this->Mod_LJ_userlogbook->get_lastDatePath($userid, $path);
        
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
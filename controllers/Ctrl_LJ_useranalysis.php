<?php

class Ctrl_LJ_useranalysis extends MY_Controller {
    
     public function __construct() {
        parent::__construct();
        $this->lang->load('lj_grading_analysis', $this->language);
        $this->load->model('Mod_users');
        $this->load->model('Mod_LJ_useranalysis');
        $this->load->helper('lj_student');
    }
    
    public function index() {
        $this->analysis_for_user();
	}

    public function analysis_for_user() {
        

        $userid = $_GET['userid'];
        $quizid= $_GET['quizid'];
        
        $objectarray = array();
        $pi = new Student;
        
        //$pi->set_userid($userid);

        $analysis = $this->Mod_LJ_useranalysis->get_analysis($userid, $quizid);
        $pi->set_analysis($analysis);
  
        $objectarray[] = $pi; 
        
        $data['data'] = $objectarray;

        $this->load->view('view_top1', array('title' => $this->lang->line('grading_analysis_title')));
        $this->load->view('view_top2');
        $this->load->view('view_menu_bar', array('langselect' => true));

        //center_text weiter bearbeiten
        $center_text = $this->load->view('view_LJ_grading_analysis', array('data' => $objectarray), true); 
        $this->load->view('view_confirm_dialog');
        $left_text = $this->load->view('view_LJ_grading_analysis_left', array('name' => $this->Mod_users->my_name()), true);
        $this->load->view('view_main_page', array('left_title' => $this->lang->line('grading_analysis_title'),
                                                  'left' => $left_text,
                                                  'center' => $center_text));
        $this->load->view('view_bottom');
  
    }
    
}
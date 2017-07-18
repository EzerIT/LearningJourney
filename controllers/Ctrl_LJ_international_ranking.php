<?php
class Ctrl_LJ_international_ranking extends MY_Controller {

    public function index() {
        $this->get_internationalRanking();
	}
	
    public function get_internationalRanking()
    {
    	$this->load->model('Mod_LJ_international_ranking');
    	$this->load->model('Mod_users');
    	$this->load->helper('lj_Student');
    	$this->lang->load('lj_international_ranking', $this->language);
    	$users = $this->Mod_LJ_international_ranking->get_users();
    	   	
    	$objectarray = array();
    	
    	foreach ($users as $u) 
    	{
    		$pi = new Student;
    		
    		$learningData = $this->Mod_LJ_international_ranking->get_learningData($u);
    		$duration = $this->Mod_LJ_international_ranking->get_duration($u);
    		
  			$pi->set_userid($u->id);
  			$pi->set_firstname($u->first_name);
  			$pi->set_lastname($u->last_name);
  			
  			foreach($learningData as $row)
  			{
  				$pi->set_arrayright($row->cor);
  				$pi->set_arraywrong($row->wrong);
  			}
  				
  				
  			foreach($duration as $row)
  				$pi->set_duration($row->duration);
  				
  				
  			$objectarray [] = $pi;
  		}
  			usort($objectarray, 'Student::compare_proficiency');
  				
			//$data['users'] = $objectarray;
			$this->load->view('view_top1', array('title' => $this->lang->line('ranking_title')));
            $this->load->view('view_top2');
            $this->load->view('view_menu_bar', array('langselect' => true));
    		$center_text = $this->load->view('view_LJ_international_ranking', array('data' => $objectarray), true); 
			$this->load->view('view_confirm_dialog');
            $left_text = $this->load->view('view_LJ_international_ranking_left', array('name' => $this->Mod_users->my_name()), true);
            $this->load->view('view_main_page', array('left_title' => $this->lang->line('ranking_title'),
            										  'left' => $left_text,
            										  'center' => $center_text));
            $this->load->view('view_bottom');
            
            	
                                                      
  		
  	}
}

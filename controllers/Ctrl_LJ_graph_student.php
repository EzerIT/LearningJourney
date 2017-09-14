<?php


class Ctrl_LJ_graph_student extends MY_Controller {
    public function __construct() {
        parent::__construct();

        $this->load->library('Lj_timeperiod');
    }

    public function index() {
        $this->student_time();
	}

    // Dummy validation function
    public function always_true($field) {
        return true;
    }

    public function student_time() {
    	$this->load->model('mod_users');
    	$this->load->model('mod_classes');
    	$this->load->model('mod_userclass');
    	$this->load->model('mod_statistics');

        try {
//            $this->db->set_dbprefix('bol_');

            $myid = $this->mod_users->my_id();
            $myclassids = $this->mod_userclass->get_classes_for_user($myid);
            $myclasses = $this->mod_classes->get_classes_by_ids($myclassids);
                
            $this->load->helper('form');
            $this->load->library('form_validation');

            $this->form_validation->set_data($_GET);

            $this->lj_timeperiod->set_validation_rules();
            $this->form_validation->set_rules('classid', '', 'callback_always_true');  // Dummy rule. At least one rule is required

			if ($this->form_validation->run()) {
                $this->lj_timeperiod->ok_dates();

                $classid = $this->input->get('classid');
                if (is_null($classid))
                    $classid = 0;
            }
            else {
                $this->lj_timeperiod->default_dates();

                $classid = 0;
            }
            
            // $classid==0 means ignore class information

            if ($classid>0) {
                if (!in_array($classid,$myclassids))
                    throw new DataException($this->lang->line('illegal_class_id'));

                $templates = $this->mod_statistics->get_templates_for_class_and_students((int)$classid,array($myid));
            }
            else
                $templates = $this->mod_statistics->get_templates_for_students(array($myid));

            $temp_id2path = $this->mod_statistics->get_pathnames_for_templids($templates);
                
            if (!empty($templates))
                $durations = $this->mod_statistics->get_quizzes_duration($templates,
                                                                         $this->lj_timeperiod->start_timestamp(),
                                                                         $this->lj_timeperiod->end_timestamp());
            else
                $durations = array();

            // How many weeks does the time cover?
            $minweek = $this->lj_timeperiod->start_week();
            $maxweek = $this->lj_timeperiod->end_week();

            // $total[23] will be the duration in week 23
            // $totaltemp[$templatename] will be the total time spent on template $templatename
            $total = array();
            $totaltemp = array();
            for ($w=$minweek; $w<=$maxweek; ++$w)
                $total[$w] = 0;

            foreach ($durations as $d) {
                $hours = $d->duration / 3600;
                $w = $this->lj_timeperiod->time_to_week((int)$d->start);
                $total[$w] += $hours;

                $templname = $temp_id2path[$d->templid];
                if (!isset($totaltemp[$templname]))
                    $totaltemp[$templname] = $hours;
                else
                    $totaltemp[$templname] += $hours;
            }

            
            // VIEW:
            $this->load->view('view_top1', array('title' => 'Student Graphs',
                                                 'js_list' => array('RGraph/libraries/RGraph.common.core.js',
                                                                    'RGraph/libraries/RGraph.bar.js',
                                                                    'RGraph/libraries/RGraph.hbar.js',
                                                                    'RGraph/libraries/RGraph.common.dynamic.js',
                                                                    'RGraph/libraries/RGraph.common.tooltips.js',
                                                                    'RGraph/libraries/RGraph.common.key.js',
                                                                    'myapp/third_party/lj/js/datepicker_period.js')));
            $this->load->view('view_top2');
            $this->load->view('view_menu_bar', array('langselect' => true));
            
            $center_text = $this->load->view('view_LJ_graph_student_time', array('classid' => $classid,
                                                                                 'classlist' => $myclasses,
                                                                                 'userid' => $myid,
                                                                                 'start_date' => $this->lj_timeperiod->start_string(),
                                                                                 'end_date' => $this->lj_timeperiod->end_string(),
                                                                                 'total' => $total,
                                                                                 'totaltemp' => $totaltemp), true);


            
            $this->load->view('view_main_page', array('left_title' => 'Select a Period',
                                                      'left' => '<p>Use the two date fields to select a first
                                                                 and last date to view.</p>
                                                                 <p><b>Note: At most 26 weeks (6 months) of information
                                                                 can be shown at a time.</b></p>
                                                                 <p>Optionally, select the class for which you want
                                                                 data.</p>',
                                                      'center' => $center_text));
            $this->load->view('view_bottom');
        }
        catch (DataException $e) {
            $this->error_view($e->getMessage(), 'Student Graphs');
        }
    }
        

    public function view_quiz()
    {
		$this->load->model('mod_users');
		$this->load->model('mod_statistics');
 
		try {
            $this->load->helper('form');
            $this->load->library('form_validation');

            $this->form_validation->set_data($_GET);

            $this->lj_timeperiod->set_validation_rules();
            $this->form_validation->set_rules('templ', 'Template', 'required');
            $this->form_validation->set_rules('userid', 'User ID', 'required');

            $userid = $this->mod_users->my_id(); // Default value
            
			if ($this->form_validation->run()) {
                $this->lj_timeperiod->ok_dates();

                $templ = $this->input->get('templ');
                $userid = (int)$this->input->get('userid');

                if (!$this->mod_users->is_teacher() || $userid!=$this->mod_users->my_id())
                    throw new DataException($this->lang->line('illegal_user_id'));

                $templs = $this->mod_statistics->get_templids_for_pathname_and_user($templ, $userid);
                $res = $this->mod_statistics->get_score_by_date_user_templ($userid,
                                                                           $templs,
                                                                           $this->lj_timeperiod->start_timestamp(),
                                                                           $this->lj_timeperiod->end_timestamp());

                $status = empty($res) ? 0 : 1;  // 0=no data, 1=data
            }
            else {
                $this->lj_timeperiod->default_dates();

                $status = 2; // 2=Bad data
            }

            // VIEW:
            $this->load->view('view_top1', array('title' => 'Exercise Graphs',
                                                 'js_list' => array('RGraph/libraries/RGraph.common.core.js',
                                                                    'RGraph/libraries/RGraph.scatter.js',
                                                                    'RGraph/libraries/RGraph.common.dynamic.js',
                                                                    'RGraph/libraries/RGraph.common.tooltips.js',
                                                                    'RGraph/libraries/RGraph.common.key.js',
                                                                    'myapp/third_party/lj/js/datepicker_period.js')));

            $this->load->view('view_top2');
            $this->load->view('view_menu_bar', array('langselect' => true));
            
            $center_text = $this->load->view('view_LJ_graph_exercise', array('resall' => $res,
                                                                             'quiz' => $templ,
                                                                             'status' => $status,
                                                                             'userid' => $userid,
                                                                             'start_date' => $this->lj_timeperiod->start_string(),
                                                                             'end_date' => $this->lj_timeperiod->end_string(),
                                                                             'minweek' => $this->lj_timeperiod->start_week(),
                                                                             'maxweek' => $this->lj_timeperiod->end_week()), true);

            $this->load->view('view_main_page', array('left_title' => 'Select a Period',
                                                      'left' => '<p>TODO</p>',
                                                      'center' => $center_text));
            $this->load->view('view_bottom');
        }
        catch (DataException $e) {
            $this->error_view($e->getMessage(), 'Exercise Graphs');
        }
    }
  }


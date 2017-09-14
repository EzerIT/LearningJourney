<?php


class Ctrl_LJ_graph_student extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->helper('lj_date_helper');
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

            $this->form_validation->set_rules('start_date', 'Start date', 'trim|valid_date_check');
            $this->form_validation->set_rules('end_date', 'End date', 'trim|valid_date_check');
            $this->form_validation->set_rules('classid', '', 'callback_always_true');  // Dummy rule. At least one rule is required

			if ($this->form_validation->run()) {
                $period_start = decode_start_date($this->input->get('start_date'));
                $period_end = decode_end_date($this->input->get('end_date')) -1;  // -1 to turn exclusive time into inclusive

                // If period is longer than MAX_PERIOD, adjust the end date.
                $period_end = min($period_end, $period_start + MAX_PERIOD -1);

                $classid = $this->input->get('classid');
                if (is_null($classid))
                    $classid = 0;
            }
            else {
                $period_end = ((int)(time() / (24*3600)) + 1) * 24*3600;  // Midnight tonight
                $period_start = $period_end - MAX_PERIOD;
                --$period_end;  // Turn exclusive time into inclusive

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
                $durations = $this->mod_statistics->get_quizzes_duration($templates, $period_start, $period_end);
            else
                $durations = array();

            // How many weeks does the time cover?
            $minweek = time_to_week($period_start);
            $maxweek = time_to_week($period_end);

            // $total[23] will be the duration in week 23
            // $totaltemp[$templatename] will be the total time spent on template $templatename
            $total = array();
            $totaltemp = array();
            for ($w=$minweek; $w<=$maxweek; ++$w)
                $total[$w] = 0;

            foreach ($durations as $d) {
                $hours = $d->duration / 3600;
                $w = time_to_week((int)$d->start);
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
                                                                                 'start_date' => timestamp_to_date($period_start),
                                                                                 'end_date' => timestamp_to_date($period_end),
                                                                                 'minweek' => $minweek,
                                                                                 'maxweek' => $maxweek,
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

            $this->form_validation->set_rules('start_date', 'Start date', 'trim|required|valid_date_check');
            $this->form_validation->set_rules('end_date', 'End date', 'trim|required|valid_date_check');
            $this->form_validation->set_rules('templ', 'Template', 'required');
            $this->form_validation->set_rules('userid', 'User ID', 'required');

            $userid = $this->mod_users->my_id(); // Default value
            
			if ($this->form_validation->run()) {
                $period_start = decode_start_date($this->input->get('start_date'));
                $period_end = decode_end_date($this->input->get('end_date')) -1;  // -1 to turn exclusive time into inclusive

                // If period is longer than MAX_PERIOD, adjust the end date.
                $period_end = min($period_end, $period_start + MAX_PERIOD -1);

                $templ = $this->input->get('templ');
                $userid = (int)$this->input->get('userid');

                if (!$this->mod_users->is_teacher() || $userid!=$this->mod_users->my_id())
                    throw new DataException($this->lang->line('illegal_user_id'));

                $templs = $this->mod_statistics->get_templids_for_pathname_and_user($templ, $userid);
                $res = $this->mod_statistics->get_score_by_date_user_templ($userid,$templs,$period_start,$period_end);

                $status = empty($res) ? 0 : 1;  // 0=no data, 1=data
            }
            else {
                $period_end = ((int)(time() / (24*3600)) + 1) * 24*3600;  // Midnight tonight
                $period_start = $period_end - MAX_PERIOD;
                --$period_end;  // Turn exclusive time into inclusive

                $status = 2; // 2=Bad data
            }

            // How many weeks does the time cover?
            $minweek = time_to_week($period_start);
            $maxweek = time_to_week($period_end);
            
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
                                                                             'start_date' => timestamp_to_date($period_start),
                                                                             'end_date' => timestamp_to_date($period_end),
                                                                             'minweek' => (int)$minweek,
                                                                             'maxweek' => (int)$maxweek), true);

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


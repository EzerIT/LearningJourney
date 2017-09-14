<?php


class Ctrl_LJ_graph_student extends MY_Controller {
    const MAX_PERIOD = 26*7*24*3600;  // 26 weeks
    
    public function index() {
        $this->student_time();
	}

    public function valid_date_check($date) {
        try {
            new DateTime($date,new DateTimeZone('UTC'));
            return true;
        }
        catch (Exception $e) {
            return false;
        }
    }

    public function decode_start_date($date) {
        // Set time to 00:00:00
        return date_create($date . ' 00:00:00',new DateTimeZone('UTC'))->getTimestamp();
    }

    public function decode_end_date($date) {
        // Set time to 23:59:59 and add one second
        return date_create($date . ' 23:59:59',new DateTimeZone('UTC'))->getTimestamp() + 1;
    }



    // Returns number of weeks since Monday 1970-01-05
    private function time_to_week(integer $time) {
        // UNIX time starts on a Thursday. So move epoch to Monday 1970-01-05
        $monday_offset = 4*24*3600;
        $seconds_per_week = 7*24*3600;
        return (int)floor(($time-$monday_offset) / $seconds_per_week);
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

            $this->form_validation->set_rules('start_date', 'Start date', 'trim|callback_valid_date_check');
            $this->form_validation->set_rules('end_date', 'End date', 'trim|callback_valid_date_check');

 
			if ($this->form_validation->run()) {
                $period_start = $this->decode_start_date($this->input->post('start_date'));
                $period_end = $this->decode_end_date($this->input->post('end_date'));

                // If period is more than MAX_PERIOD, this is faked POST data. Adjust the end date.
                $period_end = min($period_end, $period_start + self::MAX_PERIOD);

                $classid = $this->input->post('classid');
                if (is_null($classid))
                    $classid = 0;
            }
            else {
                $period_end = ((int)(time() / (24*3600)) + 1) * 24*3600;  // Midnight tonight
                $period_start = $period_end - self::MAX_PERIOD;
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
            $minweek = $this->time_to_week($period_start);
            $maxweek = $this->time_to_week($period_end-1);

            // $total[23] will be the duration in week 23
            // $totaltemp[$templatename] will be the total time spent on template $templatename
            $total = array();
            $totaltemp = array();
            for ($w=$minweek; $w<=$maxweek; ++$w)
                $total[$w] = 0;

            foreach ($durations as $d) {
                $hours = $d->duration / 3600;
                $w = $this->time_to_week((int)$d->start);
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
                                                                    'RGraph/libraries/RGraph.common.key.js')));
            $this->load->view('view_top2');
            $this->load->view('view_menu_bar', array('langselect' => true));
            
            $center_text = $this->load->view('view_LJ_graph_student_time', array('classid' => $classid,
                                                                                 'classlist' => $myclasses,
                                                                                 'userid' => $myid,
                                                                                 'start_date' => date('Y-m-d',$period_start),
                                                                                 'end_date' => date('Y-m-d',$period_end),
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


    // Note: This is never invoked directly. It is called as a post request form other web pages
    public function view_quiz()
    {
		$this->load->model('mod_users');
		$this->load->model('mod_statistics');
 
		try {
            $this->load->helper('form');
            $this->load->library('form_validation');

            $this->form_validation->set_rules('start_date', 'Start date', 'trim|required|callback_valid_date_check');
            $this->form_validation->set_rules('end_date', 'End date', 'trim|required|callback_valid_date_check');
            $this->form_validation->set_rules('templ', 'Template', 'required');
            $this->form_validation->set_rules('userid', 'User ID', 'required');

            $userid = $this->mod_users->my_id(); // Default value
            
			if ($this->form_validation->run()) {
                $period_start = $this->decode_start_date($this->input->post('start_date'));
                $period_end = $this->decode_end_date($this->input->post('end_date'));

                // If period is more than MAX_PERIOD, this is faked POST data. Adjust the end date.
                $period_end = min($period_end, $period_start + self::MAX_PERIOD);

                $templ = $this->input->post('templ');
                $userid = (int)$this->input->post('userid');

                if (!$this->mod_users->is_teacher() || $userid!=$this->mod_users->my_id())
                    throw new DataException($this->lang->line('illegal_user_id'));

                $templs = $this->mod_statistics->get_templids_for_pathname_and_user($templ, $userid);
                $res = $this->mod_statistics->get_score_by_date_user_templ($userid,$templs,$period_start,$period_end);

                $status = empty($res) ? 0 : 1;  // 0=no data, 1=data
            }
            else {
                $period_end = ((int)(time() / (24*3600)) + 1) * 24*3600;  // Midnight tonight
                $period_start = $period_end - self::MAX_PERIOD;

                $status = 2; // 2=Bad data
            }

            // How many weeks does the time cover?
            $minweek = $this->time_to_week($period_start);
            $maxweek = $this->time_to_week($period_end-1);
            
            // VIEW:
            $this->load->view('view_top1', array('title' => 'Exercise Graphs',
                                                 'js_list' => array('RGraph/libraries/RGraph.common.core.js',
                                                                    'RGraph/libraries/RGraph.scatter.js',
                                                                    'RGraph/libraries/RGraph.common.dynamic.js',
                                                                    'RGraph/libraries/RGraph.common.tooltips.js',
                                                                    'RGraph/libraries/RGraph.common.key.js')));

            $this->load->view('view_top2');
            $this->load->view('view_menu_bar', array('langselect' => true));
            
            $center_text = $this->load->view('view_LJ_graph_exercise', array('resall' => $res,
                                                                             'quiz' => $templ,
                                                                             'status' => $status,
                                                                             'userid' => $userid,
                                                                             'start_date' => date('Y-m-d',$period_start),
                                                                             'end_date' => date('Y-m-d',$period_end),
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
    
    public function TBD_view_exercises() {
    	$this->load->model('mod_users');
    	$this->load->model('mod_classes');
    	$this->load->model('mod_statistics');

        try {
            $this->mod_users->check_teacher();

            $this->db->set_dbprefix('bol_');

            $classid = isset($_GET['classid']) ? (int)$_GET['classid'] : 0;
            $class = $this->mod_classes->get_class_by_id($classid);
			if ($classid<=0 || ($class->ownerid!=$this->mod_users->my_id() && $this->mod_users->my_id()!=25)) // TODO remove 25
//			if ($classid<=0 || $class->ownerid!=$this->mod_users->my_id())
				throw new DataException($this->lang->line('illegal_class_id'));

            $exercise_list = $this->mod_statistics->get_pathnames_for_class($classid);

            $this->load->helper('form');
            $this->load->library('form_validation');

            $this->form_validation->set_rules('start_date', 'Start date', 'trim|callback_valid_date_check');
            $this->form_validation->set_rules('end_date', 'End date', 'trim|callback_valid_date_check');
            $this->form_validation->set_rules('exercise', 'Exercise', 'required');


			if ($this->form_validation->run()) {
                $period_start = $this->decode_start_date($this->input->post('start_date'));
                $period_end = $this->decode_end_date($this->input->post('end_date'));

                // If period is more than MAX_PERIOD, this is faked POST data. Adjust the end date.
                $period_end = min($period_end, $period_start + self::MAX_PERIOD);

                $ex = $this->input->post('exercise');

                // Find all user IDs and template IDs that match the specified pathname
                $users_and_templs = $this->mod_statistics->get_users_and_templ($ex);

                $resall = array();
                $real_students = array(); // Will be used as a set

                foreach ($users_and_templs as $uid => $templs) {
                    $res = $this->mod_statistics->get_score_by_date_user_templ($uid,$templs,$period_start,$period_end);
                    if (empty($res))
                        continue;
                    $resall[] = $res;
                    $real_students[$uid] = true;
                }

                $status = empty($resall) ? 0 : 1;  // 0=no data, 1=data

                // Get student names
                foreach ($real_students as $uid => &$v)
                    $v = make_full_name($this->mod_users->get_user_by_id($uid));

                // Because $users_and_temps is sorted by user ID, $real_students and $resall are sorted in the same order
            }
            else {
                $period_end = ((int)(time() / (24*3600)) + 1) * 24*3600;  // Midnight tonight
                $period_start = $period_end - self::MAX_PERIOD;
                $ex = '';

                $status = 2; // 2=Initial display
                $real_students = null;
                $resall = null;
            }

            // How many weeks does the time cover?
            $minweek = $this->time_to_week($period_start);
            $maxweek = $this->time_to_week($period_end-1);

            
            // VIEW:
            $this->load->view('view_top1', array('title' => 'Exercise Graphs',
                                                 'js_list' => array('RGraph/libraries/RGraph.common.core.js',
                                                                    'RGraph/libraries/RGraph.scatter.js',
                                                                    'RGraph/libraries/RGraph.common.dynamic.js',
                                                                    'RGraph/libraries/RGraph.common.tooltips.js',
                                                                    'RGraph/libraries/RGraph.common.key.js')));

            $this->load->view('view_top2');
            $this->load->view('view_menu_bar', array('langselect' => true));
            
            $center_text = $this->load->view('view_LJ_graph_view_exercises', array('classid' => $classid,
                                                                                   'classname' => $class->classname,
                                                                                   'students' => $real_students,
                                                                                   'resall' => $resall,
                                                                                   'status' => $status,
                                                                                   'quiz' => $ex,
                                                                                   'start_date' => date('Y-m-d',$period_start),
                                                                                   'end_date' => date('Y-m-d',$period_end),
                                                                                   'minweek' => (int)$minweek,
                                                                                   'maxweek' => (int)$maxweek,
                                                                                   'exercise_list' => $exercise_list), true);

            $this->load->view('view_main_page', array('left_title' => 'Select a Period',
                                                      'left' => '<p>Use the two date fields to select a first
                                                                 and last date to view. Use the selector to select an exercise.</p>
                                                                 <p><b>Note: At most 26 weeks (6 months) of information
                                                                 can be shown at a time.</b></p>',
                                                      'center' => $center_text));
            $this->load->view('view_bottom');
        }
        catch (DataException $e) {
            $this->error_view($e->getMessage(), 'Exercise Graphs');
        }
    }
  }


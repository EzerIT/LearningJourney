<?php

function preprint($x) {
    echo "<pre>",print_r($x,true),"</pre>";
  }
    

class Ctrl_LJ_graph_teacher extends MY_Controller {
    const MAX_PERIOD = 26*7*24*3600;  // 26 weeks

    
    public function index() {
        $this->select_class();
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

    public function select_class() {
    	$this->load->model('mod_users');
    	$this->load->model('mod_classes');

        try {
            $this->mod_users->check_teacher();

            //$this->db->set_dbprefix('bol_');
            
            $classes = $this->mod_classes->get_named_classes_owned(false);

            $this->load->view('view_top1', array('title' => 'Teacher Graph'));
            $this->load->view('view_top2');
            $this->load->view('view_menu_bar', array('langselect' => true));
            
            $center_text = $this->load->view('view_LJ_teacher_classes', array('classes' => $classes), true); 

            $this->load->view('view_main_page', array('left_title' => 'Select class',
                                                      'left' => '<p>Here you will find a list of classes you manage.</p><p>Click the “Students” or “Exercises” button next to a class.</p>',
                                                      'center' => $center_text));
            $this->load->view('view_bottom');
        }
        catch (DataException $e) {
            $this->error_view($e->getMessage(), 'Teacher Graph');
        }
    }

    // Returns number of weeks since Monday 1970-01-05
    private function time_to_week(integer $time) {
        // UNIX time starts on a Thursday. So move epoch to Monday 1970-01-05
        $monday_offset = 4*24*3600;
        $seconds_per_week = 7*24*3600;
        return floor(($time-$monday_offset) / $seconds_per_week);
    }
    
    public function view_students() {
    	$this->load->model('mod_users');
    	$this->load->model('mod_classes');
    	$this->load->model('mod_userclass');
    	$this->load->model('mod_statistics');

        try {
            $this->mod_users->check_teacher();

            //$this->db->set_dbprefix('bol_');

            $classid = isset($_GET['classid']) ? (int)$_GET['classid'] : 0;
            $class = $this->mod_classes->get_class_by_id($classid);
//			if ($classid<=0 || ($class->ownerid!=$this->mod_users->my_id() && $this->mod_users->my_id()!=25)) // TODO remove 25
			if ($classid<=0 || $class->ownerid!=$this->mod_users->my_id())
				throw new DataException($this->lang->line('illegal_class_id'));

            $students = $this->mod_userclass->get_named_users_in_class($classid);
            if (empty($students))
                throw new DataException('No students in class');

            $student_ids = array();
            foreach ($students as $st)
                $student_ids[] = (int)$st->userid;

            $this->load->helper('form');
            $this->load->library('form_validation');

            $this->form_validation->set_rules('start_date', 'Start date', 'trim|callback_valid_date_check');
            $this->form_validation->set_rules('end_date', 'End date', 'trim|callback_valid_date_check');

 
			if ($this->form_validation->run()) {
                $period_start = $this->decode_start_date($this->input->post('start_date'));
                $period_end = $this->decode_end_date($this->input->post('end_date'));

                // If period is more than MAX_PERIOD, this is faked POST data. Adjust the end date.
                $period_end = min($period_end, $period_start + self::MAX_PERIOD);
			}
            else {
                $period_end = ((int)(time() / (24*3600)) + 1) * 24*3600;  // Midnight tonight
                //$period_end = 1483138800; // 31.12.2016
                $period_start = $period_end - self::MAX_PERIOD;
            }

            $templates = $this->mod_statistics->get_templates_for_class_and_students($classid,$student_ids);
            if (!empty($templates))
                $durations = $this->mod_statistics->get_quizzes_duration($templates, $period_start, $period_end);
            else
                $durations = array();

            // How many weeks does the time cover?
            $minweek = $this->time_to_week($period_start);
            $maxweek = $this->time_to_week($period_end-1);

            // What students actually have results?
            $real_students = array(); // Will be used as a set
            foreach ($durations as $d)
                $real_students[$d->userid] = true;
            ksort($real_students);
            $number_students = count($real_students);
            
            // $dur[23][55] will be the duration for user 55 in week 23
            // $total[23]  will be the total duration for all users in week 23
            $dur = array();
            $total = array();
            for ($w=$minweek; $w<=$maxweek; ++$w) {
                $dur[$w] = array();
                $total[$w] = 0;
                foreach ($real_students as $st => $ignore)
                    $dur[$w][$st] = 0;
            }

            foreach ($durations as $d) {
                $hours = $d->duration / 3600;
                $w = $this->time_to_week((int)$d->start);
                $dur[$w][$d->userid] += $hours;
                $total[$w] += $hours;
            }

            // Get student names
            foreach ($students as $st)
                if (isset($real_students[$st->userid]))
                    $real_students[$st->userid] = $st->name;

            // VIEW:
            $this->load->view('view_top1', array('title' => 'Student Graphs',
                                                 'js_list' => array('RGraph/libraries/RGraph.common.core.js',
                                                                    'RGraph/libraries/RGraph.bar.js',
                                                                    'RGraph/libraries/RGraph.common.dynamic.js',
                                                                    'RGraph/libraries/RGraph.common.tooltips.js',
                                                                    'RGraph/libraries/RGraph.common.key.js')));
            $this->load->view('view_top2');
            $this->load->view('view_menu_bar', array('langselect' => true));
            
            $center_text = $this->load->view('view_LJ_graph_view_students', array('classid' => $classid,
                                                                                  'classname' => $class->classname,
                                                                                  'students' => $real_students,
                                                                                  'start_date' => date('Y-m-d',$period_start),
                                                                                  'end_date' => date('Y-m-d',$period_end),
                                                                                  'dur' => $dur,
                                                                                  'total' => $total), true);

            $this->load->view('view_main_page', array('left_title' => 'Select a Period',
                                                      'left' => '<p>Use the two date fields to select a first
                                                                 and last date to view.</p>
                                                                 <p><b>Note: At most 26 weeks (6 months) of information
                                                                 can be shown at a time.</b></p>',
                                                      'center' => $center_text));
            $this->load->view('view_bottom');
        }
        catch (DataException $e) {
            $this->error_view($e->getMessage(), 'Student Graphs');
        }
    }
    
    public function view_exercises() {
    	$this->load->model('mod_users');
    	$this->load->model('mod_classes');
    	$this->load->model('mod_statistics');

        try {
            $this->mod_users->check_teacher();

            //$this->db->set_dbprefix('bol_');

            $classid = isset($_GET['classid']) ? (int)$_GET['classid'] : 0;
            $class = $this->mod_classes->get_class_by_id($classid);
//			if ($classid<=0 || ($class->ownerid!=$this->mod_users->my_id() && $this->mod_users->my_id()!=25)) // TODO remove 25
			if ($classid<=0 || $class->ownerid!=$this->mod_users->my_id())
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

                $ex = urldecode($this->input->post('exercise'));

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


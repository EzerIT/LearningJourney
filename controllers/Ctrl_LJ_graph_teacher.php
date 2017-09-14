<?php


class Ctrl_LJ_graph_teacher extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->helper('lj_date_helper');
    }

    public function index() {
        $this->select_class();
	}

    // Dummy validation function
    public function always_true($field) {
        return true;
    }

    public function select_class() {
    	$this->load->model('mod_users');
    	$this->load->model('mod_classes');

        try {
            $this->mod_users->check_teacher();

            $this->db->set_dbprefix('bol_');
            
//            $classes = $this->mod_classes->get_named_classes_owned(false);
            $classes = $this->mod_classes->get_named_classes_owned(!false);

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

    public function view_students() {
    	$this->load->model('mod_users');
    	$this->load->model('mod_classes');
    	$this->load->model('mod_userclass');
    	$this->load->model('mod_statistics');

        try {
            $this->mod_users->check_teacher();

            $this->db->set_dbprefix('bol_');

            $this->load->helper('form');
            $this->load->library('form_validation');

            $this->form_validation->set_data($_GET);

            $classid = (int)$this->input->get('classid');
            $class = $this->mod_classes->get_class_by_id($classid);
			if ($classid<=0 || ($class->ownerid!=$this->mod_users->my_id() && $this->mod_users->my_id()!=25)) // TODO remove 25
//			if ($classid<=0 || $class->ownerid!=$this->mod_users->my_id())
				throw new DataException($this->lang->line('illegal_class_id'));
            
            $this->form_validation->set_rules('start_date', 'Start date', 'trim|valid_date_check');
            $this->form_validation->set_rules('end_date', 'End date', 'trim|valid_date_check');

			if ($this->form_validation->run()) {
                $period_start = decode_start_date($this->input->get('start_date'));
                $period_end = decode_end_date($this->input->get('end_date')) -1;  // -1 to turn exclusive time into inclusive

                // If period is longer than MAX_PERIOD, adjust the end date.
                $period_end = min($period_end, $period_start + MAX_PERIOD -1);

                $status = 1; // 1 = OK
                
                $students = $this->mod_userclass->get_named_users_in_class($classid);
                if (empty($students))
                    throw new DataException('No students in class');

                $student_ids = array();
                foreach ($students as $st)
                    $student_ids[] = (int)$st->userid;

                $templates = $this->mod_statistics->get_templates_for_class_and_students($classid,$student_ids);
                if (!empty($templates))
                    $durations = $this->mod_statistics->get_quizzes_duration($templates, $period_start, $period_end);
                else
                    $durations = array();

                // How many weeks does the time cover?
                $minweek = time_to_week($period_start);
                $maxweek = time_to_week($period_end);

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
                    $w = time_to_week((int)$d->start);
                    $dur[$w][$d->userid] += $hours;
                    $total[$w] += $hours;
                }

                // Get student names
                foreach ($students as $st)
                    if (isset($real_students[$st->userid]))
                        $real_students[$st->userid] = $st->name;
			}
            else {
                $period_end = ((int)(time() / (24*3600)) + 1) * 24*3600;  // Midnight tonight
                $period_start = $period_end - MAX_PERIOD;
                --$period_end;  // Turn exclusive time into inclusive
                
                $real_students = null;
                $dur = null;
                $total = null;
                
                $status = 2; // 2 = Bad
            }

            // VIEW:
            $this->load->view('view_top1', array('title' => 'Student Graphs',
                                                 'js_list' => array('RGraph/libraries/RGraph.common.core.js',
                                                                    'RGraph/libraries/RGraph.bar.js',
                                                                    'RGraph/libraries/RGraph.common.dynamic.js',
                                                                    'RGraph/libraries/RGraph.common.tooltips.js',
                                                                    'RGraph/libraries/RGraph.common.key.js',
                                                                    'myapp/third_party/lj/js/datepicker_period.js')));
            $this->load->view('view_top2');
            $this->load->view('view_menu_bar', array('langselect' => true));

            $center_text = $this->load->view('view_LJ_graph_view_students', array('status' => $status,
                                                                                  'classid' => $classid,
                                                                                  'classname' => $class->classname,
                                                                                  'students' => $real_students,
                                                                                  'start_date' => timestamp_to_date($period_start),
                                                                                  'end_date' => timestamp_to_date($period_end),
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

            $this->db->set_dbprefix('bol_');


            $this->load->helper('form');
            $this->load->library('form_validation');

            $this->form_validation->set_data($_GET);

            $classid = (int)$this->input->get('classid');
            $class = $this->mod_classes->get_class_by_id($classid);
			if ($classid<=0 || ($class->ownerid!=$this->mod_users->my_id() && $this->mod_users->my_id()!=25)) // TODO remove 25
//			if ($classid<=0 || $class->ownerid!=$this->mod_users->my_id())
				throw new DataException($this->lang->line('illegal_class_id'));

            $exercise_list = $this->mod_statistics->get_pathnames_for_class($classid);

            $this->form_validation->set_rules('start_date', 'Start date', 'trim|valid_date_check');
            $this->form_validation->set_rules('end_date', 'End date', 'trim|valid_date_check');
            $this->form_validation->set_rules('exercise', '', 'callback_always_true');  // Dummy rule. At least one rule is required

			if ($this->form_validation->run()) {
                $period_start = decode_start_date($this->input->get('start_date'));
                $period_end = decode_end_date($this->input->get('end_date')) -1;  // -1 to turn exclusive time into inclusive

                // If period is longer than MAX_PERIOD, adjust the end date.
                $period_end = min($period_end, $period_start + MAX_PERIOD -1);

                $ex = $this->input->get('exercise');
                if (empty($ex)) {
                    $ex = '';
                    $status = 2; // 2=Initial display
                    $real_students = null;
                    $resall = null;
                }
                else {
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
            }
            else {
                $period_end = ((int)(time() / (24*3600)) + 1) * 24*3600;  // Midnight tonight
                $period_start = $period_end - MAX_PERIOD;
                --$period_end;  // Turn exclusive time into inclusive

                $ex = '';
                $status = 2; // 2=Initial display
                $real_students = null;
                $resall = null;
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
            
            $center_text = $this->load->view('view_LJ_graph_view_exercises', array('classid' => $classid,
                                                                                   'classname' => $class->classname,
                                                                                   'students' => $real_students,
                                                                                   'resall' => $resall,
                                                                                   'status' => $status,
                                                                                   'quiz' => $ex,
                                                                                   'start_date' => timestamp_to_date($period_start),
                                                                                   'end_date' => timestamp_to_date($period_end),
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


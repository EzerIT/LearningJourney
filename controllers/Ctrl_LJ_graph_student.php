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
            $this->db->set_dbprefix('bol_');

            $this->load->helper('form');
            $this->load->library('form_validation');

            $this->form_validation->set_data($_GET);

            $this->lj_timeperiod->set_validation_rules();
            $this->form_validation->set_rules('classid', '', 'callback_always_true');  // Dummy rule. At least one rule is required

			if ($this->form_validation->run()) {
                $this->lj_timeperiod->ok_dates();

                $userid =  $this->input->get('userid');
                if (is_null($userid))
                    $userid = $this->mod_users->my_id(); // Default value
                else
                    $userid = (int)$userid;

                if (!$this->mod_users->is_teacher() && $userid!=$this->mod_users->my_id())
                    throw new DataException($this->lang->line('illegal_user_id'));

                $classid = $this->input->get('classid');
                if (is_null($classid))
                    $classid = 0;
            }
            else {
                $this->lj_timeperiod->default_dates();

                $classid = 0;
                $userid = $this->mod_users->my_id();
            }


            $myclassids = $this->mod_userclass->get_classes_for_user($userid);
            $myclasses = $this->mod_classes->get_classes_by_ids($myclassids);

            
            // $classid==0 means ignore class information

            if ($classid>0) {
                if (!in_array($classid,$myclassids))
                    throw new DataException($this->lang->line('illegal_class_id'));

                $templates = $this->mod_statistics->get_templates_for_class_and_students((int)$classid,array($userid));
            }
            else
                $templates = $this->mod_statistics->get_templates_for_students(array($userid));

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
            
            $center_text = $this->load->view('view_LJ_student_time', array('classid' => $classid,
                                                                           'classlist' => $myclasses,
                                                                           'userid' => $userid,
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
        

    public function view_exercise()
    {
        $this->db->set_dbprefix('bol_');

		$this->load->model('mod_users');
		$this->load->model('mod_statistics');
 
		try {
            $this->load->helper('form');
            $this->load->library('form_validation');
            $this->load->library('db_config');

            $this->form_validation->set_data($_GET);

            $this->lj_timeperiod->set_validation_rules();
            $this->form_validation->set_rules('templ', 'Template', 'required');
            $this->form_validation->set_rules('userid', 'User ID', 'required');

            $userid = $this->mod_users->my_id(); // Default value
            $templ = $this->input->get('templ');
            
			if ($this->form_validation->run()) {
                $this->lj_timeperiod->ok_dates();

                $userid = (int)$this->input->get('userid');

                if (!$this->mod_users->is_teacher() && $userid!=$this->mod_users->my_id())
                    throw new DataException($this->lang->line('illegal_user_id'));

                $templs = $this->mod_statistics->get_templids_for_pathname_and_user($templ, $userid);

                $resscore = $this->mod_statistics->get_score_by_date_user_templ($userid,
                                                                                $templs,
                                                                                $this->lj_timeperiod->start_timestamp(),
                                                                                $this->lj_timeperiod->end_timestamp());

                
                $resfeat = $this->mod_statistics->get_features_by_date_user_templ($userid,
                                                                                  $templs,
                                                                                  $this->lj_timeperiod->start_timestamp(),
                                                                                  $this->lj_timeperiod->end_timestamp());

                // Localize feature names

                if (!empty($resfeat)) {
                    // We assume that the underlying database information never changed
                    $dbnames = $this->mod_statistics->get_templ_db($templs);
                    $this->db_config->init_config($dbnames->dbname,$dbnames->dbpropname, $this->language_short);
                    $l10n = json_decode($this->db_config->l10n_json);
                    $featloc = $l10n->emdrosobject->{$dbnames->qoname}; // We only need localization of feature names
                }
                else
                    $featloc = null;

                $status = empty($resscore) ? 0 : 1;  // 0=no data, 1=data
            }
            else {
                $this->lj_timeperiod->default_dates();

                $resscore = null;
                $resfeat = null;
                $featloc = null;
                $status = 2; // 2=Bad data
            }

            // VIEW:
            $this->load->view('view_top1', array('title' => 'Exercise Graphs',
                                                 'js_list' => array('RGraph/libraries/RGraph.common.core.js',
                                                                    'RGraph/libraries/RGraph.scatter.js',
                                                                    'RGraph/libraries/RGraph.hbar.js',
                                                                    'RGraph/libraries/RGraph.common.dynamic.js',
                                                                    'RGraph/libraries/RGraph.common.tooltips.js',
                                                                    'RGraph/libraries/RGraph.common.key.js',
                                                                    'myapp/third_party/lj/js/datepicker_period.js')));

            $this->load->view('view_top2');
            $this->load->view('view_menu_bar', array('langselect' => true));
            
            $center_text = $this->load->view('view_LJ_student_exercises', array('resscore' => $resscore,
                                                                                'resfeat' => $resfeat,
                                                                                'featloc' => $featloc,
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


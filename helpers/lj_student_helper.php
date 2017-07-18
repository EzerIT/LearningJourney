<?php

	class Student
	{
		private $rightarr = array();
		private $wrongarr = array();
		private $sumright;
		private $sumwrong;
		private $durationarr = array();
		private $sumtime;
		private $proficiency;
		private $firstname;
		private $lastname;
		private	$userid;
		private $sumduration;
		private $path;
		private $learningDate;
		private $secPerCor;
		private $correctPerMin;
		private $durationSingle;
		private $rightLogbook;
		private $wrongLogbook;
		private $learningData = array();
		private $userclass;
        private $sumdur;
        private $sumGradeTemp;
        private $temp;
        private $gradePercent;
        private $finalGrade;
        private $gradearr = array();
        private $start;
        private $startarr = array();
        private $progress;
        private $gradingFeatureRight;
        private $gradingFeatureWrong;
        private $gradingFeature;
        private $gradingArrComplete = array();
        private $allQuizzes = array();
        private $gradingPath;
        private $gradingArrCompletePath = array();
        private $gradearrPath = array();
        private $progressPath;
        private $quizid;
        private $analysis = array();
        private $gradingPathProgress;
        private $suspiciousData;
        private $secpercorarr = array();
        private $averagesecpercor;
        private $gradingRight;
        private $gradingWrong;
			
		public function set_firstname($data)
		{
			$this->firstname = $data;
		}
			
		public function get_firstname()
		{
			return $this->firstname;
		}

		public function set_lastname($data)
		{
			$this->lastname = $data;
		}
			
		public function get_lastname()
		{
			return $this->lastname;
		}

		public function set_userid($data)
		{
			$this->userid = $data;
		}
			
		public function get_userid()
		{
			return $this->userid;
		}
        
        public function set_ownerid($data)
		{
			$this->ownerid = $data;
		}
			
		public function get_ownerid()
		{
			return $this->ownerid;
		}
			
		public function get_sumduration()
		{
			$this->sumduration;
			return $this->sumduration;
		}
		
		public function get_path()
		{
			$this->path;
			return $this->path;
		}
		
		public function get_date()
		{
			$this->learningDate;
			return $this->learningDate;
		}
		
		/*public function get_secPerCor()
		{
			$this->secPerCor;
			return $this->secPerCor;
		}*/
		
		public function get_correctPerMin()
		{
			$this->correctPerMin;
			return $this->correctPerMin;
		}	
			
		public function set_arrayright($data)
		{
			$this->rightarr[] = $data;
		}
			
		public function get_arrayright()
		{
			echo '<pre>'; var_dump($this->rightarr); echo '</pre>';
		}
			
		public function set_arraywrong($data)
		{
			$this->wrongarr[] = $data;
		}
        
        public function arraysecpercor($data)
        {
           $this->secpercorarr[] = $data; 
        }
        
        public function set_start($data){
            
            $this->startarr[] = $data;
            
            $this->start = max($this->startarr);
        }

        public function get_start(){
            
            return $this->start;
        }
        
        public function set_progress($data)
        {
           
            $this->progress = $data; 
        }
        
        public function get_progress()
        {
            return $this->progress;
        }
        
        public function set_progressPath($data)
        {
           
            $this->progressPath = $data; 
        }
        
        public function get_progressPath()
        {
            return $this->progressPath;
        }
				

		public function set_sumright()
		{	
			$this->sumright = 0;
				
			foreach($this->rightarr as $answers)
			{
				$this->sumright = $this->sumright + $answers;
			}
				
			return $this->sumright;
		}
		
		public function set_rightLogbook($data)
		{
			$this->rightLogbook = $data;
		}
		
		public function get_rightLogbook()
		{
			$this->rightLogbook;
			return $this->rightLogbook;
		}
		
		public function set_wrongLogbook($data)
		{
			$this->wrongLogbook = $data;
		}
		
		public function get_wrongLogbook()
		{
			$this->wrongLogbook;
			return $this->wrongLogbook;
		}
				
		public function set_sumwrong()
		{		
			$this->sumwrong = 0;
				
			foreach($this->wrongarr as $answers)
			{
				$this->sumwrong = $this->sumwrong + $answers;
			}
				
			return $this->sumwrong;
		}
			
		public function set_duration($data)
		{
			$this->durationarr[] = $data;
            
            foreach($this->durationarr as $duration)
            {
                $this->sumdur = $this->sumdur + $duration;
            }
            
            return $this->sumdur;
		}
		
		public function set_durationSingle($data)
		{
			$this->durationSingle = $data;
		}
		
		public function get_durationSingle()
		{
			$this->durationSingle;
			return $this->durationSingle;
		}
			
		public function get_duration()
		{
			return $this->sumdur;
		}
		
		public function set_path($data)
		{
			$this->path = $data;
		}
		
		public function set_date($data)
		{
			$this->learningDate = $data;
		}
		
		public function set_secPerCor($data)
		{
			$this->secPerCor = $data;
		}
		
		public function set_correctPerMin($data)
		{
			$this->correctPerMin = $data;
		}
					
		public function proficiency()
		{
			$this->set_sumright();
			$this->sumtime = 0;
								
			foreach($this->durationarr as $time) 
			{
				$this->sumtime = $this->sumtime + $time;
			}
				
			if($this->sumtime != 0)
			{
				return $this->proficiency = $this->sumright / $this->sumtime * 60;
			}
			else
			{
				return $this->proficiency = "Proficiency not set";
			}
			
		}
		
		public function set_learningData($data)
		{
			$this->learningData = $data;
		}
		
		public function get_learningData()
		{	
			return $this->learningData;
		}
	
		public static function compare_proficiency($s1, $s2) 
		{
        	$p1 = $s1->proficiency();
        	$p2 = $s2->proficiency();
        
        	if ($p1 < $p2)
        	{
            	return 1;
        	}
        	if ($p1 > $p2)
        	{
            	return -1;
        	}
        	return 0;
    	}
        
        public static function compare_grade($g1, $g2)
        {
            if ($g1 < $g2)
        	{
            	return 1;
        	}
        	if ($g1 > $g2)
        	{
            	return -1;
        	}
        	return 0;
        }
    	
    	public function set_userclass($data)
    	{
    		$this->userclass = $data;
    	}
    	
    	public function get_userclass()
    	{
    		return $this->userclass;
    	}
        
        public function set_featureArr($data)
        {
            $this->featurearr[] = $data;
        }

        public function get_featureArr()
        {
            return $this->featurearr;
        }
        
        public function set_valueArr($data)
        {
            $this->valuearr[] = $data;
        }
        
        public function get_valueArr()
        {
            return $this->valuearr;
        }

        public function set_studentAnswerArr($data)
        {
           $this->studentanswerarr[] = $data; 
        }
        
        public function get_studentAnswerArr()
        {
            return $this->studentanswerearr;
        }
        
        public function average_secpercor()
        {
            if (count($this->secpercorarr) > 0) {
                
                $this->averagesecpercor = array_sum($this->secpercorarr) / count($this->secpercorarr);
                
                return $this->averagesecpercor;
            }
            else
                return $this->averagesecpercor = 0;
            
        }
        
        public function grading($right, $wrong)
        {
            //$this->set_sumright();
            //$this->set_sumwrong();
            
            //$this->average_secpercor();
            $this->gradingRight = $right;
            $this->gradingWrong = $wrong;

            $this->sumGradeTemp = $this->gradingRight + $this->gradingWrong;
            
            //$this->sumGradeTemp = $this->sumright + $this->sumwrong;
            
            //if($this->averagesecpercor < 12) {
                
            if($this->sumGradeTemp > 0){
                $this->temp = 100 / $this->sumGradeTemp;
                $this->gradePercent = $this->temp * $this->gradingRight;

                if($this->gradePercent >= 95)
                    $this->finalGrade = "A+";
                elseif ($this->gradePercent < 95 && $this->gradePercent >= 93)
                    $this->finalGrade = "A";
                elseif ($this->gradePercent < 93 && $this->gradePercent >= 90)
                    $this->finalGrade = "A-";
                elseif ($this->gradePercent < 90 && $this->gradePercent >= 87)
                    $this->finalGrade = "B+";
                elseif ($this->gradePercent < 87 && $this->gradePercent >= 83)
                    $this->finalGrade = "B";
                elseif ($this->gradePercent < 83 && $this->gradePercent >= 80)
                    $this->finalGrade = "B-";
                elseif ($this->gradePercent < 80 && $this->gradePercent >= 77)
                    $this->finalGrade = "C+";
                elseif ($this->gradePercent < 77 && $this->gradePercent >= 73)
                     $this->finalGrade = "C";   
                elseif ($this->gradePercent < 73 && $this->gradePercent >= 70)
                    $this->finalGrade = "C-";      
                elseif ($this->gradePercent < 70 && $this->gradePercent >= 67)
                    $this->finalGrade = "D+";    
                elseif ($this->gradePercent < 67 && $this->gradePercent >= 63)
                    $this->finalGrade = "D";           
                elseif ($this->gradePercent < 63 && $this->gradePercent >= 60)
                     $this->finalGrade = "D-";                   
                elseif ($this->gradePercent < 60 && $this->gradePercent >= 0)
                   $this->finalGrade = "F";
            }
            else
            {
                $this->gradePercent = 0;
                $this->finalGrade = "0";
                $this->sumGradeTemp = 0;
            }
            
            $this->gradearr[0] = $this->gradePercent;
            $this->gradearr[1] = $this->finalGrade;
            $this->gradearr[2] = $this->sumGradeTemp;

        }
        
        public function get_grade()
        {
            return $this->gradearr;
        }
        
        public function set_grading_feature($feature, $right, $wrong, $progress)
        {
            $this->gradingFeatureRight = $right;
            $this->gradingFeatureWrong = $wrong;
            $this->gradingFeature = $feature;
            $this->progress = $progress;
            //$this->average_secpercor();

            $this->sumGradeTemp = $this->gradingFeatureRight + $this->gradingFeatureWrong;
            
            //if($this->averagesecpercor < 12) {
            if($this->sumGradeTemp > 0){

                $this->temp = 100 / $this->sumGradeTemp;
                $this->gradePercent = $this->temp * $this->gradingFeatureRight ;

                if($this->gradePercent >= 95)
                    $this->finalGrade = "A+";
                elseif ($this->gradePercent < 95 && $this->gradePercent >= 93)
                    $this->finalGrade = "A";
                elseif ($this->gradePercent < 93 && $this->gradePercent >= 90)
                    $this->finalGrade = "A-";
                elseif ($this->gradePercent < 90 && $this->gradePercent >= 87)
                    $this->finalGrade = "B+";
                elseif ($this->gradePercent < 87 && $this->gradePercent >= 83)
                    $this->finalGrade = "B";
                elseif ($this->gradePercent < 83 && $this->gradePercent >= 80)
                    $this->finalGrade = "B-";
                elseif ($this->gradePercent < 80 && $this->gradePercent >= 77)
                    $this->finalGrade = "C+";
                elseif ($this->gradePercent < 77 && $this->gradePercent >= 73)
                     $this->finalGrade = "C";   
                elseif ($this->gradePercent < 73 && $this->gradePercent >= 70)
                    $this->finalGrade = "C-";      
                elseif ($this->gradePercent < 70 && $this->gradePercent >= 67)
                    $this->finalGrade = "D+";    
                elseif ($this->gradePercent < 67 && $this->gradePercent >= 63)
                    $this->finalGrade = "D";           
                elseif ($this->gradePercent < 63 && $this->gradePercent >= 60)
                     $this->finalGrade = "D-";                   
                elseif ($this->gradePercent < 60 && $this->gradePercent >= 0)
                    $this->finalGrade = "F";
            }
            //}

            $this->gradearr['feature'] = $this->gradingFeature;
            $this->gradearr['percent'] = $this->gradePercent;
            $this->gradearr['grade'] = $this->finalGrade;
            $this->gradearr['progress'] = $this->progress;
            $this->gradearr['numberAnswers'] = $this->sumGradeTemp;
            
            $this->gradingArrComplete[] = $this->gradearr;
            
        }
        
        public function get_grading_feature() {
            
            return $this->gradingArrComplete;   
        }
        
         public function set_grading_path($path, $right, $wrong, $progress, $quizid)
         {
            $this->gradingFeatureRight = $right;
            $this->gradingFeatureWrong = $wrong;
            $this->gradingPath = $path;
            $this->gradingPathProgress = $progress;
            $this->quizid = $quizid;
            //$this->average_secpercor();

            $this->sumGradeTemp = $this->gradingFeatureRight + $this->gradingFeatureWrong;

            //if($this->averagesecpercor < 12  && $this->averagesecpercor > 0) {

            if($this->sumGradeTemp > 0){

                $this->temp = 100 / $this->sumGradeTemp;
                $this->gradePercent = $this->temp * $this->gradingFeatureRight ;

                if($this->gradePercent >= 95)
                    $this->finalGrade = "A+";
                elseif ($this->gradePercent < 95 && $this->gradePercent >= 93)
                    $this->finalGrade = "A";
                elseif ($this->gradePercent < 93 && $this->gradePercent >= 90)
                    $this->finalGrade = "A-";
                elseif ($this->gradePercent < 90 && $this->gradePercent >= 87)
                    $this->finalGrade = "B+";
                elseif ($this->gradePercent < 87 && $this->gradePercent >= 83)
                    $this->finalGrade = "B";
                elseif ($this->gradePercent < 83 && $this->gradePercent >= 80)
                    $this->finalGrade = "B-";
                elseif ($this->gradePercent < 80 && $this->gradePercent >= 77)
                    $this->finalGrade = "C+";
                elseif ($this->gradePercent < 77 && $this->gradePercent >= 73)
                     $this->finalGrade = "C";   
                elseif ($this->gradePercent < 73 && $this->gradePercent >= 70)
                    $this->finalGrade = "C-";      
                elseif ($this->gradePercent < 70 && $this->gradePercent >= 67)
                    $this->finalGrade = "D+";    
                elseif ($this->gradePercent < 67 && $this->gradePercent >= 63)
                    $this->finalGrade = "D";           
                elseif ($this->gradePercent < 63 && $this->gradePercent >= 60)
                     $this->finalGrade = "D-";                   
                elseif ($this->gradePercent < 60 && $this->gradePercent >= 0)
                    $this->finalGrade = "F";
            }
            //}

            $this->gradearrPath['feature'] = $this->gradingPath;
            $this->gradearrPath['percent'] = $this->gradePercent;
            $this->gradearrPath['grade'] = $this->finalGrade;
            $this->gradearrPath['progress'] = $this->gradingPathProgress;
            $this->gradearrPath['quizid'] = $this->quizid;
            $this->gradearrPath['numberAnswers'] = $this->sumGradeTemp;
            
            $this->gradingArrCompletePath[] = $this->gradearrPath;
            
        }
        
         public function get_grading_path() {
            
            return $this->gradingArrCompletePath;   
        }
        
        public function set_allQuizzes($data){
            
            $this->allQuizzes[] = $data;
        }
        
        public function get_allQuizzes() {
            
            return $this->allQuizzes;
        }
        
         public function set_quizid($data){
            
            $this->quizid = $data;
        }
        
        public function get_quizid() {
            
            return $this->quizid;
        }
        
        public function set_analysis($data){
            
            $this->analysis[] = $data;
        }
        
        public function get_analysis() {
            
            return $this->analysis;
        }
        
        public function empty_analysis() {
          
            unset($this->analysis);
        }
        
        public function set_suspiciousData($data){
            
            if(isset($data) &&  $data > 0.3){
                
                $this->suspiciousData = "Student data is suspicious!";
            }
        }
        
        public function get_suspiciousData() {
            
            return $this->suspiciousData;
        }    
    }
        
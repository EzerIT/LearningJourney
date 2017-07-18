<?php
  function stripSortIndex(string $s) {
      return (strlen($s)>0 && $s[0]==='#')
          ? substr(strchr($s, ' '),1)
          : $s;
  }
?>

<div class="grading_logbook">   
    <h1> Grading for Student </h1>
  	 <table class="type2 table table-striped">
  		<tr>
  			<th>Feature</th>
            <th>Anwers given</th>
            <th>Percent</th>
            <th>Grade</th>
            <th>Progress</th>
         </tr>
         <tr>
        
        <?php foreach ($data as $u): ?>
  			
            <?
  				$gradingarr = $u->get_grading_feature();
                $userid = $u->get_userid();
                $quizdid = $u->get_quizid();
            
            ?>
             
             <?php foreach ($gradingarr as $grade): ?>
  			
  				<tr>
                    <td><?=$grade["feature"]?></td>
                    <td><?=$grade["numberAnswers"]?></td>
  				    <td><?=round($grade["percent"], 2)?></td>
                    <td><?=$grade["grade"]?></td>
                    <td><?=$grade['progress']?></td>
                </tr>
            <?php endforeach ?>

        <?php endforeach ?>
             
          
  	 </table>

    <h1>Learning Logbook for Student</h1>
    <table class="type2 table table-striped">
  		<tr>
  			<th>Filename</th>
            <th>Anwers given</th>
            <th>Percent</th>
            <th>Grade</th>
            <th>Progress</th>
         </tr>
         <tr>

         <?php foreach ($data as $u): ?>
  			
            <?
  				$quizarr = $u->get_grading_path();
                $progresspath = $u->get_progressPath();
                $quizid = $u->get_quizid();

            ?>
           
            <?php foreach ($quizarr as $quiz): ?>
        
                <?php $quizid=$quiz["quizid"]?>
             
  				<tr>
                    
                    <td><a href="<?= site_url("/lj/LJ_useranalysis/analysis_for_user?userid=$userid&quizid=$quizid")?>"><?=substr($quiz['feature'], 2, -2)?></a></td>
                    <td><?=$quiz['numberAnswers']?></td>
  				    <td><?=round($quiz["percent"], 2)?></td>
                    <td><?=$quiz["grade"]?></td>
                    <td><?=$quiz['progress']?></td>
                </tr>
            <?php endforeach ?>

        <?php endforeach ?>
        
    </table>
    </div>



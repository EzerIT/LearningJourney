<?php
  function stripSortIndex(string $s) {
      return (strlen($s)>0 && $s[0]==='#')
          ? substr(strchr($s, ' '),1)
          : $s;
  }
?>

<div class="gradebook_teacher">
  	 <table class="type2 table table-striped">
  		<tr>
  			<th>Class</th>
  			<th>Name</th>
            <th>Anwers given</th>
  			<th>Percent</th>
            <th>Grade</th>
            <th>Time</th>
            <th>Progress</th>
            <th>Suspicious Data</th>
            
        <?php
            
            $namebuffer = "name";
            $classbuffer = "class";
            
        ?>
  		
  		<?php foreach ($data as $u): ?>
  			
            <?
            
                $class = $u->userclass;
                $name = $u->name;
                $duration = $u->trainingtime;
                $answers = $u->answers;
                $percent = $u->percent;
                $grade = $u->grade;
                $progress = $u->progress;
                $suspicious = $u->suspicious;
                $userid = $u->userid;
            
                if($namebuffer == $name && $classbuffer == $class)
                    continue;
            
            ?>
  			
  				<tr>
                    <td><?=$class?></td>
                    <td><a href="<?= site_url("/lj/LJ_userlogbook/logbook_for_user?userid=$userid")?>"><?=$name?></a></td>
  				    <td><?=$answers?></td>
                    <td><?=$percent?></td>
                    <td><?=$grade?></td>
                    <td><?=$duration?></td>
                    <td><?=$progress?></td>
                    <td><?=$suspicious?></td>
                </tr>
         
        <?php
            
            $namebuffer = $name;
            $classbuffer = $class;
                        
        ?>

        <?php endforeach ?>
                    
  	 </table>
    </div>





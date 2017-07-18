<?php
  function stripSortIndex(string $s) {
      return (strlen($s)>0 && $s[0]==='#')
          ? substr(strchr($s, ' '),1)
          : $s;
  }
?>

<div class="grading_analysis">   
    <h1> Grading for Student </h1>
  	 <table class="type2 table table-striped">
  		<tr>
  			<th>Correct</th>
            <th>Value</th>
            <th>Answer</th>
            <th>Original Word</th>
         </tr>
         <tr>
        
        <?php foreach ($data as $u): ?>
  			
            <?
  				$analysis = $u->get_analysis();
            ?>
             
            <?php foreach ($analysis as $a): ?>
          	     <?php foreach ($a as $value): ?>
             
                  <tr>
    		          <td><?=$value->correct?></td>
                      <td><?=$value->value?></td>
                      <td><?=$value->answer?></td>
                      <td><?=$value->txt?></td>
                </tr>
                <?php endforeach ?>
            <?php endforeach ?>

        <?php endforeach ?>
             
          
  	 </table>
    </div>



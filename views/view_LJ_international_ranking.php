<?php
  function stripSortIndex(string $s) {
      return (strlen($s)>0 && $s[0]==='#')
          ? substr(strchr($s, ' '),1)
          : $s;
  }
?>

<div class="international_ranking">
  	 <table class="type2 table table-striped">
  		<tr>
  			<th>Firstname</th>
  			<th>Lastname</th>
  			<th>Proficiency</th>
  			<th>Duration</th>
  		</tr>
  		
  		<?php foreach ($data as $u) {
  			
  				$firstname = $u->get_firstname();
  				$lastname = $u->get_lastname();
  				$proficiency = $u->proficiency();
  				$duration = $u->get_duration();
  			
  				echo "<tr>\n";
  				echo "<td>" . $firstname .  "</td>\n";
  				echo "<td>" . $lastname . "</td>\n";
  				echo "<td>" . round($proficiency, 2) . "</td>\n";
  				echo "<td>" . gmdate("H:i:s", $duration) . "</td>\n";
  				echo "</tr>\n";
  			}
  		?>
  	</table>
</div>





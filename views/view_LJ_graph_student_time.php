<?php
    $valerr = validation_errors();
    if (!empty($valerr))
        echo "<div class=\"alert alert-danger\">$valerr</div>\n";
     
    function format_week(integer $weekno) {
        // $week is number of weeks since 1970-01-05
        $monday_offset = 4*24*3600;
        $seconds_per_week = 7*24*3600;
     
        $unixtime = $weekno * $seconds_per_week + $monday_offset;
     
        // $dt = date('oW',$unixtime);
        // $year = substr($dt,2,2);
        // $week = substr($dt,4);
        
        return date('W',$unixtime);
    }
?>

  <?= form_open("lj/LJ_graph_student/student_time") ?>
    <p>Specify date period (in the UTC time zone):</p>
  <table>
    <tr>
    <td style="font-weight:bold;padding-right:5px;padding-left:20px;">From:</td><td style="padding-left:5px"><input type="text" id="start_date1" name="start_date" value="<?= set_value('start_date',$start_date) ?>"></td>
    </tr>
    <tr>
    <td style="font-weight:bold;padding-right:5px;padding-left:20px;">To (and including):</td><td style="padding-left:5px"><input type="text" id="end_date1" name="end_date" value="<?= set_value('end_date',$end_date) ?>"></td>
    </tr>
  </table>

  <p>&nbsp;</p>
  <div>
    <span style="font-weight:bold">Class:</span>
    <select name="classid">
      <option value="0" <?= set_select('classid', 0, true) ?>>Ignore class</option>
      <?php foreach($classlist as $cl): ?>
        <?php if ($cl->id==$classid) $myclassname = $cl->classname; ?>
        <option value="<?= $cl->id ?>" <?= set_select('classid', $cl->id) ?>><?= htmlspecialchars($cl->classname) ?></option>
      <?php endforeach; ?>
    </select>
  </div>


        
  <p><input class="btn btn-primary" style="margin-top:10px;" type="submit" name="submit" value="<?= $this->lang->line('OK_button') ?>"></p>
</form>

<script>
    // ============== Datepicker ==============
    $(function() {
        // Datepicker

        var dateFormat = 'yy-mm-dd';
        var start_date = $('#start_date1')
            .datepicker({
                dateFormat: dateFormat,
                showWeek: true,
                firstDay: 1,
                numberOfMonths: 3
            });
        var end_date = $('#end_date1')
            .datepicker({
                dateFormat: dateFormat,
                showWeek: true,
                firstDay: 1,
                numberOfMonths: 3
            });

        start_date
            .on( 'change', function() {
                var period_start = getDate(this);
                // The period will be at most 26 weeks
                var period_end = new Date(period_start.getFullYear(), period_start.getMonth(), period_start.getDate()+26*7);
                end_date
                    .datepicker( 'option', 'minDate', period_start)
                    .datepicker( 'option', 'maxDate', period_end);
            })
            .trigger("change"); // Set initial minDate and maxDate in end_date
 
        function getDate( element ) {
            var date;
            try {
                date = $.datepicker.parseDate( dateFormat, element.value );
            }
            catch( error ) {
                date = null;
            }
 
            return date;
        }
    } );
</script>

<!-- Form used by "hyperlinks" from exercise names -->
 <form id="exform" action="<?= site_url('/lj/LJ_graph_student/view_quiz') ?>" method="post" accept-charset="utf-8">
        <input type="hidden" id="formtempl" name="templ">
        <input type="hidden" name="start_date" value="<?= $start_date ?>">
        <input type="hidden" name="end_date"   value="<?= $end_date ?>">
        <input type="hidden" name="userid"     value="<?= $userid ?>">

 </form>


<?php if ($classid==0): ?>
  <h1>Statistics for all exercises</h1>
<?php else: ?>
  <h1>Statistics for class &ldquo;<?= htmlspecialchars($myclassname) ?>&rdquo;</h1>
<?php endif; ?>
  
<?php $totaltime = array_sum($total); ?>
<?php if ($totaltime==0): ?>
    <h2>No data</h2>
<?php else: ?>

  <?php
      function compare_ignorecase(string $s1, string $s2) {
          $s1 = strtolower($s1);
          $s2 = strtolower($s2);
          if ($s1<$s2) return -1;
          if ($s1>$s2) return 1;
          return 0;
      }

      uksort($totaltemp,'compare_ignorecase');
      $totaltempnames = array();
      $totaltempvalues = array();

      $totaltempnames_html = array();
      $totaltempnames_url = array();

      foreach ($totaltemp as $name => $value) {
          $totaltempnames_html[] = "'" . htmlspecialchars($name) . "'";
          if (strlen($name)>30)
              $name='...' . substr($name,-30);
          $totaltempnames[] = "'$name'";

          $totaltempvalues[] = $value * 60;
      }

      $canvasheight = count($totaltemp)*25 + 70;
  ?>
      
  <h2>Total time spent: <?= floor($totaltime) ?> hours <?= round(($totaltime - floor($totaltime))*60) ?> minutes. </h2>
  <h2>Time spent per week</h2>
  <canvas style="background:#f8f8f8; display:block;" id="weekcanvas" width="800" height="500">
    [No canvas support]
  </canvas>
   
  <h2>Time spent on each exercise</h2>
  <canvas style="background:#f8f8f8; display:inline-block; vertical-align:top;" id="excanvas" width="800" height="<?= $canvasheight ?>">
    [No canvas support]
  </canvas>


  <script>
    function adaptScale(obj, e) {
        // Change number of decimals on y axis depending on max value
        if (obj.scale2.max < 0.05)
            obj.set('scaleDecimals', 3);
        else if (obj.scale2.max < 0.5)
            obj.set('scaleDecimals', 2);
        else if (obj.scale2.max < 5)
            obj.set('scaleDecimals', 1);
        else
            obj.set('scaleDecimals', 0);

        this.firstDraw=false; // Prevent firstdraw event from firing again. (Probably bug in RGraph.)
        RGraph.redraw();
    }

    function showTempl(quizname) {
        $('#formtempl').val(quizname);
        $('#exform').submit();
        return false;
    }


    $(function() {
        new RGraph.Bar({
            id: 'weekcanvas',
            data: [<?= implode(",", $total) ?>],
            options: {
                labels: [<?php foreach ($total as $w => $ignore) echo '"',format_week($w),'",'; ?>],
                colors: ['#f00'],
                gutterLeft: 55,
                gutterBottom: 45,
                titleYaxis: 'Hours',                  
                titleYaxisX: 12,                  
                titleXaxis: 'Week number',                  
                titleXaxisY: 490,
                textAccessible: true
            }
        }).on('firstdraw', adaptScale).draw();


        var hbarconf = {
            id: 'excanvas',
            data: [<?= implode(",", $totaltempvalues) ?>],
            options: {
                labels: [<?= implode(",", $totaltempnames) ?>],
                gutterLeftAutosize: true,
                gutterBottom: 45,
                scaleZerostart: true,
                titleXaxis: 'Minutes',                  
                titleXaxisY: <?= $canvasheight-10 ?>,
                textAccessible: true
            }
        };
        
        new RGraph.HBar(hbarconf).on('firstdraw', adaptScale).draw();

        var totaltempnames_html = [<?= implode(',', $totaltempnames_html) ?>];

        for (var i in hbarconf.options.labels) {
            var found = RGraph.text2.find({
                id: 'excanvas',
                text: hbarconf.options.labels[i]
            });

            $(found[0]).wrap('<div title="' + totaltempnames_html[i] + '"><a href="#" onclick="showTempl(\''+ totaltempnames_html[i] +'\');return false;"</a></div>')
            $(found[0]).css({color: 'blue'});
        }

        
    });
  </script>

<?php endif; ?>
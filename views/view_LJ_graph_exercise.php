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
   <h1>Statistics for exercise &ldquo;<?= htmlspecialchars($quiz) ?>&rdquo;</h1>

   <?= form_open("/lj/LJ_graph_student/view_quiz",array('method'=>'get')) ?>
    <p>Specify date period (in the UTC time zone):</p>
    <table>
      <tr>
        <td style="font-weight:bold;padding-right:5px;padding-left:20px;">From:</td>
        <td style="padding-left:5px"><input type="text" name="start_date" value="<?= $start_date ?>"></td>
      </tr>
      <tr>
        <td style="font-weight:bold;padding-right:5px;padding-left:20px;">To (and including):</td>
        <td style="padding-left:5px"><input type="text" name="end_date" value="<?= $end_date ?>"></td>
      </tr>
    </table>

  <input type="hidden" name="templ" value="<?= $quiz ?>">
  <input type="hidden" name="userid" value="<?= $userid ?>">
            
  <p><input class="btn btn-primary" style="margin-top:10px;" type="submit" name="submit" value="<?= $this->lang->line('OK_button') ?>"></p>
</form>

  <script>
        $(lj_datepicker_period('input[name="start_date"]','input[name="end_date"]'));
  </script>

<?php if ($status!=2): ?>

  <?php if ($status==0): ?>

    <h2>No data</h2>

  <?php else: ?>

    <?php
      $res = array();
      $resspf = array();
      foreach ($resall as $r) {
          $res[]    = "['$r->st',$r->pct,null,'Date: $r->st<br>Questions: $r->cnt']";
          $roundpct = round($r->pct);
          $resspf[] = "['$r->st',$r->featpermin,null,'Date: $r->st<br>Correct: $roundpct%']";
      }

      $resx    = "[[" . implode(",", $res) . "]]";
      $resxspf = "[[" . implode(",", $resspf) . "]]";
    ?>

    <canvas style="background:#f8f8f8; display:inline-block; vertical-align:top;" id="cvs" width="800" height="500">
      [No canvas support]
    </canvas>

    <canvas style="background:#f8f8f8; display:inline-block; vertical-align:top;" id="cvsspf" width="800" height="500">
      [No canvas support]
    </canvas>


    <script>
      function pad(number) {
          return number<10 ? '0'+number : number;
      }


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

      $(function() {
                
          var dataorig = <?= $resx ?>;
          var dataorigspf = <?= $resxspf ?>;


          var scatterdata = {
              id: 'cvs',
              data: null,
              options: {
                  xmin: '<?= $start_date ?>',
                  xmax: '<?= $end_date ?>',
                  gutterLeft: 70,
                  gutterBottom: 45,
                  tickmarks:  myTick,
                  ymin: 0,
                  ymax: 100,
                  shadow: false,
                  unitsPost: '%',
                  titleYaxis: 'Correct',
                  titleYaxisX: 12,
                  titleXaxis: 'Week number',
                  titleXaxisY: 490,
                  textAccessible: true,

                  line: true,
                  lineLinewidth: 2,
                  lineColors: ['#f00','#0f0','#00f','#0ff','#ff0','#f0f','#000',
                                   '#800','#080','#008','#08f','#8f0','#80f','#0f8','#f80','#f08',
                                   '#088','#880','#808',
                                   '#f88','#8f8','#88f',
                                   '#ff8','#f8f','#8ff','#888'],
                  labels: [<?php for ($w=$minweek; $w<=$maxweek; ++$w) echo '"',format_week($w),'",'; ?>],
                  numxticks: <?= $maxweek-$minweek+1 ?>,
              }
          };

          var scatterdataspf = $.extend(true, {}, scatterdata);  // This is a deep copy
          scatterdataspf.id = 'cvsspf';
          scatterdataspf.options.titleYaxis = 'Question items per minute';//'Seconds per question item';
          scatterdataspf.options.unitsPost = null;
          scatterdataspf.options.ymax = null;
          scatterdataspf.options.scaleDecimals = 1;

          scatterdata.data = RGraph.array_clone(dataorig);
          scatterdataspf.data = RGraph.array_clone(dataorigspf);
          scatter = new RGraph.Scatter(scatterdata).draw();
          scatterspf = new RGraph.Scatter(scatterdataspf).on('firstdraw', adaptScale).draw();



          var users_elem = $('input[name="users"]');
          var selectall_elem = $('input[name="selectall"]');


          var cvs = $("#cvs")[0];
          var cvsspf = $("#cvsspf")[0];

          function userchange(e) {
              var state = 0;  // 0=unknown, 1=on, 2=off, 3=indeterminate
              users_elem.each(function() {
                  switch (state) {
                  case 0:
                      state = $(this).prop('checked') ? 1 : 2;
                      break;

                  case 1:
                      if (!$(this).prop('checked')) {
                          state = 3;
                          return false;
                      }
                      break;

                  case 2:
                      if ($(this).prop('checked')) {
                          state = 3;
                          return false;
                      }
                      break;
                  }
                  return true;
              });

              switch (state) {
              case 0:
              case 1:
                  selectall_elem.prop("indeterminate", false).prop("checked", true);
                  break;

              case 2:
                  selectall_elem.prop("indeterminate", false).prop("checked", false);
                  break;

              case 3:
                  selectall_elem.prop("indeterminate", true).prop("checked", true);
                  break;
              }

              var ix = $(this).prop('value');
              if ($(this).prop('checked')) {
                  scatterdata.data[ix] = RGraph.array_clone(dataorig[ix]);
                  scatterdataspf.data[ix] = RGraph.array_clone(dataorigspf[ix]);
              }
              else {
                  scatterdata.data[ix] = [[]];
                  scatterdataspf.data[ix] = [[]];
              }

              RGraph.reset(cvs);
              RGraph.reset(cvsspf);
              scatter = new RGraph.Scatter(scatterdata).draw();
              scatterspf = new RGraph.Scatter(scatterdataspf).on('firstdraw', adaptScale).draw();
          }

          function allchange(e) {
              users_elem
                  .prop("checked", $(this).prop('checked'))
                  .trigger("change");
          }

          users_elem.change(userchange);

          selectall_elem
              .change(allchange)
              .prop("indeterminate", false);


          /**
           * The function that is called once per tickmark, to draw it
           *
           * @param object obj           The chart object
           * @param object data          The chart data
           * @param number x             The X coordinate
           * @param number y             The Y coordinate
           * @param number xVal          The X value
           * @param number yVal          The Y value
           * @param number xMax          The maximum X scale value
           * @param number xMax          The maximum Y scale value
           * @param string color         The color of the tickmark
           * @param string dataset_index The index of the data (which starts at zero
           * @param string data_index    The index of the data in the dataset (which starts at zero)
           */
          function myTick (obj, data, x, y, xVal, yVal, xMax, yMax, color, dataset_index, data_index) {
              co = document.getElementById(obj.canvas.id).getContext('2d');
              co.strokeStyle = color;
              co.fillStyle = obj.original_colors['chart.line.colors'][dataset_index];
              co.fillRect(x-4, y-4, 8, 8);
          }

      });
      </script>

  <?php endif; ?>
<?php endif; ?>

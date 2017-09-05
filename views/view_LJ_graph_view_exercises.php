

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
   <h1>Statistics for class &ldquo;<?= htmlspecialchars($classname) ?>&rdquo;</h2>

    <?= form_open("lj/LJ_graph_teacher/view_exercises?classid=$classid") ?>
    <p>Specify date period (in the UTC time zone):</p>
  <table>
    <tr>
    <td style="font-weight:bold;padding-right:5px;padding-left:20px;">From:</td><td style="padding-left:5px"><input type="text" name="start_date" value="<?= set_value('start_date',$start_date) ?>"></td>
    </tr>
    <tr>
    <td style="font-weight:bold;padding-right:5px;padding-left:20px;">To (and including):</td><td style="padding-left:5px"><input type="text" name="end_date" value="<?= set_value('end_date',$end_date) ?>"></td>
    </tr>
  </table>

  <p>&nbsp;</p>
  <div>
    <span style="font-weight:bold">Exercise:</span>
    <select name="exercise">
      <option value="" <?= set_select('exercise', '', true) ?>></option>
      <?php foreach($exercise_list as $ex): ?>
        <?php $ex2 = htmlspecialchars($ex); ?>
        <option value="<?= $ex2 ?>" <?= set_select('exercise', $ex) ?>><?= $ex2 ?></option>
      <?php endforeach; ?>
    </select>
  </div>


  <p><input class="btn btn-primary" style="margin-top:10px;" type="submit" name="submit" value="<?= $this->lang->line('OK_button') ?>"></p>
</form>

<script>
    // ============== Datepicker ==============
    $(function() {
        var dateFormat = 'yy-mm-dd';
        var start_date = $('input[name="start_date"]')
            .datepicker({
                dateFormat: dateFormat,
                showWeek: true,
                firstDay: 1,
                numberOfMonths: 3
            });
        var end_date = $('input[name="end_date"]')
            .datepicker({
                dateFormat: dateFormat,
                showWeek: true,
                firstDay: 1,
                numberOfMonths: 3
            });

        start_date
            .on('change', function() {
                var period_start = getDate(this);
                // The period will be at most 26 weeks
                var period_end = new Date(period_start.getFullYear(), period_start.getMonth(), period_start.getDate()+26*7);
                end_date
                    .datepicker('option', 'minDate', period_start)
                    .datepicker('option', 'maxDate', period_end);
            })
            .trigger("change"); // Set initial minDate and maxDate in end_date

        function getDate(element) {
            var date;
            try {
                date = $.datepicker.parseDate(dateFormat, element.value);
            }
            catch (error) {
                date = null;
            }

            return date;
        }
    });
    // ============== End Datepicker ==============
</script>

<?php if ($status!=2): ?>
  <h2>Statistics for exercise &ldquo;<?= htmlspecialchars($quiz) ?>&rdquo;</h2>

  <?php if ($status==0): ?>

    <h2>No data</h2>

  <?php else: ?>

    <?php
      foreach ($resall as $r1) {
          $res1 = array();
          foreach ($r1 as $r2)
              $res1[] = "['$r2->st',$r2->pct,null,'Date: $r2->st<br>Questions: $r2->cnt']";

          $res2[] = "[" . implode(",",$res1) . "]";
      }

      $resx = "[" . implode(",", $res2) . "]";

      $student_captions = array();
      $ix = 0;
      foreach ($students as $id => $name) {
          $student_captions[$ix] = "'<input type=\"checkbox\" checked name=\"users\" value=\"$ix\">" . addslashes($name) . "'";
          ++$ix;
      }
    ?>

    <canvas style="background:#f8f8f8; display:inline-block; vertical-align:top;" id="cvs" width="800" height="500">
      [No canvas support]
    </canvas>
    <div style="display:inline-block; vertical-align:top;">
      <div id="mykey"></div>
      <div id="allkey"><input type="checkbox" style="margin-left:20px" checked name="selectall" value="">All</div>
    </div>


    <script>
      function pad(number) {
          return number<10 ? '0'+number : number;
      }

      $(function() {
          <?php if (count($students)<2): ?>
            $('#allkey').hide();
          <?php endif; ?>
                
          var dataorig = <?= $resx ?>;
          var scatterdata = {
              id: 'cvs',
              data: null,
              options: {
                  xmin: '<?= $start_date ?>',
                  xmax: '<?= $end_date ?>',
                  gutterLeft: 60,
                  gutterBottom: 45,
                  tickmarks:  myTick,
                  numxticks: 12,
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

          scatterdata.data = RGraph.array_clone(dataorig);
          scatter = new RGraph.Scatter(scatterdata).draw();

          RGraph.HTML.Key('mykey', {
              'colors': scatter.Get('colors'),
              'labels': [<?= implode(",", $student_captions) ?> ]
              });


          var users_elem = $('input[name="users"]');
          var selectall_elem = $('input[name="selectall"]');


          var cvs = $("#cvs")[0];

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
              if ($(this).prop('checked'))
                  scatterdata.data[ix] = RGraph.array_clone(dataorig[ix]);
              else
                  scatterdata.data[ix] = [[]];

              RGraph.reset(cvs);
              scatter = new RGraph.Scatter(scatterdata).draw();
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
              co = document.getElementById('cvs').getContext('2d');
              co.strokeStyle = color;
              co.fillStyle = obj.original_colors['chart.line.colors'][dataset_index];
              co.fillRect(x-4, y-4, 8, 8);
          }

  //        setTimeout(function() {
  //           scatter.interactiveKeyHighlight(7);
  //        },6000);
  //
  //        setTimeout(function() {
  //           scatter.interactiveKeyHighlight(2);
  //        },3000);
  //
  //        scatter.oldInteractiveKeyHighlight = function (index)
  //        {
  //            // This is modelled on RGraph.scatter.js
  //            if (this.coords && this.coords[index] && this.coords[index].length) {
  //                this.coords[index].forEach(function (value, idx, arr)
  //                {
  //                    co.beginPath();
  //                    co.fillStyle = 'rgba(255,0,0,0.3)'
  //                    co.arc(value[0], value[1], 10, 0, RGraph.TWOPI, false);
  //                    co.fill();
  //                });
  //            }
  //        };
  //
  //        scatter.interactiveKeyHighlight = function (index)
  //        {
  //            if (dataorig[index].length==1)
  //                return scatter.oldInteractiveKeyHighlight(index);
  //
  //            // This is modelled on RGraph.line.js
  //            var coords = this.coords[index];
  //
  //            if (coords) {
  //
  //                var pre_linewidth = co.lineWidth;
  //                var pre_linecap   = co.lineCap;
  //
  //                co.lineWidth   = 2.01 + 10;
  //                co.lineCap     = 'round';
  //                co.strokeStyle = 'rgba(255,0,0,0.3)';
  //
  //
  //                co.beginPath();
  //                for (var i=0,len=coords.length; i<len; i+=1) {
  //                    if (  i == 0
  //                           || RGraph.is_null(coords[i][1])
  //                           || (typeof coords[i - 1][1] != undefined && RGraph.is_null(coords[i - 1][1]))) {
  //                        co.moveTo(coords[i][0], coords[i][1]);
  //                    } else {
  //                        co.lineTo(coords[i][0], coords[i][1]);
  //                        }
  //                }
  //                co.stroke();
  //
  //                // Reset the lineCap and lineWidth
  //                co.lineWidth = pre_linewidth;
  //                co.lineCap = pre_linecap;
  //            }
  //        };

      });
      </script>

  <?php endif; ?>
<?php endif; ?>

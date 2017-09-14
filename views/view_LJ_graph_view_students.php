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

  <h1>Statistics for class &ldquo;<?= htmlspecialchars($classname) ?>&rdquo;</h1>
  <?= form_open("lj/LJ_graph_teacher/view_students",array('method'=>'get')) ?>
    <input type="hidden" name="classid" value="<?= $classid ?>">

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

    <p><input class="btn btn-primary" style="margin-top:10px;" type="submit" name="submit" value="<?= $this->lang->line('OK_button') ?>"></p>
  </form>

  <script>
        $(lj_datepicker_period('input[name="start_date"]','input[name="end_date"]'));
  </script>

<?php if ($status==1): ?>
  <?php if (array_sum($total)==0): ?>

    <h2>No data</h2>
  <?php else: ?>

  <?php
      $durx = array();
      foreach ($dur as $w => $val)
          $durx[$w] = '[' . implode(',',$val) . ']';
   
      $student_captions = array();
      $ix = 0;
      foreach ($students as $id => $name) {
          $student_captions[$ix] = "'<input type=\"checkbox\" checked name=\"users\" value=\"$ix\">" . addslashes($name) . "'";
          ++$ix;
      }
  ?>
      
  <h2>Time spent by all students</h2>
  <canvas style="background:#f8f8f8; display:block;" id="totalcanvas" width="800" height="500">
    [No canvas support]
  </canvas>
   
  <h2>Time spent by each student</h2>
  <canvas style="background:#f8f8f8; display:inline-block; vertical-align:top;" id="studentscanvas" width="800" height="500">
    [No canvas support]
  </canvas>
  <div style="display:inline-block; vertical-align:top;">
    <div id="mykey"></div>
    <div id="allkey"><input type="checkbox" style="margin-left:20px" checked name="selectall" value="">All</div>
  </div>


  <script>
    function set_config(config,on,data,colors) {
        config.data = [];
        config.options.colors = [];

        no_of_periods = data.length;
        no_of_students = data.length==0 ? 0 : data[0].length;
        if (no_of_students<2)
            $('#allkey').hide();

        
        for (s=0; s<no_of_students; ++s)
            if (on[s])
                config.options.colors.push(colors[s]);

        for (p=0; p<no_of_periods; ++p) {
            config.data.push([]);
            for (s=0; s<no_of_students; ++s)
                if (on[s])
                    config.data[p].push(data[p][s]);
        }
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
        var bar1 = new RGraph.Bar({
            id: 'totalcanvas',
            data: [<?= implode(",", $total) ?>],
            options: {
                labels: [<?php foreach ($total as $w => $ignore) echo '"',format_week($w),'",'; ?>],
                colors: ['#f00'],
                gutterLeft: 55,
                gutterBottom: 45,
                hmargin: 7,
                hmarginGrouped: 1,
                titleYaxis: 'Hours',                  
                titleYaxisX: 12,                  
                titleXaxis: 'Week number',                  
                titleXaxisY: 490,
                textAccessible: true
            }
        }).on('firstdraw', adaptScale).draw();

    
        var bar2colors = ['#f00','#0f0','#00f','#0ff','#ff0','#f0f','#000',
                          '#800','#080','#008','#08f','#8f0','#80f','#0f8','#f80','#f08',
                          '#088','#880','#808',
                          '#f88','#8f8','#88f',
                          '#ff8','#f8f','#8ff','#888'];
        var bar2on = [<?php for ($i=0; $i<count($student_captions); ++$i) echo 'true,'; ?>];
        var bar2data = [<?= implode(",", $durx) ?>];

        var studentscanvas = $("#studentscanvas")[0];
    
        var bar2config = {
            id: 'studentscanvas',
            data: null,
            options: {
                labels: [<?php foreach ($total as $w => $ignore) echo '"',format_week($w),'",'; ?>],
                colors: null,
                gutterLeft: 55,
                gutterBottom: 45,
                hmargin: 7,
                hmarginGrouped: 1,
                titleYaxis: 'Hours',                  
                titleYaxisX: 12,                  
                titleXaxis: 'Week number',                  
                titleXaxisY: 490,
                textAccessible: true
            }
        };

        set_config(bar2config,bar2on,bar2data,bar2colors);
        var bar2  = new RGraph.Bar(bar2config).on('firstdraw', adaptScale).draw();

        RGraph.HTML.Key('mykey', {
            'colors': bar2.Get('colors'),
            'labels': [<?= implode(",", $student_captions) ?> ]
        });


        var users_elem = $('input[name="users"]');
        var selectall_elem = $('input[name="selectall"]');

        function userchange(e) {
            bar2on[$(this).prop('value')] = $(this).prop('checked');

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
            
            set_config(bar2config,bar2on,bar2data,bar2colors);
            RGraph.reset(studentscanvas);
            bar2  = new RGraph.Bar(bar2config).on('firstdraw', adaptScale).draw();
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
    });
  </script>
  
  <?php endif; ?>
<?php endif; ?>
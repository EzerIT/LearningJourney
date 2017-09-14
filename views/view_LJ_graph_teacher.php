<!--pre><?php print_r($students); ?></pre>

<pre><?php print_r($classes); ?></pre-->

<?php $valerr = validation_errors();
      if (!empty($valerr))
          echo "<div class=\"alert alert-danger\">$valerr</div>\n";
    ?>


<?= form_open('lj/LJ_graph_teacher') ?>


<p><select name="classid">
  <option value="">Select class</option>
  <?php foreach($classes as $cl): ?>
    <option value="<?= $cl->id ?>" <?= /*set_select('book_'.$db['name'], $book_name[0])*/'' ?>><?= $cl->classname ?></option>
  <?php endforeach; ?>
</select></p>

<p><?php foreach($classes as $cl): ?>
  <select name="userid_<?= $cl->id ?>">
    <option value="">Select student</option>
    <?php foreach($students[$cl->id] as $st): ?>
      <option value="<?= $st->userid ?>" <?= /*set_select('book_'.$db['name'], $book_name[0])*/'' ?>><?= $st->name ?></option>
    <?php endforeach; ?>
  </select>
<?php endforeach; ?></p>

<input type="text" name="start_date" value="<?= set_value('start_date','1990-01-01') ?>">

<p><input class="btn btn-primary" type="submit" name="submit" value="<?= $this->lang->line('Submit_button') ?>"></p>

</form>

<script>
  $(function() {
      var allSelectors = $('select[name^="userid_"]');

      $('select[name="classid"]').on('change',null, function(e) {
          allSelectors.hide();
          $('select[name="userid_' + $(this).prop('value') + '"]').show().trigger('change');
      });

      allSelectors.hide();
  });
</script>


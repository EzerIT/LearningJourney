<p><?= sprintf($this->lang->line('grading_logbook_left'), $name) ?></p>
<a href="#" onclick="goBack(); return false;">Back</a>
<br/>
<a href="<?= site_url('lj/LJ_grading_system') ?>">Home</a>

<script>
    function goBack()
    {
        window.history.back();
    }
</script>

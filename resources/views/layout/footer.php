<?php

global $PAGE, $CFG, $OUTPUT;
?>

<input type="hidden" id="modal_input" data-toggle="modal" data-target=".core-modal">
<div id="modal_container"></div>
<script type="text/javascript">
    $(document).ready(function() {
        rebuild_vendors();
    });
</script>
<?php
echo $OUTPUT->footer(); ?>
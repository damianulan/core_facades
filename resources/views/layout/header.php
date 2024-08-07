<?php

global $PAGE, $CFG, $OUTPUT;

require_login();
$request = request();

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('base');
$PAGE->set_pagetype('index');

$PAGE->set_title($pagetitle);
$PAGE->set_pagetype($request->core_module);

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
?>
<script type="text/javascript" src="<?=$CFG->wwwroot . '/assets/lib'?>/sweetalert2/sweetalert2.all.min.js"></script>
<?php
$PAGE->requires->css( new moodle_url($CFG->wwwroot . '/assets/lib/trix-editor/trix.css'));
$PAGE->requires->css( new moodle_url($CFG->wwwroot . '/assets/lib/sweetalert2/theme-core.css'));
$PAGE->requires->css( new moodle_url($CFG->wwwroot . '/local/core_facades/public/assets/core.css'));

echo $OUTPUT->header();
require_once('scripts.php'); 
if(isset($breadcrumbs)){
    $breadcrumbs->render();
}
require_once('pageheading.php'); 
?>

<style>
    #page-header {
        display: none !important;
    }

    .trix-button-group--file-tools {
        display: none !important;
    }
</style>
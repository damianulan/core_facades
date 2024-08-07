<?php
// Include me whenever You wish to receive notifications

$alerts = get_alerts();

if(isset($alerts['success']) && count($alerts['success'])){
    foreach($alerts['success'] as $message){
?>
        <div class="alert alert-info alert-dismissable" role="alert">
            <?= $message ?>
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">×</span>
                <span class="sr-only"><?= get_string('dismiss_notification', 'local_core_facades') ?></span>
            </button>
        </div>
<?php 
    }
unset(session()->alerts['success']);
}

if(isset($alerts['warning']) && count($alerts['warning'])){
    foreach($alerts['warning'] as $message){
?>
        <div class="alert alert-info alert-dismissable" role="alert">
            <?= $message ?>
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">×</span>
                <span class="sr-only"><?= get_string('dismiss_notification', 'local_core_facades') ?></span>
            </button>
        </div>
<?php 
    }
unset(session()->alerts['warning']);
}

if(isset($alerts['error']) && count($alerts['error'])){
    foreach($alerts['error'] as $message){
?>
        <div class="alert alert-info alert-dismissable" role="alert">
            <?= $message ?>
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">×</span>
                <span class="sr-only"><?= get_string('dismiss_notification', 'local_core_facades') ?></span>
            </button>
        </div>
<?php 
    }
unset(session()->alerts['error']);
}

if(isset($alerts['info']) && count($alerts['info'])){
    foreach($alerts['info'] as $message){
?>
        <div class="alert alert-info alert-dismissable" role="alert">
            <?= $message ?>
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">×</span>
                <span class="sr-only"><?= get_string('dismiss_notification', 'local_core_facades') ?></span>
            </button>
        </div>
<?php 
    }
unset(session()->alerts['info']);
}
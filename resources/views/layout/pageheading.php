<header class="core-header">
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <h1><?=$pageheading?></h1>
        </div>
        <div class="col-md-6 col-sm-12 self-center">
            <div class="text-right text-dimmed">
                <span class="pointer tippy" data-tippy="<?=get_string('development_info', 'local_core_facades')?>"><?=development() ? get_string('development', 'local_core_facades'):''?></span>
            </div>
        </div>
    </div>

</header>

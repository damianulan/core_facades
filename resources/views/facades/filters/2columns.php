<div class="accordion filter-container" id="<?=$uuid?>">
  <div class="">
    <div id="filterHeading">
        <button class="btn btn-accordion" type="button" data-toggle="collapse" data-target="#filterBody" aria-expanded="<?= $builder->isVisible() ? 'true':'false' ?>" aria-controls="filterBody">
            <?= $builder->isVisible() ? get_string('hide_filters', 'local_core_facades'):get_string('show_filters', 'local_core_facades') ?>
        </button>
    </div>
    <div id="filterBody" class="collapse<?= $builder->isVisible() ? ' show':'' ?>" aria-labelledby="filterHeading" data-parent="#<?=$uuid?>">
      <div class="filter-body">
        <input type="hidden" name="formid" value="<?=$uuid?>"/>
        <div class="row">
            <?php foreach($filters as $filter): ?>

            <div class="col-md-6 col-xs-12">
                <?php 
                foreach($filter as $obj):
                    $obj->render();

                endforeach; ?>
            </div>

            <?php endforeach; ?>
        </div>
        <div class="filter-btns">
            <button class="btn btn-primary mr-2 filter-submit"><?=get_string('filtruj', 'local_core_facades')?></button>
            <button class="btn btn-secondary filter-cancel"><?=get_string('cancel', 'local_core_facades')?></button>
        </div>
      </div>
    </div>
  </div>
</div>
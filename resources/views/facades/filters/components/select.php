<?php
    $name = $object->name();

    $id = $object->alias() . $name;
    $input_name = $object->multiple ? $id.'[]':$id;
?>

<div class="form-group row">
    <label for="<?=$id?>" class="col-sm-3 col-form-label col-form-label-sm"><?=$object->label?></label>
    <div class="col-sm-9">
      <select class="form-control<?=$object->chosen ? ' chosen':''?>" id="<?=$id?>" name="<?=$input_name?>"<?=$object->multiple ? ' multiple':''?>>
        <?php foreach($object->options() as $option): ?>
            <option value="<?=$option->id?>">
                <?=$option->text?>
            </option>
        <?php endforeach; ?>
      </select>
    </div>
 </div>

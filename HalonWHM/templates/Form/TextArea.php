<?php if($enableLabel): ?>
    <label for="<?php echo $formName.'_'.$name; ?>" class="col-md-4 control-label"><?php echo MGLang::T('label'); ?></label>
<?php endif; ?>
<div class="col-md-6">
    <textarea class="form-control" name="<?php echo $formName.'_'.$name; ?>" rows="<?php echo $rows?>" cols="<?php echo $cols?>" id="<?php echo $formName.'_'.$name; ?>" placeholder="<?php if($enablePlaceholder) echo MGLang::T('placeholder'); ?>"><?php echo $value; ?></textarea>
    <?php if($enableDescription): ?>
    <span class="help-block"><?php echo MGLang::T('description'); ?></span>
  <?php endif;?>
</div>

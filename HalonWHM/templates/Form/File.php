<?php if($enableLabel): ?>
    <label for="<?php echo $formName.'_'.$name; ?>" class="col-md-4 control-label"><?php echo MGLang::T('label'); ?></label>
<?php endif; ?>
<div class="col-md-6">
  <input class="pull-left" rel="txtTooltip" name="<?php echo $formName.'_'.$name; ?>" type="file" value="<?php echo $value; ?>"  class="form-control" id="<?php echo $formName.'_'.$name; ?>" placeholder="<?php if($enablePlaceholder) echo MGLang::T('placeholder'); ?>">
  <?php if($enableDescription): ?>
    <span class="help-block"><?php echo MGLang::T('description'); ?></span>
  <?php endif;?>
</div>

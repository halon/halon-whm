<?php if($enableLabel): ?>
    <label for="<?php echo $formName.'_'.$name; ?>" class="col-sm-2 control-label"><?php echo MGLang::T('label'); ?></label>
<?php endif; ?>
<div class="col-sm-4">
    <label>
        <input name="<?php echo $formName.'_'.$name; ?>" type="checkbox" <?php if($value): ?>checked<?php endif; ?>  id="<?php echo $formName.'_'.$name; ?>" />
        <?php if($enableDescription): ?>
          <span class="help-block"><?php echo MGLang::T('label'); ?></span>
        <?php endif;?>
    </label>
</div>
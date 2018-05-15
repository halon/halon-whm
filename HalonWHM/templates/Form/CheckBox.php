<?php if($enableLabel): ?>
    <label for="<?php echo $formName.'_'.$name; ?>" class="col-md-4 control-label"><?php echo MGLang::T('label'); ?></label>
<?php endif; ?>
<div class="col-md-6">
    <label>
        <input name="<?php echo $formName.'_'.$name; ?>" type="checkbox" <?php if($value): ?>checked<?php endif; ?>  id="<?php echo $formName.'_'.$name; ?>" />
        <?php if($enableDescription): ?>
          <span class="help-block"><?php echo MGLang::T('label'); ?></span>
        <?php endif;?>
    </label>
</div>

<?php if(!$skipLabel): ?>
    <label class="col-md-4 control-label"></label>
<?php endif;?>
<?php if(!$disableStartContainer): ?>
    <div class="col-md-6">
<?php endif;?>
    <button type="submit" class="btn btn-success" name="<?php echo $name; ?>" value="<?php echo $value;?>"><?php echo MGLang::T('label'); ?></button>
<?php if(!$disableFinishContainer): ?>
    </div>
<?php endif; ?>

<?php if(!$skipLabel): ?>
    <label class="col-sm-2 control-label"></label>
<?php endif;?>
<?php if(!$disableStartContainer): ?>
    <div class="col-sm-4">
<?php endif;?>
    <button type="submit" class="btn btn-success" name="<?php echo $name; ?>" value="<?php echo $value;?>"><?php echo MGLang::T('label'); ?></button>
<?php if(!$disableFinishContainer): ?>
    </div>
<?php endif; ?>
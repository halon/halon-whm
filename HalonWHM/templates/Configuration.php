<div class="col-lg-10 col-lg-offset-1 configurationMessage">
    <?php if(!empty($error)): ?>
        <div class="alert alert-danger">
            <button type="button" class="close closeMessage" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <?php echo $error; ?>
        </div>
    <?php endif; ?>
    <?php if(!empty($success)): ?>
        <div class="alert alert-success">
            <button type="button" class="close closeMessage" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <?php echo $success; ?>
        </div>
    <?php endif; ?>
</div>
<div class="col-lg-10 col-lg-offset-1 configurationForm">
    <?php echo $configurationForm; ?>
</div>
<script type="text/javascript" src="./assets/js/configurationScript.js"></script>

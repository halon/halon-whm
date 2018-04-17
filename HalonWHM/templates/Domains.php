<div class="col-sm-10 col-sm-offset-1">
    <div class="alert alert-success" id="successMessage" style="display:none">
        <button type="button" class="close closeMessage" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <span class="content"></span>
    </div>
    <div class="alert alert-danger" id="errorMessage" style="display:none">
        <button type="button" class="close closeMessage" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <span class="content"></span>
    </div> 
    <div class="alert alert-info" id="bulkMessage" style="display:none">
        <button type="button" class="close closeMessage" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <span class="content"></span>
    </div> 
    <?php if(!empty($error)): ?>
        <div class="alert alert-danger">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>
    <?php if(!empty($success)): ?>
        <div class="alert alert-success">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>
</div>
<div class="col-sm-10 col-sm-offset-1">
    <?php if(!empty($domains)): ?>
    <table id="domains" class="table table-striped" cellspacing="0">
        <thead>
            <td class="col-sm-1"></td>
            <td class="col-sm-4"><?php echo MGLang::T('domainColumnName'); ?></td>
            <td class="col-sm-3" data-statusEnabledValue="<?php echo MGLang::T('enabledStatus'); ?>" data-statusDisabledValue="<?php echo MGLang::T('disabledStatus'); ?>">
                <?php echo MGLang::T('domainStatusColumnName'); ?>
            </td>
            <td class="col-sm-2"><?php echo MGLang::T('toggleProtectionColumnName'); ?></td>
        </thead>   
    </table>  
    <div class="buttons">
        <button id="toggleItems" class="btn btn-primary" data-checkAllText="<?php echo MGLang::T('checkAllItemsButtonValue'); ?>"
                data-uncheckAllText="<?php echo MGLang::T('uncheckAllItemsButtonValue'); ?>" data-status="allUnselected">
            <?php echo MGLang::T('checkAllItemsButtonValue'); ?>
        </button>
        <button id="enableProtectionForAllDomains" class="btn btn-primary" data-type="enable"><?php echo MGLang::T('enableProtectionForAllDomainsButtonValue'); ?></button>
        <button id="disableProtectionForAllDomains" class="btn btn-primary" data-type="disable"><?php echo MGLang::T('disableProtectionForAllDomainsButtonValue'); ?></button>
    </div>
<?php else: ?>
    <p class="noDomainsMessage"><?php echo MGLang::T('noDomainsMessage'); ?></p>
<?php endif; ?>
</div>
<script type="text/javascript" src="./assets/js/domainsScript.js"></script>
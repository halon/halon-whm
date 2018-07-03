<div class="col-sm-12">
    <?php if(!empty($error)): ?>
        <div class="alert alert-danger">
            <?php echo $error; ?>
            <?php if(!empty($errorID)): ?>
                <strong>
                    <?php echo MGLang::T('errorID'); ?> <?php echo $errorID; ?>
                </strong>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <div class="appsListFilters errorFinder">
        <form action="" method="post" class="form-inline">
            <div class="form-group col-sm-4">
                <div class="input-group" >
                      <input id="tokenID" name="tokenID" type="text" class="form-control" value="<?php if(!empty($tokenID)) echo $tokenID; ?>">
                      <span class="input-group-btn">
                          <button class="btn btn-info" type="submit" name="action" value="SearchToken" placeholder="<?php echo MGLang::T('findByToken'); ?>"><i class="glyphicon glyphicon-search"></i></button>
                      </span>
                </div>
            </div>
        </form>
    </div>
    <div class="clearfix" style="margin-bottom:10px;"></div>
    <?php if(!empty($showError)): ?>
        <pre>
            <?php echo $showError; ?>
        </pre>    
    <?php endif; ?>
    <?php if(!empty($tokenList)): ?>
        <h3><?php echo MGLang::T('errorList'); ?></h3>
        <table id="errorList" class="display" cellspacing="0">
            <thead><tr><td></td></tr></thead>
            <?php foreach($tokenList as $data): ?>
            <tr class="error-list">
                <td><a href="index.php?page=ErrorList&action=SearchToken&tokenID=<?php echo $data['token']; ?>">#<?php echo $data['token']; ?></a> <?php echo $data['message']; ?> <span class="label label-primary pull-right"><?php echo $data['date']; ?></span></td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>

<script>
    $(document).ready(function() {
        $('#errorList').DataTable({
            "dom": 't<"pull-right" p>',
            "ordering": false,
            "info":     false,
            "pageLength": 25
        });
    } );
</script>

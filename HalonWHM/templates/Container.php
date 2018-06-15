<?php
    require_once('/usr/local/cpanel/php/WHM.php');
    WHM::header(MGLang::T('header'), 1, 1);
?>
<link rel="stylesheet" type="text/css" href="./assets/css/datatables.min.css"/>
<link rel="stylesheet" type="text/css" href="./assets/css/style.css"/>
<script type="text/javascript" src="./assets/js/jquery-1.11.0.min.js"></script>
<script type="text/javascript" src="./assets/js/datatables.min.js"></script>
<script type="text/javascript" src="./assets/js/ajaxParser.js"></script>
<script type="text/javascript">
    ajaxParser.create('<?php echo $currenAjax; ?>');
</script>
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="masthead">
                <ul class="nav nav-tabs">
                    <?php foreach ($pages as $page): ?>
                        <li <?php if ($currentPage == $page): ?>class="nav-item active"<?php endif; ?>>
                            <a class="nav-link" href="index.php?page=<?php echo $page; ?>"><?php echo MGLang::T('pages', $page); ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="row" id="MGErrors">
        <div class="col-lg-10 col-lg-offset-1">
            <?php if(!empty($error)): ?>
                <div class="alert alert-danger">
                    <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only"><?php echo MGLang::T('close'); ?></span></button>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            <?php if(!empty($success)): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            <div class="alertContainer">
                <div class="alertPrototype" style="display: none;">
                    <div class="alert alert-danger">
                        <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only"><?php echo MGLang::T('close'); ?></span></button>
                        <strong></strong>
                        <a style="display: none;" class="errorID" href=""></a>
                    </div>
                </div>
                <div class="alertPrototype" style="display: none;">
                    <div class="alert alert-success">
                        <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only"><?php echo MGLang::T('close'); ?></span></button>
                        <strong></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <?php echo $content; ?>
    </div>
</div>
<?php
    WHM::footer();
?>
<div id="MGLoader" style="display: none;">
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="progress progress-striped active">
                <div class="progress-bar" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div>
            </div>
        </div>
    </div>
</div>

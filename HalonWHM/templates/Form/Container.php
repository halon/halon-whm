<form class="form-horizontal" action="<?php echo $url; ?>" method="post" <?php echo isset($enctype) && !empty($enctype) ? 'enctype="'.$enctype.'"' : ''; ?> >
    <?php foreach($htmlFields as $field):?>
          <div class="form-group">
              <?php echo $field; ?>
          </div>
    <?php endforeach; ?>
</form>
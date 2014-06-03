<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
  </head>

  <body>
    <div class="navbar navbar-default navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <a class="navbar-brand" href="<?php echo $this->config->site_url();?>">PhoneBook Manager</a>
        </div>
        <div class="navbar-collapse collapse">
          <ul class="nav navbar-nav">
          </ul>
          <ul class="nav navbar-nav navbar-right">
           
          </ul>
        </div>
      </div>
    </div>

    <div class="container" style="margin-top:30px;">
      <div class="jumbotron">
        <?php if($result == 'success') { ?> 
            <p class="bg-success">Records updated : <?php echo $update_count; ?></p>
            <p class="bg-success">New Records : <?php echo $new_count; ?></p>
            <p class="bg-success">Records deleted : <?php echo $delete_count; ?></p>
            <p class="bg-success">Records Not changed : <?php echo $not_changed; ?></p>
        <?php } elseif($result == 'error') { ?>
            <p class="bg-danger"> <?php echo $message; ?> </p>
        <?php } ?>
      </div>
    </div>
  </body>
</html>

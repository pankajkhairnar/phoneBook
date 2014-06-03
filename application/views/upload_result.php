<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <script src="<?php echo $this->config->item('base_url').'assets/js/jquery-1.11.1.min.js'; ?>"></script>
    <script src="<?php echo $this->config->item('base_url').'assets/js/bootstrap.min.js'; ?>"></script>
    <script src="<?php echo $this->config->item('base_url').'assets/js/bootbar.js'; ?>"></script>
    <!--script src="<?php //echo $this->config->item('base_url').'assets/js/modal.js'; ?>"></script-->
    <link href="<?php echo $this->config->item('base_url'); ?>assets/css/bootstrap.css" rel="stylesheet">
    <link href="<?php echo $this->config->item('base_url'); ?>assets/css/bootbar.css" rel="stylesheet">
   <script type="text/javascript">
   var siteUrl = "<?php echo $this->config->site_url(); ?>";
   </script>
    <script type="text/javascript">
    jQuery(document).ready(function(){
    });
    </script>
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
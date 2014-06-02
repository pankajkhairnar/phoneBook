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

    	var bootbarOptions = {
								autoDismiss: true,      
								autoLinkClass: true,     
								dismissTimeout: 3000,    // 3 Seconds
								dismissEffect: "slide",
								dismissSpeed: "slow",
								onDraw: null,
								onDismiss: null
							};

		$('#addNewContact').click(function(){
			var contactName   = $('#newContactName').val();
			var contactNumber = $('#newPhoneNumber').val();
			var contactEmail  = $('#newContactEmail').val();
			var createUrl     = siteUrl+'/phonebook/create';
			
			var data = {contact_name:contactName, contact_number:contactNumber, 
				contact_email:contactEmail};

			$.post(createUrl, data, function(response){				
				if(response.result == 'success') {
					$('#errorDiv').html(response.message).removeClass('bg-danger').addClass('bg-success');
					//refresining page after 3 seconds
					window.setTimeout(function(){
						location.reload();	
					}, 3000);
				} else {
					$('#errorDiv').html(response.message);
					//$.bootbar.danger(response.message, bootbarOptions);
				}
			},'json');

		});				
    	$('#nameFilter').keyup(function() {
		    var that = this;
		    $.each($('tr'),
			    function(i, val) {
			        if ($(val).text().indexOf($(that).val()) == -1) {
			        	if($('tr').eq(i).attr('id') != 'filterRow' && $('tr').eq(i).attr('id') != 'tableHead') {
			            	$('tr').eq(i).hide();
			        	}
			        } else {
			            $('tr').eq(i).show();
			        }
				});
		});

		$('.edit').click(function(){
			var recordId = $(this).attr('record_id');
			var parentRow = $(this).closest("tr");//find parent tr

			var contactName   = parentRow.find("td:eq(1)").html(); //get Contact Name
			var contactNumber = parentRow.find("td:eq(2)").html(); //get Contact Number
			var contactEmail  = parentRow.find("td:eq(3)").html(); //get Contact Email

			parentRow.find("td:eq(1)").html('<input type="text" value="'+ contactName +'" id="cname_'+ recordId +'">');	
			parentRow.find("td:eq(2)").html('<input type="text" value="'+ contactNumber +'" id="cnumber_'+ recordId +'">');	
			parentRow.find("td:eq(3)").html('<input type="text" value="'+ contactEmail +'" id="cemail_'+ recordId +'">');
			$(this).hide();
			$('#save_'+recordId).show();
		});

		$('.save').click(function(){
			var recordId = $(this).attr('record_id');
			var contactName   = $('#cname_'+recordId).val(); //get Contact Name
			var contactNumber = $('#cnumber_'+recordId).val(); //get Contact Number
			var contactEmail  = $('#cemail_'+recordId).val(); //get Contact Email

			var updateUrl =  siteUrl+'/phonebook/update';
			var data = {contact_id:recordId, contact_name:contactName, 
						contact_number:contactNumber, contact_email:contactEmail};

			$.post(updateUrl, data, function(response){
				
				if(response.result == 'success') {
					var contatId = response.contact_id;
					var parentRow = $('#row_'+contatId);
					parentRow.find("td:eq(1)").html($('#cname_'+ recordId).val());	
					parentRow.find("td:eq(2)").html($('#cnumber_'+ recordId).val());	
					parentRow.find("td:eq(3)").html($('#cemail_'+ recordId).val());
					$('#save_'+recordId).hide();
					$('#edit_'+recordId).show();
					$.bootbar.success(response.message, bootbarOptions);
				} else {
					$.bootbar.danger(response.message, bootbarOptions);
				}
			}, 'json');
			
		});

		$('.delete').click(function(){
			var recordId = $(this).attr('record_id');
			var row = '#row_'+recordId;
			var deleteUrl =  siteUrl+'/phonebook/delete';
			$.post(deleteUrl, {contact_id:recordId}, function(response){
				if(response.result == 'success') {
					$(row).hide('slow');
					$.bootbar.success(response.message, bootbarOptions);
				} else {
					$.bootbar.danger(response.message, bootbarOptions);	
				}
			}, 'json');
		});

		$('#download').click(function(){
			$.bootbar.success('Phonbook csv downloaded', bootbarOptions);
		});
    });
    </script>
  </head>

  <body>
    <div class="navbar navbar-default navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <a class="navbar-brand" href="#">PhoneBook Manager</a>
        </div>
        <div class="navbar-collapse collapse">
          <ul class="nav navbar-nav">
          </ul>
          <ul class="nav navbar-nav navbar-right">
            <li><a href="#NewContact" data-target="#newContactDialog"
            		class="glyphicon glyphicon-plus">NewContact</a></li>

            <li class="">
            	<a href="#import" data-toggle="modal" data-target="#importContact" 
            		class="glyphicon glyphicon-import">Import</a>
        	</li>
    		
    		<li><a  class="glyphicon glyphicon-export" id="download"
            href="<?php echo $this->config->site_url().'/phonebook/export';?>">Export</a></li>
          </ul>
        </div>
      </div>
    </div>

    <div class="container" style="margin-top:30px;">
      <div class="jumbotron">
        <table class="table table-striped">
		   <thead>
		      <tr id="tableHead">
		         <th>#</th>
		         <th>Full Name</th>
		         <th>Contact Number</th>
		         <th>Email</th>
		         <th></th>
		      </tr>
		   </thead>
		   <tbody>
		   <tr id="filterRow">
		   	<td>&nbsp;</td>
		   	<td>
		   		<div class="right-inner-addon">
				    <i class="glyphicon glyphicon-search"></i>
				    <input class="form-control" type="text" id="nameFilter">
				</div>
		   	</td>
		   	<td>&nbsp;</td>
		   	<td>&nbsp;</td>
		   	<td>&nbsp;</td>
		   	<td>&nbsp;</td>
		   </tr>
		    <?php 
		    	$index = 1;
		    	foreach($records as $record) { ?>
			      <tr id="<?php echo 'row_'.$record->id; ?>">
			         <td><?php echo $index; ?></td>
			         <td><?php echo $record->full_name;?></td>
			         <td><?php echo $record->phone_number;?></td>
			         <td><?php echo $record->email;?></td>
			         <td>
			         	<a href="#edit" class="btn btn-primary btn-small edit" 
			         		id="edit_<?php echo $record->id; ?>" record_id="<?php echo $record->id; ?>">
			         		<i class="glyphicon glyphicon-edit"></i> Edit</a>


			         	<a href="#save" class="btn btn-success btn-small save" style="display:none;"
			         		id="save_<?php echo $record->id; ?>" record_id="<?php echo $record->id; ?>">
			         		<i class="glyphicon glyphicon-floppy-save"></i> Save </a>
	         		 </td>
			         <td>
			         	<a href="#delete" class="btn btn-danger btn-small delete" record_id="<?php echo $record->id; ?>" >
			         		<i class="glyphicon glyphicon-remove"></i>Delete</a>
			         </td>
			      </tr>
			<?php 
			    	$index++;
			    } 
		    ?>
		   </tbody>
		</table>
      </div>
    </div>

    	<div class="modal fade" id="importContact">
			  <div class="modal-dialog">
			    <div class="modal-content">
			      <div class="modal-header">
			        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			        <h4 class="modal-title">Import contacts</h4>
			      </div>
			      <form class="form-horizontal" action="<?php echo $this->config->site_url().'/phonebook/import?XDEBUG_SESSION_START';?>" 
			      			enctype="multipart/form-data" role="form" method="post">

				      <div class="modal-body">
				      	  <div class="form-group">
						    <label for="newContactEmail" class="col-sm-4 control-label">Value Delimiter</label>
						    <div class="col-sm-8">
						      <span><input type="radio" name="delimeter" value="tab" checked="checked">Tab</span>
						      <span><input type="radio" name="delimeter" value="comma">Comma</span>
						    </div>
						  </div>

						  <div class="form-group">
						    <label for="newContactEmail" class="col-sm-4 control-label">Contacts file</label>
						    <div class="col-sm-8">
						      <input type="file" name="contacts" style="margin-top:8px;"  />
						    </div>
						  </div>

				      </div>
				      <div class="modal-footer">
				        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				        <button type="submit" class="btn btn-primary" id="addNewContact">Import Contacts</button>
				      </div>
			      </form>
			    </div>
			  </div>
			</div>


	    
		    <div class="modal fade" id="newContactDialog">
			  <div class="modal-dialog">
			    <div class="modal-content">
			      <div class="modal-header">
			        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			        <h4 class="modal-title">New Contact</h4>
			      </div>
			      <div class="modal-body">

			      	<form class="form-horizontal" role="form">
					  <div class="form-group">
					    <label for="newContactName" class="col-sm-4 control-label">Contact Name</label>
					    <div class="col-sm-8">
					      <input type="text" class="form-control" id="newContactName" placeholder="Contact Name">
					    </div>
					  </div>

					  <div class="form-group">
					    <label for="newPhoneNumber" class="col-sm-4 control-label">Phone Number</label>
					    <div class="col-sm-8">
					      <input type="text" class="form-control" id="newPhoneNumber" placeholder="Phone Number">
					    </div>
					  </div>
					  
					  <div class="form-group">
					    <label for="newContactEmail" class="col-sm-4 control-label">Email Id</label>
					    <div class="col-sm-8">
					      <input type="email" class="form-control" id="newContactEmail" placeholder="Email">
					    </div>
					  </div>

					  <div class="form-group">
					    <label for="newContactEmail" class="col-sm-4 control-label"></label>
					    <div class="col-sm-8">
					    	<div id="errorDiv" class="bg-danger"></div>
					    </div>
					  </div>
					</form>

			      </div>
			      <div class="modal-footer">
			        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			        <button type="button" class="btn btn-primary" id="addNewContact">Add Contact</button>
			      </div>
			    </div>
			  </div>
			</div>
  </body>
</html>
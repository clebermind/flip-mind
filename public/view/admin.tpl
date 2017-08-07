<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

<div id="success-new-flipbook" class="col-md-11 alert alert-success" role="alert" style="display: none">Success. FlipBook has been created!</div>
<div id="error-new-flipbook" class="col-md-11 alert alert-danger" role="alert" style="display: none">Ops... Something was wrong. Unfortunitily the Flipbook was not created! Try again.</div>

<div class="col-md-11">
  <h2>Mind FlipBook</h2>
  <ul class="nav nav-tabs">
    <li class="active"><a data-toggle="tab" href="#list"><i class="glyphicon glyphicon-book"></i> My FlipBooks</a></li>
    <li><a data-toggle="tab" href="#upload"><i class="glyphicon glyphicon-plus"></i> New FlipBook</a></li>
  </ul>

  <div class=tab-content">
    
	<div id="list" class="tab-pane fade in active">
      
			<table class="table table-hover">
					<thead>
							<tr>
									<!--th>#</th-->
									<th>key</th>
									<th>File</th>
									<th>Description</th>
									<th>Uploaded at</th>
									<th>&nbsp;</th>
							</tr>
					</thead>
					<tbody>
						{inserted-flibooks}
					</tbody>
			</table>
    </div>
    
		<div id="upload" class="tab-pane fade">
			<!--h2>Create a new Mind FlipBook</h2-->
			<p class="form-notice"></p>
			<form action="" method="post" class="image-form">
				{wp-nonce-field}
				
				<div class="form-group mind-fill-step" id="1-new-mind-step">
						<h4>1st Step: Choose a name for the FlipBook</h4>
						<div class="input-group">
							<input type="text" class="form-control" id="pdf-description" name="pdf-description" placeholder="Mind FlipBook's Name" >
							<span class="input-group-btn">
								<button class="btn btn-default" type="button" id='btn-mind-step-one'>Go!</button>
							</span>
						</div>
				</div>
				
				<div class="form-group mind-fill-step" id="2-new-mind-step">
						<h4>2nd Step: Choose the FlipBook's setting</h4>
						I promess that here will ahve some settings to fill.
						cor de fundo, tipo e
						<div class="mind-overlay-step"></div>
				</div>
			
				<p class="image-notice"></p>
			
				<div class="form-group mind-fill-step" id="3-new-mind-step"> 
					<h4>3rd and final step: Select your PDF which will be your FlipBook</h4>
					<input type="file" name="async-upload" class="image-file" accept="application/pdf" required>
					
					<div class="mind-overlay-step"></div>
				</div>
				
				<p class="submit">
					<input class="button button-primary button-large" id="mind-submit-new" type="submit" value="Create">
				</p>
		
			</form>
			
    </div>
  
  </div>
</div>

<script>
	function changeFileDescription(key) {
		jQuery('#field-description-'+key).show();
		jQuery('#span-description-'+key).hide();
		jQuery('#mind-edit-file-'+key).hide();
		jQuery('#mind-save-file-'+key).show();
		jQuery('#mind-cancel-file-'+key).show();
	}

	function cancelFileDescription(key) {
		jQuery("#field-description-"+key).val(jQuery("#span-description-"+key).html());
		jQuery('#field-description-'+key).hide();
		jQuery('#span-description-'+key).show();
		jQuery('#mind-edit-file-'+key).show();
		jQuery('#mind-save-file-'+key).hide();
		jQuery('#mind-cancel-file-'+key).hide();
	}	

	function saveFileDescription(key) {
		
		if(!jQuery("#field-description-"+key).val()) {
			alert('You must to put a description');
			return false;
		} else if (jQuery("#field-description-"+key).val()==jQuery("#span-description-"+key).html()) {
			cancelFileDescription(key);
			return false;
		}
		
		
		var text = jQuery("#span-description-"+key).html();
		jQuery("#span-description-"+key).html('<img src="{url-plugin}/mind-flipbook/public/images/loading.gif" /> Working...');
		jQuery('#span-description-'+key).show();
		jQuery('#field-description-'+key).hide();
		jQuery('#mind-edit-file-'+key).hide();
		jQuery('#mind-save-file-'+key).hide();
		jQuery('#mind-cancel-file-'+key).hide();
		
		jQuery.ajax({
			 type:'POST',
			 data:{action:'mind_edit', value: jQuery("#field-description-"+key).val(), key: key},
			 url: "{url-site}/wp-admin/admin-ajax.php",
			 success: function(value) {
				 if(value=='ok') {
					alert('Success. It has been Updated!');
					jQuery("#span-description-"+key).html('<img src="{url-plugin}/mind-flipbook/public/images/loading.gif" /> Realoding page...');
					location.reload();
				 } else {
					alert(value + '. Try again.');
					jQuery("#span-description-"+key).html(text);
				  changeFileDescription(key);
				 }
			 },
			 error: function(e) {
				 //console.log(e);
				 jQuery("#span-description-"+key).html(text);
				 changeFileDescription(key);
			 }
		 });
    
	}
	
	function deleteMindFlipbook(key) {
		cancelFileDescription(key);
		if (confirm('Are you sure you want to delete this FlipBook?')) {
			
			var text = jQuery("#span-description-"+key).html();
			jQuery('#mind-edit-file-'+key).hide();
			jQuery("#span-description-"+key).html('<img src="{url-plugin}/mind-flipbook/public/images/loading.gif" /> Deleting...');
			
			jQuery.ajax({
			 type:'POST',
			 data:{action:'mind_delete', key: key},
			 url: "{url-site}/wp-admin/admin-ajax.php",
			 success: function(value) {
				 if(value=='ok') {
					alert('Success. It has been deleted!');
					jQuery("#span-description-"+key).html('<img src="{url-plugin}/mind-flipbook/public/images/loading.gif" /> Realoding page...');
					location.reload();
				 } else {
					alert(value + '. Try again.');
					jQuery("#span-description-"+key).html(text);
					jQuery('#mind-edit-file-'+key).show();
				 }
			 },
			 error: function(e) {
				 //console.log(e);
				 jQuery("#span-description-"+key).html(text);
				 jQuery('#mind-edit-file-'+key).show();
			 }
		 });
			
		}
	}
	
	jQuery(document).ready(function() {
	
		if('{message}'=='success') {
			jQuery('#success-new-flipbook').show();
		} else if('{message}'=='error') {
			jQuery('#error-new-flipbook').show();
		}
		
		jQuery('#btn-mind-step-one').on('click', function(){
				alert('sss');
		});
		
	});
	

</script>
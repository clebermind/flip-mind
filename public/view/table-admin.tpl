<tr>
		<!--td>{num}</td-->
		<td>{key}</td>
		<td>{file}</td>
		<td>
			<a href="javascript:void(0);" id="mind-edit-file-{key}" onclick="changeFileDescription('{key}')"><i title="Edit" class="glyphicon glyphicon-pencil"></i></a>
			<a href="javascript:void(0);" id="mind-save-file-{key}" onclick="saveFileDescription('{key}')" style="display: none"><i title="Save" class="glyphicon glyphicon glyphicon-ok"></i></a>
			<a href="javascript:void(0);" id="mind-cancel-file-{key}" onclick="cancelFileDescription('{key}')" style="display: none"><i title="Cancel" class="glyphicon glyphicon-remove"></i></a>
			
			<span id="span-description-{key}">{description}</span>
			<input type="text" name="field-description-{key}" id="field-description-{key}" value="{description}" style="display:none" />
		</td>
		<td>{date}</td>
		<td>
			<a href="javascript:void(0);" onclick="deleteMindFlipbook('{key}')"><i title="Delete" class="glyphicon glyphicon-trash"></i></a>
			&nbsp;&nbsp;
			<a href="{url-view}" target="_blank"><i title="Take a Look" class="glyphicon glyphicon-eye-open"></i></a>
		</td>
</tr>


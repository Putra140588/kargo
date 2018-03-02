<?php 
$w=1;
foreach ($x as $row){?>
	<table>		
		<tbody>
			<tr>
				<td colspan=2><img class="logo" src="<?php echo base_url()?>assets/images/airline_logo/<?php echo $row->logo?>"></td>				
			</tr>
			<tr>
				<td colspan=2><img src="<?php echo base_url(MODULE.'/tesprint/bikin_barcode1/'.$row->barcode_no);?>"></td>				
			</tr>			
		</tbody>
	</table>	
<?php 	
	echo '<footer></footer>';
	$w++;
}?>	
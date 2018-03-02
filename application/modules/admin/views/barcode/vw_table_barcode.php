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
			<tr>
				<td colspan=2>
					Air Waybill No.
					<br>
					<span class="text1"><?php echo substr_awb($row->awb_no)['subnumber1']?></span><span class="text2"> <?php echo substr_awb($row->awb_no)['subnumber2']?></span>
				</td>				
			</tr>
			<tr>
				<td class="right-border">
					Destinaton
					
					<span class="text4"><?php echo $row->awb_dest?></span>
				</td>	
				<td>
					Total Pcs					
					<span class="text4"><?php echo $row->awb_pcs?></span>
				</td>				
			</tr>
			<tr>
				<td colspan=2>
					Origin Station					
					<span class="text3"><?php echo $row->origin_station?></span>
				</td>									
			</tr>
			<tr>
				<td colspan=2><img class="barcode2" src="<?php echo base_url(MODULE.'/tesprint/bikin_barcode2/'.$row->hawb_no);?>"></td>				
			</tr>
			<tr>
				<td class="right-border hide-bottom">
					Destinaton					
					<span class="text4"><?php echo $row->hawb_dest?></span>
				</td>	
				<td class="hide-bottom">
					Total Pcs
					<br>
					<span class="text4"><?php echo $row->hawb_pcs?></span>
				</td>				
			</tr>
		</tbody>
	</table>	
<?php 	
	echo '<footer></footer>';
	$w++;
}?>	
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>Print Airway Bill Barcode</title>   
    <style type="text/css"> 
   		@page
		   {
		    margin-left: 0px;
			margin-right: 0px;
			margin-top: 0px;
			margin-bottom: 0px;
			
		  }
    	
    	hr{
    		height:4px;
			border:none;
			color:#333;
			background-color:#333;
			*margin-right:100px;		
    	} 	
  		table{  			
  			margin-left:3px;
  			margin-right:30px;
  			margin-top:20px;
  			width:300px;
  			border : 3px solid black;
  		}
  		td{
  			padding:4px;   			 	
  			border-bottom : 3px solid black;			
  			    			
  		}
  		.hide-bottom{
  			border-bottom : 0;
  		}
  		.logo{
  			margin-left:16px;
  			width:300px;
  			height:60px;
  		}
  		.text1{
  			font-size: 30px; font-weight:bold;
  		}
  		.text2{
  			font-size: 50px; font-weight:bold;
  			margin-left:50px;
  		}
  		.text3{
  			font-size: 30px; font-weight:bold;
  			margin-left:45px;
  		}
  		.text4{
  			font-size: 25px; font-weight:bold;
  			margin-left:80px;
  		}
  		.right-border{
  			border-right : 3px solid black;
  		}
		footer {
				page-break-after: always;
				
				}
		.barcode2{
			margin-left:38px;
		}
    </style>
</head>
<body>
	<div id="barcodecontainer">
		<?php if ($label_dest == 'FRC'){
			//france dest
			$this->load->view('barcode/vw_table_barcode_france');
		}else{
			$this->load->view('barcode/vw_table_barcode');
		}?>
	</div>
</body>
</html>
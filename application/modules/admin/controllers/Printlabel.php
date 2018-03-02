<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Printlabel extends CI_Controller{
	var $class;
	var $table;
	var $datenow;
	var $addby;
	var $access_code;
	public function __construct(){
		parent::__construct();
		$this->load->model('m_admin');
		$this->class = strtolower(__CLASS__);
		$this->table = 'tb_order';
		$this->datenow = date('Y-m-d H:i:s');
		$this->addby = $this->session->userdata('first_name');
		$this->access_code = 'SUMPRN';
		$this->m_admin->maintenance();
	}	
	function index(){
		$this->m_admin->sess_login();
		$priv = $this->m_admin->get_priv($this->access_code,'view');
		$body= (empty($priv)) ? $this->class.'/vw_printlabel' : $priv['error'];
		$data['notif']= (empty($priv)) ? '' : $priv['notif'];
		links(MODULE.'/'.$this->class);
		url_sess(base_url(MODULE.'/'.$this->class));//link for menu active
		$data['page_title'] = 'Data '.__CLASS__;
		$data['body'] = $body;
		$data['class'] = $this->class;		
		$this->load->view('vw_header',$data);
	}
	function column(){
		$field_array = array(
			0 => 'A.date_add',			
			1 => 'A.id_order',
			2 => 'B.complete_name',
			3 => 'A.total_koli',
			4 => 'A.total_qty',
			5 => 'A.total_price',	
			6 => 'A.total_amount_tax',
			7 => 'A.total_price_tax',
			8 => 'C.shift_name',	
			9 => 'A.active',	
			10 => 'A.add_by',
			11 => 'A.date_add',		
				
		);
		return $field_array;
	}
	
	function get_records(){
		$output = array();		
		//load datatable
		$this->m_admin->datatable();
		$total = count($this->m_admin->get_order());
		$output['draw'] = $_REQUEST['draw'];
		$output['csrf_token'] = csrf_token()['hash'];//reload hash token diferent
		$output['recordsTotal']= $output['recordsFiltered'] = $total;	
		//date filter value already set index row column
		$date_from = $_REQUEST['columns'][0]['search']['value'];
		$date_to = $_REQUEST['columns'][11]['search']['value'];
		$this->m_admin->range_date($this->column(),$date_from,$date_to);
		$query = $this->m_admin->get_order('',$this->column());
		$this->m_admin->range_date($this->column(),$date_from,$date_to);
		$total = count($this->m_admin->get_order());
		$output['recordsFiltered'] = $total;		
		$output['data'] = array();
		$no = $_REQUEST['start'] + 1;
		foreach ($query as $row){
			$actions = '<a class="btn btn-xs btn-info" href="'.base_url(MODULE.'/'.$this->class.'/view/'.$row->id_order).'" title="View" data-rel="tooltip" data-placement="top">'.icon_action('view').'</a>
						<a class="btn btn-xs btn-success" href="javascript:void(0)" onClick="openWin(\''.base_url(MODULE.'/tesprint/invoice/'.$row->id_order).'\')" title="Generate Invoice" target="_blank" data-rel="tooltip" data-placement="top"><i class="fa fa-file"></i></a>						
					    <a class="btn btn-xs btn-danger" href="'.base_url(MODULE.'/'.$this->class.'/void/'.$row->id_order).'" onclick="return confirm(\'Are you sure void this order ?\')" title="Void" data-rel="tooltip" data-placement="top">'.icon_action('cancel').'</a>';			
			$status = ($row->active == 1) ? '<span class="label label-success arrowed-in-right arrowed">Active</span>' : '<span class="label label-important arrowed-in-right arrowed">Void</span>';
			$output['data'][] = array(
					$no,					
					$row->id_order,
					$row->complete_name,
					$row->total_koli,
					$row->total_qty,
					ISOCODE.' '.number_format($row->total_price,0),	
					ISOCODE.' '.number_format($row->total_amount_tax,0),
					ISOCODE.' '.number_format($row->total_price_tax,0),
					$row->shift_name,
					$status,
					$row->add_by,
					short_date_time($row->date_add),					
					$actions
			);
			$no++;
		}
		echo json_encode($output);
	}
	function view($id=''){
		$this->m_admin->sess_login();		
		links(MODULE.'/'.$this->class.'/view/'.$id);
		$data['page_title'] = 'View Summary Print Label';		
		$action = 'view';
		$priv = $this->m_admin->get_priv($this->access_code,$action);
		$body= (empty($priv)) ? $this->class.'/vw_detail' : $priv['error'];
		$data['detail'] = $this->m_admin->get_order_detail(array('A.id_order'=>$id));
		$data['body'] = $body;
		$data['notif']= (empty($priv)) ? '' : $priv['notif'];
		$this->load->view('vw_header',$data);
	}
	function select_print(){
		$id = $this->input->post('value',true);
		$data['class'] = $this->class;
		$data['id_form'] = 'formmodal';
		$data['page_title']='Print Select Barcode';
		$data['sql'] = $this->m_admin->get_label_print(array('C.id_order_detail'=>$id));
		$modal = $this->load->view($this->class.'/vw_modal_barcode',$data,true);
		echo json_encode(array('csrf_token'=>csrf_token()['hash'],'modal'=>$modal));
	}
	function print_select(){
		$id = $this->input->post('idbarcode');	
		if (!empty($id)){	
			$imp = implode("-", $id);
			$url= base_url(MODULE.'/tesprint/index/select/'.$imp);
			echo json_encode(array('csrf_token'=>csrf_token()['hash'],'error'=>0,'type'=>'print_barcode','url'=>$url));
		}else{
			echo json_encode(array('csrf_token'=>csrf_token()['hash'],'error'=>1,'type'=>'error','msg'=>'Barcode number not check!'));
		}
	}
	function edit(){
		$id = $this->input->post('value',true);
		$data['class'] = $this->class;
		$data['id_form'] = 'formmodal';
		$data['page_title']='Edit';		
		$data['access_code'] = $this->access_code;
		$data['airline'] = $this->m_admin->get_table('tb_airline',array('id_airline','airline_name','call_sign'),array('deleted'=>0));
		$data['label_dest'] = $this->m_admin->get_table('tb_label_dest');
		$sql = $this->m_admin->get_order_detail(array('A.id_order_detail'=>$id));
		foreach ($sql as $row)
			foreach ($row as $key=>$val){
			$data[$key] = $val;
		}
		$modal = $this->load->view($this->class.'/vw_modal_edit',$data,true);
		echo json_encode(array('csrf_token'=>csrf_token()['hash'],'modal'=>$modal));
	}
	function proses(){
		$this->db->trans_start();
		$id_order = $this->input->post('id_order',true);
		
		$airline = explode("#", $this->input->post('airline',true));
		$qty = $this->input->post('qty',true);
		
		$cost = $this->m_admin->get_cost_label($qty);
		$id_cost_label = $cost[0]->id_cost_label;
		$price = $cost[0]->price;
		$tax = $cost[0]->tax;
		$price_tax = $cost[0]->price_tax;
		$tax_amount = $cost[0]->tax_amount;
		$name_cost = $cost[0]->name_cost;
		$no_awb = $this->input->post('noawb',true);
		if (strlen($no_awb) < 11 || strlen($no_awb) > 11){
			$alert = 'Airway Bill number harus 11 digit';
			echo json_encode(array('csrf_token'=>csrf_token()['hash'],'error'=>1,'msg'=>$alert,'type'=>'error'));
			return false;
		}else{
			$id_order_detail = $this->input->post('id_order_detail',true);	
			$wheredet = array('id_order_detail'=>$id_order_detail);		
			$det['id_airline'] = $airline[0];
			$det['id_cost_label'] = $id_cost_label;
			$det['awb_no'] = $no_awb;
			$det['awb_dest'] = $this->input->post('awbdest',true);
			$det['awb_pcs'] = $this->input->post('awbpcs',true);
			$det['hawb_no'] = $this->input->post('nohawb',true);
			$det['hawb_dest'] = $this->input->post('hawbdest',true);
			$det['hawb_pcs'] = $this->input->post('hawbpcs',true);
			$det['qty_print'] = $qty;
			$det['price'] = $price;
			$det['subtotal_price'] = $price * $qty;
			$det['tax'] = $tax;
			$det['price_tax'] = $price_tax;
			$det['subtotal_price_tax'] = $price_tax * $qty;
			$det['name_cost'] = $name_cost;
			$det['amount_tax'] = $tax_amount * $qty;
			$det['origin_station'] = $this->session->userdata('station_code');
			$res = $this->db->update('tb_order_detail',$det,$wheredet);
			$res = $this->db->delete('tb_label_barcode',$wheredet);//delete barcode
			//insert barcode new
			for ($i=1; $i <= $qty; $i++){
				$uniqe = str_pad($i, 5,"0",STR_PAD_LEFT);
				$barcode = $no_awb.$uniqe;
				$bar['id_order_detail'] = $id_order_detail;
				$bar['barcode_no'] = $barcode;				
				$input[] = $bar;
			}
			$res = $this->db->insert_batch('tb_label_barcode',$input);
			
			//update header
			$sql = $this->m_admin->get_sum_order(array('id_order'=>$id_order));
			$post['total_koli'] = $sql[0]->total_koli;
			$post['total_qty'] = $sql[0]->total_qty;
			$post['total_price'] = $sql[0]->subtotal_price;
			$post['total_price_tax'] = $sql[0]->subtotal_price_tax;
			$post['total_amount_tax'] = $sql[0]->total_amount_tax;
			$post['date_update'] = $this->datenow;
			$post['update_by'] = $this->addby;
			$res = $this->db->update('tb_order',$post,array('id_order'=>$id_order));
			
			if ($this->db->trans_status() === FALSE){
				$this->db->trans_rollback();
			}else{
				$this->db->trans_complete();
				if ($res){
					$alert='Edit summary print label successfull';
					$data['detail'] = $this->m_admin->get_order_detail(array('A.id_order'=>$id_order));
					$content=$this->load->view('printlabel/vw_table',$data,true);
					$this->session->set_flashdata('success',$alert);
					echo json_encode(array('csrf_token'=>csrf_token()['hash'],'error'=>0,'msg'=>$alert,'type'=>'modal','content'=>$content));
				}
			}
			
		}
		
	}
	function void($id_order=''){
		$this->m_admin->sess_login();
		links(MODULE.'/'.$this->class);
		$priv = $this->m_admin->get_priv($this->access_code,'delete');		
		if (empty($priv)){
			$where = array('id_order'=>$id_order);
			$post['active']=0;
			$res = $this->db->update('tb_order',$post,$where);
			$res = $this->db->update('tb_order_detail',$post,$where);
			if ($res){
				$this->session->set_flashdata('success','Void summary print label successfull');
				redirect($this->session->userdata('links'));
			}
		}else{
			$data['notif']= $priv['notif'];			
			$data['page_title'] = 'Data '.__CLASS__;
			$data['body'] = $priv['error'];
			$data['class'] = $this->class;
			$this->load->view('vw_header',$data);
		}
		
	}
}

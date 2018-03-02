<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Transaction extends CI_Controller{
	var $class;
	var $table;
	var $datenow;
	var $addby;
	var $access_code;
	var $id_shift;
	var $id_employee;
	public function __construct(){
		parent::__construct();
		$this->load->model('m_admin');
		$this->class = strtolower(__CLASS__);
		$this->table = 'tb_order';
		$this->datenow = date('Y-m-d H:i:s');
		$this->addby = $this->session->userdata('first_name');
		$this->id_shift = $this->session->userdata('id_shift');
		$this->id_employee =  $this->session->userdata('id_employee');
		$this->access_code = 'CTKLBL';
		$this->m_admin->maintenance();		
		
		
	}	
	function index(){
		$this->m_admin->sess_login();
		$this->session->unset_userdata('partialorder');
		$priv = $this->m_admin->get_priv($this->access_code,'add');
		$body= (empty($priv)) ? $this->class.'/vw_transaction' : $priv['error'];
		$data['notif']= (empty($priv)) ? '' : $priv['notif'];
		links(MODULE.'/'.$this->class);
		url_sess(base_url(MODULE.'/'.$this->class));//link for menu active
		$data['customer'] = $this->m_admin->get_table('tb_customer',array('id_customer','complete_name'),array('deleted'=>0,'active'=>1));
		$data['airline'] = $this->m_admin->get_table('tb_airline',array('id_airline','airline_name','call_sign'),array('deleted'=>0));
		$data['label_dest'] = $this->m_admin->get_table('tb_label_dest');
		$data['btn_refresh'] = true;
		$data['id_order'] = $this->m_admin->id_order('INV-');
		$data['page_title'] = 'Data '.__CLASS__;
		$data['id_form'] = 'form-ajax';
		$data['body'] = $body;
		$data['class'] = $this->class;		
		$this->load->view('vw_header',$data);
	}		
	function proses(){
		$this->db->trans_start();					
		$id_order = $this->input->post('id_order',true);		
		//detail
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
			$id_order_detail = $this->m_admin->get_rand_id('P'.date('d'));
			$det['id_order_detail'] = $id_order_detail;
			$det['id_order'] = $id_order;
			$det['id_airline'] = $airline[0];
			$det['id_cost_label'] = $id_cost_label;
			$det['awb_no'] = $no_awb;
			$det['awb_dest'] = $this->input->post('awbdest',true);
			$det['awb_pcs'] = $this->input->post('awbpcs',true);
			$det['hawb_no'] = $this->input->post('nohawb',true);
			$det['hawb_dest'] = $this->input->post('hawbdest',true);
			$det['hawb_pcs'] = $this->input->post('hawbpcs',true);
			$det['id_label_dest'] = $this->input->post('labeldest',true);;
			$det['qty_print'] = $qty;
			$det['price'] = $price;
			$det['subtotal_price'] = $price * $qty;
			$det['tax'] = $tax;		
			$det['price_tax'] = $price_tax;
			$det['subtotal_price_tax'] = $price_tax * $qty;
			$det['name_cost'] = $name_cost;
			$det['amount_tax'] = $tax_amount * $qty;
			$det['origin_station'] = $this->session->userdata('station_code');
			$res = $this->db->insert('tb_order_detail',$det);		
				//insert barcode
				for ($i=1; $i <= $qty; $i++){				
					$uniqe = str_pad($i, 5,"0",STR_PAD_LEFT);
					$barcode = $no_awb.$uniqe;
					$bar['id_order_detail'] = $id_order_detail;
					$bar['barcode_no'] = $barcode;					
					$input[] = $bar;
				}
				$res = $this->db->insert_batch('tb_label_barcode',$input);
				
				//header
				$sql = $this->m_admin->get_sum_order(array('id_order'=>$id_order));													
				$post['total_koli'] = $sql[0]->total_koli;
				$post['total_qty'] = $sql[0]->total_qty;
				$post['total_price'] = $sql[0]->subtotal_price;
				$post['total_price_tax'] = $sql[0]->subtotal_price_tax;
				$post['total_amount_tax'] = $sql[0]->total_amount_tax;								
				$post['date_update'] = $this->datenow;
				$post['update_by'] = $this->addby;				
				if (!$this->session->userdata('partialorder')){
					//new order
					$post['id_shift']= $this->id_shift;
					$post['id_order'] = $id_order;
					$post['id_customer']=$this->input->post('customer',true);
					$post['id_employee'] = $this->id_employee;
					$post['tax'] = $tax;
					$post['add_by'] = $this->addby;
					$post['date_add'] = $this->datenow;
					$res = $this->db->insert('tb_order',$post);	
				}else{
					//partial order
					$res = $this->db->update('tb_order',$post,array('id_order'=>$id_order));
				}		
			
			if ($this->db->trans_status() === FALSE){
				$this->db->trans_rollback();
			}else{
				$this->db->trans_complete();
				if ($res > 0){				
					$this->session->set_userdata('partialorder',true);
					$alert = 'Input Airway Bill successfull';
					$this->session->set_flashdata('success',$alert);
					echo json_encode(array('csrf_token'=>csrf_token()['hash'],'error'=>0,'msg'=>$alert,'type'=>'save_print','redirect'=>base_url($this->session->userdata('links')),'print'=>base_url(MODULE.'/tesprint/index/'.$id_order_detail)));
				}else{
					echo json_encode(array('csrf_token'=>csrf_token()['hash'],'error'=>1,'msg'=>$alert,'type'=>'error'));
				}
			}
			
		}	
	}
	
}

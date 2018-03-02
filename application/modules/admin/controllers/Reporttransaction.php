<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Reporttransaction extends CI_Controller{
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
		$this->load->model('m_content');
		$this->class = strtolower(__CLASS__);
		$this->table = 'tb_order';
		$this->datenow = date('Y-m-d H:i:s');
		$this->addby = $this->session->userdata('first_name');
		$this->id_shift = $this->session->userdata('id_shift');
		$this->id_employee =  $this->session->userdata('id_employee');
		$this->access_code = 'RPTTRX';
		$this->m_admin->maintenance();						
	}	
	
	function index(){
		$res="";		
		$val= $this->input->post('value');
		$tab = (empty($val)) ? '' : $val;
		$data['class'] = $this->class;
		$data['shift'] = $this->m_admin->get_table('tb_shift','*',array('deleted'=>0,'active'=>1));
		$data['idtab'] = $tab;
		switch ($tab){
			case 'tab1':
				$data['title'] = 'Rekapitulasi Pendapatan Label Barcode';				
				break;
			case 'tab2':
				$data['date_to'] = true;
				$data['title'] = 'Laporan Penerimaan Harian';
				
				break;
			case 'tab3':
				$data['general'] = true;
				$data['title'] = 'Laporan Penerimaan General';
				break;			
			default:
				//menampilkan halaman default direct url non ajax
				$this->m_admin->sess_login();
				$priv = $this->m_admin->get_priv($this->access_code,'add');
				$body= (empty($priv)) ? 'report/vw_report_transaction' : $priv['error'];
				$data['notif']= (empty($priv)) ? '' : $priv['notif'];
				links(MODULE.'/'.$this->class);
				url_sess(base_url(MODULE.'/'.$this->class));//link for menu active					
				$data['page_title'] = 'Data '.__CLASS__;
				$data['body'] = $body;
				$data['class'] = $this->class;	
				$data['idtab'] = 'tab1';
				$data['title'] = 'Rekapitulasi Pendapatan Label Barcode';
				$this->load->view('vw_header',$data);
				break;
		}
		//jika ajax select tab dijalankan
		if (!empty($tab)){
			$res .= $this->m_content->load_pluglin_jquery();
			$res .= $this->load->view('report/vw_tab_rekapitulasi',$data,true);
			echo json_encode(array('csrf_token'=>csrf_token()['hash'],'error'=>0,'element'=>$res));
		}
	}
	
}

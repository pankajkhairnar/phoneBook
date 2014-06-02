<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Phonebook extends CI_Controller {

	public function index() {
		$this->load->model('phonebook_model');

		$data = array();
		$data['records'] = $this->phonebook_model->getContacts();				
		$this->load->view('listing', $data);
	}

	public function create() {
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('', '');
		$this->form_validation->set_rules('contact_name', 'Contact Name', 'required|callback_valid_name');
		$this->form_validation->set_rules('contact_number', 'Contact Phone', 'required|callback_valid_phone');
		$this->form_validation->set_rules('contact_email', 'Contact Email', 'required|valid_email');

		$fields = array('contact_id', 'contact_name', 'contact_number', 'contact_email');

		if ($this->form_validation->run() == FALSE) {
			$response = array('result'=>'error');
			foreach ($fields as $field) {
				if(form_error($field) != '') {
					$response['message'] = form_error($field);
					break;
				}
			}
			echo json_encode($response);
			return false;//early return
		}

		
		$this->load->model('phonebook_model');
		$data = array();
		
		$data['full_name'] = $this->input->post('contact_name', true);
		$data['phone_number'] = $this->input->post('contact_number', true);
		$data['email'] = $this->input->post('contact_email', true);

		$result = $this->phonebook_model->create($data);

		if($result == true) {
			$response = array('result' => 'success', 'message' => 'Contact created successfully - Refreshing Page'); 
		} else {
			$response = array('result' => 'error', 'message' => 'Some error occured, please try after some time'); 
		}
		echo json_encode($response);
		return true;
	}

	public function update() {

		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('', '');
		$this->form_validation->set_rules('contact_id', 'Password Confirmation', 'required|numeric');
		$this->form_validation->set_rules('contact_name', 'Name', 'required|callback_valid_name');
		$this->form_validation->set_rules('contact_number', 'Phone', 'required|callback_valid_phone');
		$this->form_validation->set_rules('contact_email', 'Email', 'required|valid_email');

		$fields = array('contact_id', 'contact_name', 'contact_number', 'contact_email');

		if ($this->form_validation->run() == FALSE) {
			$response = array('result'=>'error');
			foreach ($fields as $field) {
				if(form_error($field) != '') {
					$response['message'] = form_error($field);
					break;
				}
			}
			echo json_encode($response);
			return false;//early return
		}

		$this->load->model('phonebook_model');
		$data = array();
		
		$contactId = $this->input->post('contact_id', true);//second argument for xss cleaning
		$data['full_name'] = $this->input->post('contact_name', true);
		$data['phone_number'] = $this->input->post('contact_number', true);
		$data['email'] = $this->input->post('contact_email', true);

		$result = $this->phonebook_model->update($data, $contactId);

		if($result == true) {
			$response = array('result' => 'success', 'message' => 'Contact info updated successfully'); 
		} else {
			$response = array('result' => 'error', 'message' => 'Some error occured, please try after some time'); 
		}
		$response['contact_id'] = $contactId;
		echo json_encode($response);
	}

	public function delete() {
		$contactId = $this->input->post('contact_id', true);//second argument for xss cleaning
		$this->load->model('phonebook_model');

		$contactId = (int)$contactId;//type casing if user has sent non int data it will be 0 after casting
					                  // it will also check value is sent or not

		if($contactId != 0) {
			$result = $this->phonebook_model->delete($contactId);
			if($result == true) {
				$response = array('result' => 'success', 'message' => 'Contact deleted successfully'); 
			} else {
				$response = array('result' => 'error', 'message' => 'Some error occured, please try after some time'); 
			}
		} else {
			$response = array('result' => 'error', 'message' => 'Invalid contact Id'); 
		}

		echo json_encode($response);
	}

	public function valid_phone($phoneNumber) {
		if(preg_match('/^[\(\)+\-0-9]{7,15}$/', $phoneNumber)) {
			return true;
		} else {
			$this->form_validation->set_message('valid_phone', 'Invalid Phone number');
			return false;
		}
	}

	public function valid_name($name) {

		if(preg_match('/^[0-9a-zA-Z-_ ]{1,50}$/', $name)) {
			return true;
		} else {
			$this->form_validation->set_message('valid_name', 'Invalid Contact Name');
			return false;
		}
	}

	public function import() {

		$config['upload_path']   = $this->config->item('base_path').'contact_csvs/';
		$config['allowed_types'] = 'text/plain|text/csv|csv';
		$config['file_name']     = 'upload_phonebook_csv_'.md5(time()).'.csv';

		$this->load->library('upload', $config);

		if ( ! $this->upload->do_upload('contacts')) {
			$data = array('error' => $this->upload->display_errors());
			$this->load->view('upload_error', $data);
			return false;
		} else {
			$delimeter = $this->input->post('delimeter', true);

			if($delimeter == 'tab') {
				$delimeterVal = '	';
			} else {
				$delimeterVal = ',';
			}

			$data = $this->upload->data();
 			$uploadedFilePath = $data['full_path'];
			$response = $this->getCsvContent($uploadedFilePath, $delimeterVal);

			if($response['result'] == 'error') {
				$data = array('error' => $response['message']);
				$this->load->view('upload_error', $data);
				return false;
			}
		}

		$this->load->model('phonebook_model');
		$response = $this->phonebook_model->sync_contacts($response['contacts']);
		
	}


	public function export() {
		$this->load->model('phonebook_model');
		$this->load->helper('download');
		
		if( ! ini_get('date.timezone') ) {
		    date_default_timezone_set('GMT');
		}

		$fileName = 'phonebook_dump_'.date('Y_m_d', time()).'.csv';
		$phonebook = $this->phonebook_model->export();
		force_download($fileName, $phonebook);
	}

	public function getCsvContent($filepath, $separator = ',', $enclosure = '"') {
        $maxRowSize  = 4096;
        $content     = array();
        $tableFields = array('full_name', 'phone_number', 'email');

        $file      = fopen($filepath, 'r');
        $csvFields = fgetcsv($file, $maxRowSize, $separator, $enclosure);
        $fieldDiff = array_diff($csvFields, $tableFields);
        
        if(count($fieldDiff) > 0) {
        	$response = array('result'=>'error', 'message' => 'Invalid fields in CSV, should only have: full_name, phone_number, email');
        	return $response;
        }
        $i = 0;
        while( ($row = fgetcsv($file, $maxRowSize, $separator, $enclosure)) != false ) {            
            if( count($row) == count($tableFields)) { // skip invalid lines
                foreach ($csvFields as $index => $value) {
                	$content[$i][$value] = $row[$index]; 
                }
                $i++;
            }
        }
        fclose($file);
        $response = array('result'=>'success', 'contacts' => &$content);
        return $response;
	}

}


// for($i = 1; $i<=100; $i++ ){
// 			$data = array();
// 			$data['phone_number'] = '+1-'.rand(6666,9999).rand(100,999).rand(100,999); 
// 			$data['full_name']    = 'Contact '.$i;
// 			$data['email']        = 'contact_'.$i.'@yopmail.com';
// 			$this->db->insert('phonebook', $data);
// }
?>
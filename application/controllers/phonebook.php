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
		
		$data['full_name']    = $this->input->post('contact_name', true);
		$data['phone_number'] = $this->input->post('contact_number', true);
		$data['email']        = $this->input->post('contact_email', true);

		$result = $this->phonebook_model->create($data);

		if($result == true) {
			$response = array('result' => 'success', 'message' => 'Contact created successfully - Refreshing Page'); 
		} else {
			$response = array('result' => 'error', 'message' => 'Some error occurred, please try after some time'); 
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
		
		$contactId            = $this->input->post('contact_id', true);//second argument for xss cleaning
		$data['email']        = $this->input->post('contact_email', true);
		$data['full_name']    = $this->input->post('contact_name', true);
		$data['phone_number'] = $this->input->post('contact_number', true);

		$result = $this->phonebook_model->update($data, $contactId);

		if($result == true) {
			$response = array('result' => 'success', 'message' => 'Contact info updated successfully'); 
		} else {
			$response = array('result' => 'error', 'message' => 'Some error occurred, please try after some time'); 
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
				$response = array('result' => 'error', 'message' => 'Some error occurred, please try after some time'); 
			}
		} else {
			$response = array('result' => 'error', 'message' => 'Invalid contact Id'); 
		}

		echo json_encode($response);
	}

	//callback function for checking valid phone : used in form validation
	public function valid_phone($phoneNumber) {
		if(preg_match('/^[\(\)+\-0-9]{7,15}$/', $phoneNumber)) {
			return true;
		} else {
			$this->form_validation->set_message('valid_phone', 'Invalid Phone number');
			return false;
		}
	}

	//callback function for checking valid name : used in form validation
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
			$data = array('result'=>'error', 'message' => $this->upload->display_errors());
			$this->load->view('upload_result', $data);
			return false;
		} else {
			$delimeter = $this->input->post('delimiter', true);

			if($delimeter == 'tab') {
				$delimeterVal = '	';
			} else {
				$delimeterVal = ',';
			}

			$data             = $this->upload->data();
 			$uploadedFilePath = $data['full_path'];
			$response = $this->getCsvContent($uploadedFilePath, $delimeterVal);

			if($response['result'] == 'error') {
				$data = array('result'=>'error', 'message' => $response['message']);
				$this->load->view('upload_result', $data);
				return false;
			}
		}

		$this->load->model('phonebook_model');
		$response = $this->phonebook_model->sync_contacts($response['contacts']);
		$this->load->view('upload_result', $response);
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
            	//validation for csv data, full name, phone, email

            	if($this->isValidFullName($row[0]) && $this->isValidPhoneNumber($row[1]) && $this->isValidEmail($row[2])) {
	                foreach ($csvFields as $index => $value) {
	                	$content[$i][$value] = $row[$index];
	                }
	                $i++;
            	} else {
            		return array('result'=>'error', 'message' => 'Invalid data in CSV, :'.implode('--', $row));
            	}

            }
        }

        fclose($file);
        $response = array('result'=>'success', 'contacts' => &$content);
        return $response;
	}


	private function isValidFullName($name) {
		if(preg_match('/^[0-9a-zA-Z-_ ]{1,50}$/', $name)) {
			return true;
		}
		
		return false;
		
	}

	private function isValidPhoneNumber($phoneNumber) {
		if(preg_match('/^[\(\)+\-0-9]{7,15}$/', $phoneNumber)) {
			return true;
		}
		
		return false;
	}

	private function isValidEmail($email) {
		if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
		    return true;
		}

		return false;
	}




	// public function generateRecords() {
		//Code for generating random phonebook entries
		// for($i = 1; $i<=100; $i++ ){
		// 			$data = array();
		// 			$data['phone_number'] = '+1-'.rand(6666,9999).rand(100,999).rand(100,999); 
		// 			$data['full_name']    = 'Contact '.$i;
		// 			$data['email']        = 'contact_'.$i.'@yopmail.com';
		// 			$this->db->insert('phonebook', $data);
		// }
	// }

}

?>
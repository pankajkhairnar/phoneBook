<?php

class phonebook_model extends CI_Model {
    public $table =  'phonebook';
    public function __construct() {
        parent::__construct();
    }

    public function getContacts() {
        $fields = array('id', 'phone_number', 'full_name', 'email');
        $contactsObj = $this->db->select($fields)
                             ->from($this->table)
                             ->order_by('full_name')
                             ->get();
        
        return $contactsObj->result();  
    }
    
    public function update($data, $contactId) {
        return $this->db->where('id', $contactId)->update($this->table, $data);
    }

    public function delete($contactId) {
        return $this->db->delete($this->table, array('id' => $contactId));
    }

    public function export() {
        $this->load->dbutil();
        $fields = array('full_name as `Full Name`', 'phone_number as `Phone Number`', 'email as Email');
        $phonebook = $this->db->select($fields)
                              ->from($this->table)
                              ->order_by('full_name')
                              ->get();
        $delimiter = ",";
        $newline = "\r\n";
        return $this->dbutil->csv_from_result($phonebook, $delimiter, $newline);
    }

}
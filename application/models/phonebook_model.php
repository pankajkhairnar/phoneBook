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

    public function create($data) {
        return $this->db->insert($this->table, $data);
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

    public function sync_contacts($contacts) {

        if(count($contacts) <= 0) {
            return array('result' => 'success', 'message' => 'No contacts to update in file');
        }
        $fields = array('phone_number', 'full_name', 'email');
        $contactObjs = $this->db->select($fields)->get($this->table);


        foreach($contactObjs->result() as $contact) {
           $dbContacts[$contact->phone_number] = $contact;
        }

        echo '<pre>Db contacts';
        print_r($dbContacts);
        echo '<br><br><br>';
        echo '<pre>uploaded contact';
        print_r($contacts);
        $keepContacts = array();
        $response = array('update_count' => 0, 'new_count' => 0, 'delete_count'=>0, 'not_changed'=>0);

        foreach ($contacts as $key => $contact) {
            $data = array('full_name' => $contact['full_name'], 'email' => $contact['email']);      
            $phoneNumber = $contact['phone_number'];

            if(isset($dbContacts[$phoneNumber])) {//is number present in db

                if($dbContacts[$phoneNumber]->full_name != $contact['full_name'] ||
                    $dbContacts[$phoneNumber]->email != $contact['email']) {

                    $this->db->where('phone_number', $phoneNumber)->update($this->table, $data);
                    $response['update_count']++;
                } else {
                    $response['not_changed']++;
                }

                $keepContacts[] = $phoneNumber;
            } else { //create new record
                $data['phone_number'] = $phoneNumber;
                $keepContacts[]       = $phoneNumber;
                $this->db->insert($this->table, $data);
                $response['new_count']++;
            }

        }
        if(count($keepContacts) > 0) {
            $this->db->where_not_in('phone_number', $keepContacts)->delete($this->table);
            $response['delete_count'] = $this->db->affected_rows();
        }
        return $response;
    }

}
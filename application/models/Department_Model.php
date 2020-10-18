<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Department_Model extends CI_Model
{
    protected $department_table = 'department';

    /**
     * Add a new Department
     * @param: {array} Department Data
     */
    public function create_department(array $data) {
        $this->db->insert($this->department_table, $data);
        return $this->db->insert_id();
    }

    /**
     * Delete an Department
     * @param: {array} Department Data
     */
    public function delete_department(array $data)
    {
        /**
         * Check Department exist with id
         */
        $query = $this->db->get_where($this->department_table, $data);
        if ($this->db->affected_rows() > 0) {
            
            // Delete Department
            $this->db->delete($this->department_table, $data);
            if ($this->db->affected_rows() > 0) {
                return true;
            }
            return false;
        }   
        return false;
    }

    /**
     * Update a department
     * @param: {array} department Data
     */
    public function update_department(array $data)
    {
        /**
         * Check department exist with department_id
         */
        $query = $this->db->get_where($this->department_table, [
            'id' => $data['id'],
        ]);

        if ($this->db->affected_rows() > 0) {
            
            // Update an department
            $update_data = [
                'name' =>  $data['name'],
                'updated_at' => time(),
            ];

            return $this->db->update($this->department_table, $update_data, ['id' => $query->row('id')]);
        }   
        return false;
    }
    /**
     * Fetch a department
     * @param: Token
     */
    function fetch_department_records(){
        $this->db->select('*');
        $this->db->from($this->department_table);
        $this->db->order_by('name','asc');
        return $this->db->get()->result();
    }
}
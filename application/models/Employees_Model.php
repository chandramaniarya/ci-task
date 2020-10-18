<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Employees_Model extends CI_Model
{
    protected $employees_table = 'employees';



    /**
     * employee Login
     * ----------------------------------
     * @param: username or email address
     * @param: password
     */
    public function employee_login($username, $password)
    {
        $this->db->where('email', $username);
        $this->db->or_where('username', $username);
        $q = $this->db->get($this->employees_table);

        if( $q->num_rows() ) 
        {
            $user_pass = $q->row('password');
            if(md5($password) === $user_pass) {
                return $q->row();
            }
            return FALSE;
        }else{
            return FALSE;
        }
    }
    /**
     * employee search
     * ----------------------------------
     * @param: employee name
     */
    public function employee_search($name)
    {
        $this->db->select('*');
        $this->db->from($this->employees_table);
        $this->db->like('name', $name);
        return $this->db->get()->result();
    }
    /**
     * Add a new Department
     * @param: {array} Department Data
     */
    public function create_Employees(array $data) {
        $this->db->insert($this->employees_table, $data);
        return $this->db->insert_id();
    }

    /**
     * Delete an Department
     * @param: {array} Department Data
     */
    public function delete_employees(array $data)
    {
        /**
         * Check Department exist with id
         */
        $query = $this->db->get_where($this->employees_table, $data);
        if ($this->db->affected_rows() > 0) {
            
            // Delete Department
            $this->db->delete($this->employees_table, $data);
            if ($this->db->affected_rows() > 0) {
                return true;
            }
            return false;
        }   
        return false;
    }

    /**
     * Update a employees
     * @param: {array} employees Data
     */
    public function update_employees(array $data)
    {
        /**
         * Check employees exist with department_id
         */
        $query = $this->db->get_where($this->employees_table, [
            'id' => $data['id'],
        ]);

        if ($this->db->affected_rows() > 0) {
            
            // Update an employees
            $update_data = [
                'name' =>  $data['name'],
                'email'=>$data['email'],
                'username'=>$data['username'],
                'department_id'=>$data['department_id'],
                'updated_at' => time(),
            ];

            return $this->db->update($this->employees_table, $update_data, ['id' => $query->row('id')]);
        }   
        return false;
    }
    /**
     * Fetch a employees
     * @param: Token
     */
    function fetch_Employees_records(){
        $this->db->select('*');
        $this->db->from($this->employees_table);
        $this->db->order_by('name','asc');
        return $this->db->get()->result();
    }
}
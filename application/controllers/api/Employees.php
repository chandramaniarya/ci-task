<?php defined('BASEPATH') OR exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

require APPPATH . '/libraries/REST_Controller.php';
 
class Employees extends \Restserver\Libraries\REST_Controller
{
    public function __construct() {
        parent::__construct();
        // Load Employees Model
        $this->load->model('Employees_Model','EmployeesModel');
    }

    /**
     * Add new Employees with API
     * -------------------------
     * @method: POST
     */
    public function createEmployee_post()
    {
        header("Access-Control-Allow-Origin: *");
    
        // Load Authorization Token Library
        $this->load->library('Authorization_Token');

        /**
         * Employees Token Validation
         */
        $is_valid_token = $this->authorization_token->validateToken();
        //var_dump($is_valid_token);
        if (!empty($is_valid_token) AND $is_valid_token['status'] === TRUE)
        {
            # Create an Employees

            # XSS Filtering (https://www.codeigniter.com/Employees_guide/libraries/security.html)
            $_POST = $this->security->xss_clean($_POST);

            # Form Validation
            
            $this->form_validation->set_rules('name', 'Full Name', 'trim|required|max_length[50]');
            $this->form_validation->set_rules('department_id', 'Department Name', 'trim|required');
            $this->form_validation->set_rules('username', 'Username', 'trim|required|is_unique[employees.username]|alpha_numeric|max_length[20]',
                array('is_unique' => 'This %s already exists please enter another Username')
            );
            $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|max_length[80]|is_unique[employees.email]',
                array('is_unique' => 'This %s already exists please enter another email address')
            );
            $this->form_validation->set_rules('password', 'Password', 'trim|required|max_length[100]');
            if ($this->form_validation->run() == FALSE)
            {
                // Form Validation Errors
                $message = array(
                    'status' => false,
                    'error' => $this->form_validation->error_array(),
                    'message' => validation_errors()
                );

                $this->response($message, REST_Controller::HTTP_NOT_FOUND);
            }
            else
            {
                
                $insert_data = [
                    'name' => $this->input->post('name', TRUE),
                    'department_id' => $this->input->post('department_id', TRUE),
                    'email' => $this->input->post('email', TRUE),
                    'username' => $this->input->post('username', TRUE),
                    'password' => md5($this->input->post('password', TRUE)),
                    'created_at' => time(),
                    'updated_at' => time(),
                ];
                //print_r($insert_data);die;
                
                // Insert Employees
                $output = $this->EmployeesModel->create_Employees($insert_data);
                //$output;die;
                if ($output > 0 AND !empty($output))
                {
                    // Success
                    $message = [
                        'status' => true,
                        'message' => "Employees Created Successfully"
                    ];
                    $this->response($message, REST_Controller::HTTP_OK);
                } else
                {
                    // Error
                    $message = [
                        'status' => FALSE,
                        'message' => "Employees not created"
                    ];
                    $this->response($message, REST_Controller::HTTP_NOT_FOUND);
                }
            }

        } else {
            $this->response(['status' => FALSE, 'message' => $is_valid_token['message'] ], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    /**
     * Employee Login API
     * --------------------
     * @param: username or email
     * @param: password
     * --------------------------
     * @method : POST
     * @link: api/employee/login
     */
    public function login_post()
    {
        header("Access-Control-Allow-Origin: *");

        # XSS Filtering (https://www.codeigniter.com/Employees_guide/libraries/security.html)
        //$_POST = $this->security->xss_clean($_POST);
        
        # Form Validation
        $this->form_validation->set_rules('username', 'username', 'trim|required');
        $this->form_validation->set_rules('password', 'Password', 'trim|required|max_length[100]');
        if ($this->form_validation->run() == FALSE)
        {
            // Form Validation Errors
            $message = array(
                'status' => false,
                'error' => $this->form_validation->error_array(),
                'message' => validation_errors()
            );

            $this->response($message, REST_Controller::HTTP_NOT_FOUND);
        }
        else
        {
            // Load Login Function
            $output = $this->EmployeesModel->employee_login($this->input->post('username'), $this->input->post('password'));
            
            if (!empty($output) AND $output != FALSE)
            {
                // Load Authorization Token Library
                $this->load->library('Authorization_Token');

                // Generate Token
                $token_data['id'] = $output->id;
                $token_data['name'] = $output->name;
                $token_data['username'] = $output->username;
                $token_data['email'] = $output->email;
                $token_data['created_at'] = $output->created_at;
                $token_data['updated_at'] = $output->updated_at;
                $token_data['time'] = time();

                $Employees_token = $this->authorization_token->generateToken($token_data);
                //print_r($Employees_token);die;
                $return_data = [
                    'id' => $output->id,
                    'username' => $output->username,
                    'name' => $output->name,
                    'email' => $output->email,
                    'created_at' => $output->created_at,
                    'token' => $Employees_token,
                ];

                // Login Success
                $message = [
                    'status' => true,
                    'data' => $return_data,
                    'message' => "Employee login successful"
                ];
                $this->response($message, REST_Controller::HTTP_OK);
            } else
            {
                // Login Error
                $message = [
                    'status' => FALSE,
                    'message' => "Invalid username or Password"
                ];
                $this->response($message, REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }

    /**
     * Delete an Employees with API
     * @method: DELETE
     */
    public function deleteEmployees_delete($id)
    {
        header("Access-Control-Allow-Origin: *");
    
        // Load Authorization Token Library
        $this->load->library('Authorization_Token');

        /**
         * Employees Token Validation
         */
        $is_valid_token = $this->authorization_token->validateToken();
        //var_dump($is_valid_token);die;
        if (!empty($is_valid_token) AND $is_valid_token['status'] === TRUE)
        {
            # Delete a Employees
            $id = $this->security->xss_clean($id);
            //print_r($id);die;
            if (empty($id) AND !is_numeric($id))
            {
                $this->response(['status' => FALSE, 'message' => 'Invalid Employees ID' ], REST_Controller::HTTP_NOT_FOUND);
            }
            else
            {
                $delete_Employees = [
                    'id' => $id,
                ];

                // Delete an Employees
                $output = $this->EmployeesModel->delete_Employees($delete_Employees);

                if ($output > 0 AND !empty($output))
                {
                    // Success
                    $message = [
                        'status' => true,
                        'message' => "Employees Deleted"
                    ];
                    $this->response($message, REST_Controller::HTTP_OK);
                } else
                {
                    // Error
                    $message = [
                        'status' => FALSE,
                        'message' => "Employees not delete"
                    ];
                    $this->response($message, REST_Controller::HTTP_NOT_FOUND);
                }
            }

        } else {
            $this->response(['status' => FALSE, 'message' => $is_valid_token['message'] ], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update an Employees with API
     * @method: PUT
     */
    public function updateEmployees_put()
    {
        header("Access-Control-Allow-Origin: *");
    
        // Load Authorization Token Library
        $this->load->library('Authorization_Token');

        /**
         * Employees Token Validation
         */
        $is_valid_token = $this->authorization_token->validateToken();
        if (!empty($is_valid_token) AND $is_valid_token['status'] === TRUE)
        {
            # Update a Employees


            # XSS Filtering (https://www.codeigniter.com/Employees_guide/libraries/security.html)
            $_POST = json_decode($this->security->xss_clean(file_get_contents("php://input")), true);
            //print_r($_POST);die;
            $this->form_validation->set_data([
                'id' => $this->input->post('id', TRUE),
                'name' => $this->input->post('name', TRUE),
                'email' => $this->input->post('email', TRUE),
                'department_id' => $this->input->post('department_id', TRUE),
                'username' => $this->input->post('username', TRUE),
            ]);
            //print_r($_POST);die;
            # Form Validation
            $this->form_validation->set_rules('id', 'Employees ID', 'trim|numeric');
            $this->form_validation->set_rules('name', 'Employee Name', 'trim|required|max_length[50]');
            $this->form_validation->set_rules('department_id', 'Department Name', 'trim|required');
            $this->form_validation->set_rules('username', 'username', 'trim|required|is_unique[employees.username]|alpha_numeric|max_length[20]',
                array('is_unique' => 'This %s already exists please enter another Username')
            );
            $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|max_length[80]|is_unique[employees.email]',
                array('is_unique' => 'This %s already exists please enter another email address')
            );
            if ($this->form_validation->run() == FALSE)
            {
                // Form Validation Errors
                $message = array(
                    'status' => false,
                    'error' => $this->form_validation->error_array(),
                    'message' => validation_errors()
                );

                $this->response($message, REST_Controller::HTTP_NOT_FOUND);
            }
            else
            {
                $update_data = [
                    'id' => $this->input->post('id', TRUE),
                    'name' => $this->input->post('name', TRUE),
                    'username'=>$this->input->post('username',TRUE),
                    'email'=>$this->input->post('email',TRUE),
                    'department_id'=>$this->input->post('department_id',TRUE)
                ];
                //print_r($update_data);die;
                // Update an Employees
                $output = $this->EmployeesModel->update_Employees($update_data);
                //print_r($output);die;
                if ($output > 0 AND !empty($output))
                {
                    // Success
                    $message = [
                        'status' => true,
                        'message' => "Employees Updated Successfully"
                    ];
                    $this->response($message, REST_Controller::HTTP_OK);
                } else
                {
                    // Error
                    $message = [
                        'status' => FALSE,
                        'message' => "Employees not updated"
                    ];
                    $this->response($message, REST_Controller::HTTP_NOT_FOUND);
                }
            }
        } else {
            $this->response(['status' => FALSE, 'message' => $is_valid_token['message'] ], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    function fetchEmployees_get(){
        header("Access-Control-Allow-Origin: *");
        //Load Authorization Token
        $this->load->library('Authorization_Token');
        /**
        *Employees Token Validation
        */
        $is_valid_token     =   $this->authorization_token->validateToken();
        //var_dump($is_valid_token);
        if (!empty($is_valid_token) AND $is_valid_token['status'] === TRUE)
        {
            # Fetch Employee Records
            $output             =   $this->EmployeesModel->fetch_Employees_records();
            if ($output > 0 AND !empty($output))
            {
                // Success
                $message = [
                    'status' => true,
                    'message' => "Records Fetch",
                    'output'=>$output
                ];
                $this->response($message, REST_Controller::HTTP_OK);
            } else
            {
                // Error
                $message = [
                    'status' => FALSE,
                    'message' => "No Records Found !!!"
                ];
                $this->response($message, REST_Controller::HTTP_NOT_FOUND);
            }
        }else {
            $this->response(['status' => FALSE, 'message' => $is_valid_token['message'] ], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    /**
     * Employee Search API
     * --------------------
     * @param: name
     * --------------------------
     * @method : POST
     * @link: api/employee/search
     */
    public function search_post()
    {
        header("Access-Control-Allow-Origin: *");
        //Load Authorization Token
        $this->load->library('Authorization_Token');
        /**
        *Employees Token Validation
        */
        $is_valid_token     =   $this->authorization_token->validateToken();
        //var_dump($is_valid_token);
        if (!empty($is_valid_token) AND $is_valid_token['status'] === TRUE)
        {
            # Fetch employee Records
            $output             =   $this->EmployeesModel->employee_search($this->input->post('name'));
            //print_r($output);die;
            if ($output > 0 AND !empty($output))
            {
                // Success
                $message = [
                    'status' => true,
                    'message' => "Records Fetch",
                    'output'=>$output
                ];
                $this->response($message, REST_Controller::HTTP_OK);
            } else
            {
                // Error
                $message = [
                    'status' => FALSE,
                    'message' => "No Records Found !!!"
                ];
                $this->response($message, REST_Controller::HTTP_NOT_FOUND);
            }
        }else {
            $this->response(['status' => FALSE, 'message' => $is_valid_token['message'] ], REST_Controller::HTTP_NOT_FOUND);
        }
    }
}
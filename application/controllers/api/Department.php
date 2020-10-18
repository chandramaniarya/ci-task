<?php defined('BASEPATH') OR exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

require APPPATH . '/libraries/REST_Controller.php';
 
class Department extends \Restserver\Libraries\REST_Controller
{
    public function __construct() {
        parent::__construct();
        // Load Department Model
        $this->load->model('Department_Model','DepartmentModel');
    }

    /**
     * Add new Department with API
     * -------------------------
     * @method: POST
     */
    public function createDepartment_post()
    {
        header("Access-Control-Allow-Origin: *");
    
        // Load Authorization Token Library
        $this->load->library('Authorization_Token');

        /**
         * User Token Validation
         */
        $is_valid_token = $this->authorization_token->validateToken();
        //var_dump($is_valid_token);
        if (!empty($is_valid_token) AND $is_valid_token['status'] === TRUE)
        {
            # Create a User Article

            # XSS Filtering (https://www.codeigniter.com/user_guide/libraries/security.html)
            //$_POST = $this->security->xss_clean($_POST);
            
            # Form Validation
            $this->form_validation->set_rules('name', 'Department Name', 'trim|required|max_length[50]');
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
                    'created_at' => time(),
                    'updated_at' => time(),
                ];
                //print_r($insert_data);die;
                
                // Insert Department
                $output = $this->DepartmentModel->create_department($insert_data);
                //$output;die;
                if ($output > 0 AND !empty($output))
                {
                    // Success
                    $message = [
                        'status' => true,
                        'message' => "Department Created Successfully"
                    ];
                    $this->response($message, REST_Controller::HTTP_OK);
                } else
                {
                    // Error
                    $message = [
                        'status' => FALSE,
                        'message' => "Department not created"
                    ];
                    $this->response($message, REST_Controller::HTTP_NOT_FOUND);
                }
            }

        } else {
            $this->response(['status' => FALSE, 'message' => $is_valid_token['message'] ], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    /**
     * Delete an Department with API
     * @method: DELETE
     */
    public function deleteDepartment_delete($id)
    {
        header("Access-Control-Allow-Origin: *");
    
        // Load Authorization Token Library
        $this->load->library('Authorization_Token');

        /**
         * User Token Validation
         */
        $is_valid_token = $this->authorization_token->validateToken();
        if (!empty($is_valid_token) AND $is_valid_token['status'] === TRUE)
        {
            # Delete a Department

            # XSS Filtering (https://www.codeigniter.com/user_guide/libraries/security.html)
            $id = $this->security->xss_clean($id);
            
            if (empty($id) AND !is_numeric($id))
            {
                $this->response(['status' => FALSE, 'message' => 'Invalid Department ID' ], REST_Controller::HTTP_NOT_FOUND);
            }
            else
            {
                $delete_department = [
                    'id' => $id,
                ];

                // Delete an Department
                $output = $this->DepartmentModel->delete_department($delete_department);

                if ($output > 0 AND !empty($output))
                {
                    // Success
                    $message = [
                        'status' => true,
                        'message' => "Department Deleted"
                    ];
                    $this->response($message, REST_Controller::HTTP_OK);
                } else
                {
                    // Error
                    $message = [
                        'status' => FALSE,
                        'message' => "Department not delete"
                    ];
                    $this->response($message, REST_Controller::HTTP_NOT_FOUND);
                }
            }

        } else {
            $this->response(['status' => FALSE, 'message' => $is_valid_token['message'] ], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update an Department with API
     * @method: PUT
     */
    public function updateDepartment_put()
    {
        header("Access-Control-Allow-Origin: *");
    
        // Load Authorization Token Library
        $this->load->library('Authorization_Token');

        /**
         * User Token Validation
         */
        $is_valid_token = $this->authorization_token->validateToken();
        if (!empty($is_valid_token) AND $is_valid_token['status'] === TRUE)
        {
            # Update a Department


            # XSS Filtering (https://www.codeigniter.com/user_guide/libraries/security.html)
            $_POST = json_decode($this->security->xss_clean(file_get_contents("php://input")), true);
            //print_r($_POST);die;
            $this->form_validation->set_data([
                'id' => $this->input->post('id', TRUE),
                'name' => $this->input->post('name', TRUE),
            ]);
            //print_r($_POST);die;
            # Form Validation
            $this->form_validation->set_rules('id', 'Department ID', 'trim|numeric');
            $this->form_validation->set_rules('name', 'Department Name', 'trim|max_length[50]');
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
                ];
                //print_r($update_data);die;
                // Update an Department
                $output = $this->DepartmentModel->update_department($update_data);
                //print_r($output);die;
                if ($output > 0 AND !empty($output))
                {
                    // Success
                    $message = [
                        'status' => true,
                        'message' => "Department Updated Successfully"
                    ];
                    $this->response($message, REST_Controller::HTTP_OK);
                } else
                {
                    // Error
                    $message = [
                        'status' => FALSE,
                        'message' => "Department not updated"
                    ];
                    $this->response($message, REST_Controller::HTTP_NOT_FOUND);
                }
            }
        } else {
            $this->response(['status' => FALSE, 'message' => $is_valid_token['message'] ], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    function fetchDepartment_get(){
        header("Access-Control-Allow-Origin: *");
        //Load Authorization Token
        $this->load->library('Authorization_Token');
        /**
        *User Token Validation
        */
        $is_valid_token     =   $this->authorization_token->validateToken();
        //var_dump($is_valid_token);
        if (!empty($is_valid_token) AND $is_valid_token['status'] === TRUE)
        {
            # Fetch Users Records
            $output             =   $this->DepartmentModel->fetch_department_records();
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
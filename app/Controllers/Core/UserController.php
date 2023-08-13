<?php
/**
 *
 * @author Samu
 */
namespace App\Controllers\Core;

use App\Controllers\Core\AuthController;
use App\Models\Core\UserModel;

class UserController extends AuthController
{
    protected $usermodel;

    public function __construct() {
        parent::__construct();
        $this->usermodel = new UserModel();
    }

    public function index()
    {
        return $this->respond($this->successResponse(200, "", $this->usermodel->getUsers()), 200);
    }

    public function get()
    {
        try {
            $userid = $this->request->getVar('userid');

            if ( !isset($userid) ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            return $this->respond($this->successResponse(200, "", $this->usermodel->getUser($userid)), 200);

        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
        }
    }

    public function save()
	{
		$this->setValidationRules('save');

        if ( $this->isValid() ) {           
        
			$user = [
                'tenantid'=> 1, //get this from token 
				'firstname'=> trim($this->request->getVar('firstname')), 
				'lastname'=> trim($this->request->getVar('lastname')),
                'email'=> trim($this->request->getVar('email')),
                'tenantroleid'=> trim($this->request->getVar('roleid'))
			];

			if ( !$this->usermodel->save_user($user) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_USER_CREATED, ['id'=>$this->usermodel->getInsertID()]), 200);
        
		} else {
            return $this->respond($this->errorResponse(400,$this->errors), 400);
        }
	}

    private function setValidationRules($type='')
    {
        if ( $type == 'save' ) {
            $this->validation->setRules([
                'firstname' => [
                    'label'  => 'First Name',
                    'rules'  => 'required'
                ],
                'lastname' => [
                    'label'  => 'Last Name',
                    'rules'  => 'required'
				],
                'email' => [
                    'label'  => 'Email',
                    'rules'  => 'required|is_unique[_tenantusers.email]'
				],
                'roleid' => [
                    'label'  => 'Role',
                    'rules'  => 'required'
				]
            ]);
        } elseif ( $type == 'update' ) {
            // $this->validation->setRules([
            //     'courseid' => [
            //         'label'  => 'Course ID',
            //         'rules'  => 'required'
            //     ],
            //     'coursetype' => [
            //         'label'  => 'Course Type',
            //         'rules'  => 'required'
            //     ],
            //     'coursename' => [
            //         'label'  => 'Course Name',
            //         'rules'  => 'required'
			// 	]
            // ]);
        } else {
            $this->validation->setRules([]);
        }
    }  

}
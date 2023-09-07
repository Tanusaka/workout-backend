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
        $exclude = [
            '_users.id' => $this->getAuthID()
        ];
        return $this->respond($this->successResponse(200, "", $this->usermodel->getUsers($exclude)), 200);
    }

    public function get()
    {
        try {
            $userid = $this->request->getVar('userid');

            if ( !isset($userid) ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            if ( $userid == $this->getAuthID() ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            return $this->respond($this->successResponse(200, "", $this->usermodel->getUser($userid)), 200);

        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
        }
    }

    public function get_data()
    {
        try {
            $userid = $this->request->getVar('userid');

            if ( !isset($userid) ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            if ( $userid == $this->getAuthID() ) {
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
                'roleid'=> trim($this->request->getVar('roleid'))
			];

			if ( !$this->usermodel->save_user($user) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_USER_CREATED, ['id'=>$this->usermodel->getInsertID()]), 200);
        
		} else {
            return $this->respond($this->errorResponse(400,$this->errors), 400);
        }
	}

    public function update()
    {
        $this->setValidationRules('update');

        if ( $this->isValid() ) {           
        
            $userid = trim($this->request->getVar('userid'));
			
            $extuser = $this->usermodel->getUser($userid);

            if ( empty($extuser) ) {
                return $this->respond($this->errorResponse(404,"User cannot be found."), 404);
            }

            $user = [];

            if ( !is_null($this->request->getVar('firstname')) ) {
                $user['firstname'] = trim($this->request->getVar('firstname'));
            }

            if ( !is_null($this->request->getVar('lastname')) ) {
                $user['lastname'] = trim($this->request->getVar('lastname'));
            }

            if ( !is_null($this->request->getVar('dob')) ) {
                $user['dob'] = trim($this->request->getVar('dob'));
            }

            if ( !is_null($this->request->getVar('gender')) ) {
                $user['gender'] = trim($this->request->getVar('gender'));
            }

            if ( !is_null($this->request->getVar('mobile')) ) {
                $user['mobile'] = trim($this->request->getVar('mobile'));
            }

            if ( !is_null($this->request->getVar('address1')) ) {
                $user['address1'] = trim($this->request->getVar('address1'));
            }

            if ( !is_null($this->request->getVar('address2')) ) {
                $user['address2'] = trim($this->request->getVar('address2'));
            }

            if ( !is_null($this->request->getVar('city')) ) {
                $user['city'] = trim($this->request->getVar('city'));
            }

            if ( !is_null($this->request->getVar('country')) ) {
                $user['country'] = trim($this->request->getVar('country'));
            }

            if ( !is_null($this->request->getVar('status')) ) {
                $user['status'] = trim($this->request->getVar('status'));
            }

			if ( !$this->usermodel->update_user($user, $userid) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_USER_UPDATED), 200);
        
		} else {
            return $this->respond($this->errorResponse(400,$this->errors), 400);
        }
    }

    public function update_password()
    {
        $this->setValidationRules('update_password');

        if ( $this->isValid() ) {           
        
            $userid = trim($this->request->getVar('userid'));
			
            $extuser = $this->usermodel->getUser($userid);

            if ( empty($extuser) ) {
                return $this->respond($this->errorResponse(404,"User cannot be found."), 404);
            }

            $user = [];

            if ( !is_null($this->request->getVar('password')) ) {
                $user['password'] = trim($this->request->getVar('password'));
            }

			if ( !$this->usermodel->update_user($user, $userid) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_USER_PASSWORD_UPDATED), 200);
        
		} else {
            return $this->respond($this->errorResponse(400,$this->errors), 400);
        }
    }

    public function update_role()
    {
        $this->setValidationRules('update_role');

        if ( $this->isValid() ) {           
        
            $userid = trim($this->request->getVar('userid'));

            if ( $userid == $this->getAuthID() ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }
			
            $extuser = $this->usermodel->getUser($userid);

            if ( empty($extuser) ) {
                return $this->respond($this->errorResponse(404,"User cannot be found."), 404);
            }

            $user = [];

            if ( !is_null($this->request->getVar('roleid')) ) {
                $user['roleid'] = trim($this->request->getVar('roleid'));
            }
            
			if ( !$this->usermodel->update_user($user, $userid) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_USER_ROLE_UPDATED), 200);
        
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
                    'rules'  => 'required|is_unique[_users.email]'
				],
                'roleid' => [
                    'label'  => 'Role',
                    'rules'  => 'required'
				]
            ]);
        } elseif ( $type == 'update' ) {
            $this->validation->setRules([
                'userid' => [
                    'label'  => 'User ID',
                    'rules'  => 'required'
                ],
                'firstname' => [
                    'label'  => 'First Name',
                    'rules'  => 'required'
                ],
                'lastname' => [
                    'label'  => 'Last Name',
                    'rules'  => 'required'
				]
            ]);
        } elseif ( $type == 'update_password' ) {
            $this->validation->setRules([
                'userid' => [
                    'label'  => 'User ID',
                    'rules'  => 'required'
                ],
                'current_password' => [
                    'label'  => 'Current Password',
                    'rules'  => 'required|check_current_password[]'
                ],
                'password' => [
                    'label'  => 'Password',
                    'rules'  => 'required'
                ],
                'confirm_password' => [
                    'label'  => 'Confirm Password',
                    'rules'  => 'required|matches[password]'
                ],
            ]);
        } elseif ( $type == 'update_role' ) {
            $this->validation->setRules([
                'userid' => [
                    'label'  => 'User ID',
                    'rules'  => 'required'
                ],
                'roleid' => [
                    'label'  => 'User Role',
                    'rules'  => 'required'
                ],
            ]);
        } else {
            $this->validation->setRules([]);
        }
    }  

}

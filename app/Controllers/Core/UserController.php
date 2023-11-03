<?php
/**
 *
 * @author Samu
 */
namespace App\Controllers\Core;

use App\Controllers\Core\AuthController;
use App\Models\Core\UserModel;
use App\Models\Core\RoleModel;
use App\Models\Core\ConnectionModel;

class UserController extends AuthController
{
    protected $usermodel;
    protected $rolemodel;
    protected $connectionmodel;

    public function __construct() {
        parent::__construct();
        $this->usermodel = new UserModel();
        $this->rolemodel = new RoleModel();
        $this->connectionmodel = new ConnectionModel();
    }

    public function index()
    {
        if ( $this->rolemodel->getRoleName($this->getAuthRoleID()) == 'Super Administrator' ) {
            return $this->respond($this->successResponse(200, "", $this->usermodel->getUsers(true)), 200);
        }

        return $this->respond($this->successResponse(200, "", $this->usermodel->getUsers()), 200);
    }

    public function get()
    {
        try {
            $id = $this->request->getVar('id');

            if ( !isset($id) ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $user = $this->usermodel->getUser($id);

            if ( $this->rolemodel->getRoleName($this->getAuthRoleID()) == $user['rolename'] ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            if ( empty($user) ) {
                return $this->respond($this->errorResponse(404,"User cannot be found."), 404);
            }

            $user['connections'] = $this->connectionmodel->getUserConnections($id);

            return $this->respond($this->successResponse(200, "", $user), 200);

        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
        }
    }

    public function getMyProfile()
    {
        try {
            $id = $this->getAuthID();

            if ( !isset($id) ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $user = $this->usermodel->getUser($id);

            if ( empty($user) ) {
                return $this->respond($this->errorResponse(404,"User Profile cannot be found."), 404);
            }

            return $this->respond($this->successResponse(200, "", $user), 200);

        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
        }
    }

    public function getTrainers()
    {
        try {
            return $this->respond($this->successResponse(200, "", $this->usermodel->getTrainers()), 200);
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

            if ( $this->rolemodel->getRoleName($user['roleid']) == 'Super Administrator' ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            if ( !$this->usermodel->saveUser($user) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}
            
            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_USER_CREATED, 
            ['user'=>$this->usermodel->getUser($this->usermodel->getInsertID())]), 200);
        
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

            if ( $this->rolemodel->getRoleName($extuser['roleid']) == 'Super Administrator' ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $user = [
                'firstname'=> trim($this->request->getVar('firstname')),
                'lastname'=> trim($this->request->getVar('lastname')),
                'dob'=> trim($this->request->getVar('dob')),
                'gender'=> trim($this->request->getVar('gender')),
                'mobile'=> trim($this->request->getVar('mobile')),
                'address1'=> trim($this->request->getVar('address1')),
                'address2'=> trim($this->request->getVar('address2')),
                'city'=> trim($this->request->getVar('city')),
                'country'=> trim($this->request->getVar('country'))
			];

            $profileimageid = trim($this->request->getVar('profileimageid'));
            if ($profileimageid!="") {
                $user['profileimageid'] = $profileimageid;
            }

			if ( !$this->usermodel->updateUser($user, $userid) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_USER_UPDATED, 
            ['user'=>$this->usermodel->getUser($userid)]), 200);
        
		} else {
            return $this->respond($this->errorResponse(400,$this->errors), 400);
        }
    }

    public function updateRole()
    {
        $this->setValidationRules('update_role');

        if ( $this->isValid() ) {           
        
            $userid = trim($this->request->getVar('userid'));
			
            $extuser = $this->usermodel->getUser($userid);

            if ( empty($extuser) ) {
                return $this->respond($this->errorResponse(404,"User cannot be found."), 404);
            }

            $user = [
                'roleid'=> trim($this->request->getVar('roleid'))
			];

            if ( $this->rolemodel->getRoleName($extuser['roleid']) == 'Super Administrator' ||
                 $this->rolemodel->getRoleName($user['roleid']) == 'Super Administrator'
            ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

			if ( !$this->usermodel->updateUser($user, $userid) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_USER_ROLE_UPDATED, 
            ['user'=>$this->usermodel->getUser($userid)]), 200);
        
		} else {
            return $this->respond($this->errorResponse(400,$this->errors), 400);
        }
    }

    public function updatePassword()
    {
        $this->setValidationRules('update_password');

        if ( $this->isValid() ) {           
        
            $userid = trim($this->request->getVar('userid'));
			
            $extuser = $this->usermodel->getUser($userid);

            if ( empty($extuser) ) {
                return $this->respond($this->errorResponse(404,"User cannot be found."), 404);
            }

            if ( $this->rolemodel->getRoleName($extuser['roleid']) == 'Super Administrator' ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $user = [
                'password'=> trim($this->request->getVar('password'))
			];

			if ( !$this->usermodel->updateUser($user, $userid) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_USER_PASSWORD_UPDATED, 
            ['user'=>$this->usermodel->getUser($userid)]), 200);
        
		} else {
            return $this->respond($this->errorResponse(400,$this->errors), 400);
        }
    }

    public function updateDescription()
    {
        $this->setValidationRules('update_description');

        if ( $this->isValid() ) {           
        
            $userid = trim($this->request->getVar('userid'));
			
            $extuser = $this->usermodel->getUser($userid);

            if ( empty($extuser) ) {
                return $this->respond($this->errorResponse(404,"User cannot be found."), 404);
            }

            if ( $this->rolemodel->getRoleName($extuser['roleid']) == 'Super Administrator' ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $user = [
                'description'=> trim($this->request->getVar('description'))
			];

			if ( !$this->usermodel->updateUser($user, $userid) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_USER_DESCRIPTION_UPDATED, 
            ['user'=>$this->usermodel->getUser($userid)]), 200);
        
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
        } elseif ( $type == 'update_description' ) {
            $this->validation->setRules([
                'userid' => [
                    'label'  => 'User ID',
                    'rules'  => 'required'
                ],
                'description' => [
                    'label'  => 'Description',
                    'rules'  => 'required'
                ],
            ]);
        } else {
            $this->validation->setRules([]);
        }
    }  

}

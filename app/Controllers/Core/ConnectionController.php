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

class ConnectionController extends AuthController
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

    public function get()
    {
        try {
            $id = $this->request->getVar('id');

            if ( !isset($id) ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $connections = $this->connectionmodel->getUserConnections($id);

            return $this->respond($this->successResponse(200, "", $connections), 200);

        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
        }
    }

    public function getUserRoleConnections()
    {
        try {
            $id = $this->request->getVar('id');

            if ( !isset($id) ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $user = $this->usermodel->getUser($id);

            if ( empty($user) ) {
                return $this->respond($this->errorResponse(404,"Primary User cannot be found."), 404);
            }

            if ( $user['rolename'] == "Super Administrator" || $user['rolename'] == "Administrator" ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $connections = $this->connectionmodel->getUserRoleConnections($id, $user['rolename']);

            return $this->respond($this->successResponse(200, "", $connections), 200);

        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
        }
    }

    public function getTrainerConnections()
    {
        try {
            $id = $this->request->getVar('id');

            if ( !isset($id) ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $user = $this->usermodel->getUser($id);

            if ( empty($user) ) {
                return $this->respond($this->errorResponse(404,"Primary User cannot be found."), 404);
            }

            if ( $user['rolename'] == "Super Administrator" || $user['rolename'] == "Administrator" || $user['rolename'] == "Trainer" ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $connections = $this->connectionmodel->getTrainerConnections($id);

            return $this->respond($this->successResponse(200, "", $connections), 200);

        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
        }
    }

    public function getStudentConnections()
    {
        try {
            $id = $this->request->getVar('id');

            if ( !isset($id) ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $user = $this->usermodel->getUser($id);

            if ( empty($user) ) {
                return $this->respond($this->errorResponse(404,"Primary User cannot be found."), 404);
            }

            if ( $user['rolename'] == "Super Administrator" || $user['rolename'] == "Administrator" || $user['rolename'] == "Student" ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $connections = $this->connectionmodel->getStudentConnections($id);

            return $this->respond($this->successResponse(200, "", $connections), 200);

        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
        }
    }

    public function getParentConnections()
    {
        try {
            $id = $this->request->getVar('id');

            if ( !isset($id) ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $user = $this->usermodel->getUser($id);

            if ( empty($user) ) {
                return $this->respond($this->errorResponse(404,"Primary User cannot be found."), 404);
            }

            if ( $user['rolename'] == "Super Administrator" || $user['rolename'] == "Administrator" || $user['rolename'] == "Parent" ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $connections = $this->connectionmodel->getParentConnections($id);

            return $this->respond($this->successResponse(200, "", $connections), 200);

        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
        }
    }

    public function save()
	{
		$this->setValidationRules('save');

        if ( $this->isValid() ) {   
            
            $tenantid = 1; //get this from token 
			$userid  = trim($this->request->getVar('userid'));
			$connid  = trim($this->request->getVar('connid'));

            if ( $this->connectionmodel->hasConnection($tenantid, $userid, $connid) ) {
                return $this->respond($this->errorResponse(400,"This connection already exist."), 400);
            }

            $primaryuser = $this->usermodel->getUser($userid);

            if ( empty($primaryuser) ) {
                return $this->respond($this->errorResponse(404,"Primary user cannot be found."), 404);
            }

            $secondaryuser = $this->usermodel->getUser($connid);

            if ( empty($secondaryuser) ) {
                return $this->respond($this->errorResponse(404,"Connecting User cannot be found."), 404);
            }

            if ( $primaryuser['rolename'] == 'Super Administrator' || $primaryuser['rolename'] == 'Administrator' ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            if ( $secondaryuser['rolename'] == 'Super Administrator' || $secondaryuser['rolename'] == 'Administrator' ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            if ( $primaryuser['rolename'] ==  $secondaryuser['rolename'] ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $connctions = [
                [
                    'tenantid' => $tenantid,
                    'userid'  => $userid,
                    'connid'  => $connid,
                    'contype' => $secondaryuser['rolename'],
                    'status'  => 'A'
                ],
                [
                    'tenantid' => $tenantid,
                    'userid'  => $connid,
                    'connid'  => $userid,
                    'contype' => $primaryuser['rolename'],
                    'status'  => 'A'
                ],
            ];

            if ( !$this->connectionmodel->saveConnection($connctions) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_CONNECTION_CREATED, 
            ['connections'=>$this->connectionmodel->getUserRoleConnections($userid, $primaryuser['rolename'])]), 200);
        
		} else {
            return $this->respond($this->errorResponse(400,$this->errors), 400);
        }
	}

    public function delete() {

        $this->setValidationRules('delete');

        if ( $this->isValid() ) {           
        
            $id = trim($this->request->getVar('id'));

            $primaryconnection = $this->connectionmodel->find($id);

            if ( empty($primaryconnection) ) {
                return $this->respond($this->errorResponse(404,"Connection cannot be found."), 404);
            }

            $secondaryconnection = $this->connectionmodel->getUserConnection(
                $primaryconnection['tenantid'], 
                $primaryconnection['connid'],
                $primaryconnection['userid']
            );

            $connections = [];

            array_push($connections, $primaryconnection['id']);

            if (!empty($secondaryconnection)) {
                array_push($connections, $secondaryconnection['id']);
            }

			if ( !$this->connectionmodel->deleteConnections($connections) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_CONNECTION_DELETED, 
            ['connections'=>$this->connectionmodel->getUserConnections($primaryconnection['userid'])]), 200);
        
		} else {
            return $this->respond($this->errorResponse(400,$this->errors), 400);
        }
    }

    private function setValidationRules($type='')
    {
        if ( $type == 'save' ) {
            $this->validation->setRules([
                'userid' => [
                    'label'  => 'User ID',
                    'rules'  => 'required'
                ],
                'connid' => [
                    'label'  => 'Connecting User ID',
                    'rules'  => 'required'
				]
            ]);
        } elseif ( $type == 'delete' ) {
            $this->validation->setRules([
                'id' => [
                    'label'  => 'Connection ID',
                    'rules'  => 'required'
                ]
            ]);
        } else {
            $this->validation->setRules([]);
        }
    }  

}

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

            if ( empty($this->usermodel->getUser($userid)) ) {
                return $this->respond($this->errorResponse(404,"User cannot be found."), 404);
            }

            if ( empty($this->usermodel->getUser($connid)) ) {
                return $this->respond($this->errorResponse(404,"Connectiong User cannot be found."), 404);
            }

            if ( $this->rolemodel->getRoleName($userid) == 'Super Administrator' || $this->rolemodel->getRoleName($userid) == 'Administrator' ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            if ( $this->rolemodel->getRoleName($connid) == 'Super Administrator' || $this->rolemodel->getRoleName($connid) == 'Administrator' ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $connctions = [
                [
                    'tenantid' => $tenantid,
                    'userid'  => $userid,
                    'connid'  => $connid,
                    'contype' => $this->rolemodel->getRoleName($connid),
                    'status'  => 'A'
                ],
                [
                    'tenantid' => $tenantid,
                    'userid'  => $connid,
                    'connid'  => $userid,
                    'contype' => $this->rolemodel->getRoleName($userid),
                    'status'  => 'A'
                ],
            ];

            if ( !$this->connectionmodel->saveConnection($connctions) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}
            
            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_CONNECTION_CREATED, 
            ['connection'=>$this->connectionmodel->getUserConnection($tenantid, $userid, $connid)]), 200);
        
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

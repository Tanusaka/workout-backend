<?php
/**
 *
 * @author Samu
 */
namespace App\Controllers\Core;

use App\Controllers\Core\AuthController;
use App\Models\Core\RoleModel;
use App\Models\Core\RolepermissionModel;

class RoleController extends AuthController
{
    protected $rolemodel;
    protected $rolepermissionmodel;

    public function __construct() {
        parent::__construct();
        $this->rolemodel = new RoleModel();
        $this->rolepermissionmodel = new RolepermissionModel();
    }

    public function index()
    {
        if ( $this->rolemodel->getRoleName($this->getAuthRoleID()) == 'Super Administrator' ) {
            return $this->respond($this->successResponse(200, "", $this->rolemodel->getRoles(true)), 200);
        }

        return $this->respond($this->successResponse(200, "", $this->rolemodel->getRoles()), 200);
    }

    public function get()
    {
        try {
            $id = $this->request->getVar('id');

            if ( !isset($id) ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            if ($this->getAuthRoleID() == $id) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $role = $this->rolemodel->getRole($id);

            if ( empty($role) ) {
                return $this->respond($this->errorResponse(404,"Role cannot be found."), 404);
            }

            $role['permissions'] = $this->rolepermissionmodel->getRolePermmissions($role['id']);

            return $this->respond($this->successResponse(200, "", $role), 200);

        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
        }
    }

    public function updatePermission()
    {
        $this->setValidationRules('update_permissions');

        if ( $this->isValid() ) {           
        
            $permissionid = trim($this->request->getVar('permissionid'));
			
            $extpermission = $this->rolepermissionmodel->getRolePermmission($permissionid);

            if ( empty($extpermission) ) {
                return $this->respond($this->errorResponse(404,"Permission cannot be found."), 404);
            }

            if ( $this->rolemodel->getRoleName($extpermission['rid']) == 'Super Administrator' ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $permission = [
                'access'=> trim($this->request->getVar('access'))
			];


			if ( !$this->rolepermissionmodel->updateRolePermission($permission, $permissionid) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_ROLE_PERMISSIONS_UPDATED, 
            ['permission'=>$this->rolepermissionmodel->getRolePermmission($permissionid)]), 200);
        
		} else {
            return $this->respond($this->errorResponse(400,$this->errors), 400);
        }
    }

    private function setValidationRules($type='')
    {
        if ( $type == 'update_permissions' ) {
            $this->validation->setRules([
                'permissionid' => [
                    'label'  => 'Permission ID',
                    'rules'  => 'required'
                ],
                'access' => [
                    'label'  => 'Access Level',
                    'rules'  => 'required'
                ]
            ]);
        } else {
            $this->validation->setRules([]);
        }
    }  
}
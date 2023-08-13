<?php
/**
 *
 * @author Samu
 */
namespace App\Controllers\Core;

use App\Controllers\Core\AuthController;
use App\Models\Core\RoleModel;

class RoleController extends AuthController
{
    protected $rolemodel;

    public function __construct() {
        parent::__construct();
        $this->rolemodel = new RoleModel();
    }

    public function index()
    {
        return $this->respond($this->successResponse(200, "", $this->rolemodel->getRoles()), 200);
    }

    public function get()
    {
        try {
            $roleid = $this->request->getVar('roleid');

            if ( !isset($roleid) ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            return $this->respond($this->successResponse(200, "", $this->rolemodel->getRole($roleid)), 200);

        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
        }
    }

    public function updatePermissions()
    {
        try {
            $permissionid = $this->request->getVar('permissionid');
            $mode = $this->request->getVar('mode');
            $access = $this->request->getVar('access');

            if ( !isset($permissionid) || !isset($mode) || !isset($access) ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $extpermission = $this->rolemodel->getRolePermmission($permissionid);

            if ( empty($extpermission) ) {
                return $this->respond($this->errorResponse(404,"Permission cannot be found."), 404);
            }

			if ( !$this->rolemodel->update_permission($mode, $access, $permissionid) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_ROLE_PERMISSIONS_UPDATED), 200);

        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
        }
    }
}
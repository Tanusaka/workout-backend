<?php
/**
 *
 * @author Samu
 */
namespace App\Models\Core;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table      = '_roles';
    protected $primaryKey = 'id';

    protected $protectFields    = false;

    // Callbacks
    protected $allowCallbacks       = true;
    protected $beforeInsert         = [];
    protected $afterInsert          = [];
    protected $beforeUpdate         = [];
    protected $afterUpdate          = [];
    protected $beforeFind           = [];
    protected $afterFind            = [];
    protected $beforeDelete         = [];
    protected $afterDelete          = [];

    public function getRoles($allRoles=false, $status='')
    {
		try {
			$roles = 
			$this->db->table('_roles')->select(['id', 'tenantid', 'rolename', 'roledesc', 'status'])
			->where('_roles.tenantid', 1);
			
      if (!$allRoles) {
          $roles->where('rolename !=', 'Super Administrator')->where('rolename !=', 'Administrator');
      }

			if ($status!='') {
				$roles->where('status', $status);
			} 
			
			return $roles->get()->getResultArray();

		} catch (\Exception $e) {
			throw new \Exception($e->getMessage());
		}
    }

    public function getRole($id=0)
    {
		try {
			$role = 
			$this->db->table('_roles')->select(['id', 'tenantid', 'rolename', 'roledesc', 'status'])
			->where('_roles.tenantid', 1)
			->where('_roles.id', $id);

			return $role->get()->getRowArray();

		} catch (\Exception $e) {
			throw new \Exception($e->getMessage());
		}
    }

    public function getRoleName($id=0) {
        return $this->where('id', $id)->first()['rolename'];
    }

}
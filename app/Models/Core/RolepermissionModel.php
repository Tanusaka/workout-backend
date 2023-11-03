<?php
/**
 *
 * @author Samu
 */
namespace App\Models\Core;

use CodeIgniter\Model;

class RolepermissionModel extends Model
{
    protected $table      = '_rolepermissions';
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

    
    public function getRolePermmissions($roleid=0)
    {
        return $this->db->table('_rolepermissions')->select(
        [
        '_rolepermissions.id',
        '_rolepermissions.rid',
        '_rolepermissions.pid',
        '_permissions.permissioncode',
        '_permissions.permissionslug',
        '_permissions.permissionname',
        '_permissions.permissiondesc',
        '_permissions.permissiontype',
        '_rolepermissions.access',
        '_rolepermissions.status'
        ])
        ->join('_roles', '_roles.id = _rolepermissions.rid')
        ->join('_permissions', '_permissions.id = _rolepermissions.pid')
        ->where('_roles.id', $roleid)
        ->where('_permissions.status', 'A')
        ->orderBy('_permissions.permissioncode', 'ASC')->orderBy('_permissions.id', 'ASC')
        ->get()->getResultArray();
    }

    public function getRolePermmission($id=0)
    {
        return $this->db->table('_rolepermissions')->select(
        [
        '_rolepermissions.id',
        '_rolepermissions.rid',
        '_rolepermissions.pid',
        '_permissions.permissioncode',
        '_permissions.permissionslug',
        '_permissions.permissionname',
        '_permissions.permissiondesc',
        '_permissions.permissiontype',
        '_rolepermissions.access',
        '_rolepermissions.status'
        ])
        ->join('_roles', '_roles.id = _rolepermissions.rid')
        ->join('_permissions', '_permissions.id = _rolepermissions.pid')
        ->where('_rolepermissions.id', $id)
        ->get()->getRowArray();
    }

    public function updateRolePermission($data=[], $id=null)
    {
		if ( is_null($data) ) { return false; }

		if ( isset($data['access']) ) { $this->set('access', $data['access']); }

		return $this->where('id', $id)->update();
    }

}
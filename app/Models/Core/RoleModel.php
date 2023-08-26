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

    public function getRoles($exclude=[])
    {
        $query = $this->db->table('_roles')->select(
        [
        'id',
        'rolename',
        'roledesc',
        'status',
        'updatedat'
        ])
        ->where('tenantid', 1);

        if (isset($exclude) && !empty($exclude)) {
            foreach ($exclude as $key => $value) {
            $query->where($key.' !=', $value);
            }
        }

      return $query->get()->getResultArray();
    }

    public function getRole($id=0)
    {
        $rarray = [];

        $role = $this->db->table('_roles')->select(
        [
        'id',
        'rolename',
        'roledesc',
        'status'
        ])
        ->where('tenantid', 1)
        ->where('id', $id)
        ->get()->getResult();

        if ( !empty($role)) {
            $rarray = [
                'id' => $role[0]->id,
                'rolename' => $role[0]->rolename,
                'roledesc' => $role[0]->roledesc,
                'status' => $role[0]->status,
                'permissions' => $this->getRolePermmissions($id)
            ];
        } 

        return json_decode(json_encode($rarray));
    }
    
    public function getRolePermmissions($roleid=0)
    {
        return $this->db->table('_rolepermissions')->select(
        [
        '_rolepermissions.id',
        '_rolepermissions.rid',
        '_rolepermissions.pid',
        '_permissions.permissioncode',
        '_permissions.permissionname',
        '_permissions.permissiondesc',
        '_rolepermissions.r_access',
        '_rolepermissions.w_access',
        '_rolepermissions.d_access',
        '_rolepermissions.r_enable',
        '_rolepermissions.w_enable',
        '_rolepermissions.d_enable'
        ])
        ->join('_roles', '_roles.id = _rolepermissions.rid')
        ->join('_permissions', '_permissions.id = _rolepermissions.pid')
        ->where('_roles.id', $roleid)
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
        '_permissions.permissionname',
        '_permissions.permissiondesc',
        '_rolepermissions.r_access',
        '_rolepermissions.w_access',
        '_rolepermissions.d_access',
        '_rolepermissions.r_enable',
        '_rolepermissions.w_enable',
        '_rolepermissions.d_enable'
        ])
        ->join('_roles', '_roles.id = _rolepermissions.rid')
        ->join('_permissions', '_permissions.id = _rolepermissions.pid')
        ->where('_rolepermissions.id', $id)
        ->get()->getResult();
    }

    public function update_permission($mode=null, $access=null, $id=null)
    {
        if ( is_null($mode) || is_null($access) || is_null($id) ) { return false; }

        $set_column = "";

        if ( $mode == 'read' ) {
            $set_column = 'r_access';
        } elseif ( $mode == 'write' ) {
            $set_column = 'w_access';
        } elseif ( $mode == 'delete' ) {
            $set_column = 'd_access';
        } else {
            return false;
        }

        if ( $access != '0' && $access != '1' ) {
            return false;
        }

        return $this->db->table('_rolepermissions')
        ->set($set_column, $access)->where('id', $id)->update();
    }

}
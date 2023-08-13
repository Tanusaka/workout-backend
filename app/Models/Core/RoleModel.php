<?php
/**
 *
 * @author Samu
 */
namespace App\Models\Core;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table      = '_tenantroles';
    protected $primaryKey = 'id';

    public function getRoles($status='')
    {
        return $this->db->table('_tenantroles')->select(
        [
        '_tenantroles.id',
        'rolename',
        'roledesc',
        '_tenantroles.status',
        '_tenantroles.updatedat'
        ])
        ->join('_roles', '_roles.id = _tenantroles.roleid')
        ->where('_tenantroles.tenantid', 1)
        ->get()->getResultArray();
    }

    public function getRole($id=0)
    {
        $rarray = [];

        $role = $this->db->table('_tenantroles')->select(
        [
        '_tenantroles.id',
        'rolename',
        'roledesc',
        '_tenantroles.status'
        ])
        ->join('_roles', '_roles.id = _tenantroles.roleid')
        ->where('_tenantroles.id', $id)
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
        return $this->db->table('_tenantrolepermissions')->select(
        [
        '_tenantrolepermissions.id',
        '_permissions.permissionname',
        '_tenantrolepermissions.r_access',
        '_tenantrolepermissions.w_access',
        '_tenantrolepermissions.d_access'
        ])
        ->join('_tenantroles', '_tenantroles.id = _tenantrolepermissions.trid')
        ->join('_permissions', '_permissions.id = _tenantrolepermissions.pmid')
        ->where('_tenantroles.id', $roleid)
        ->get()->getResultArray();
    }

    public function getRolePermmission($id=0)
    {
        return $this->db->table('_tenantrolepermissions')->select(
        [
        '_tenantrolepermissions.id',
        '_permissions.permissionname',
        '_tenantrolepermissions.r_access',
        '_tenantrolepermissions.w_access',
        '_tenantrolepermissions.d_access'
        ])
        ->join('_tenantroles', '_tenantroles.id = _tenantrolepermissions.trid')
        ->join('_permissions', '_permissions.id = _tenantrolepermissions.pmid')
        ->where('_tenantrolepermissions.id', $id)
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

        return $this->db->table('_tenantrolepermissions')
        ->set($set_column, $access)->where('id', $id)->update();
    }

}
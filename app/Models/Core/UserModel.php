<?php
/**
 *
 * @author Samu
 */
namespace App\Models\Core;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table      = '_tenantusers';
    protected $primaryKey = 'id';

    protected $protectFields    = false;

    // Callbacks
    protected $allowCallbacks       = true;
    protected $beforeInsert         = ['hashPassword', 'setStatus'];
    protected $afterInsert          = [];
    protected $beforeUpdate         = ['hashPassword'];
    protected $afterUpdate          = [];
    protected $beforeFind           = [];
    protected $afterFind            = [];
    protected $beforeDelete         = [];
    protected $afterDelete          = [];

    protected function setStatus(array $data)
    {   
      $data['data']['status'] = 'A';
      return $data;
    }

    protected function hashPassword(array $data)
    {   
      $default_pw = 'abc123';

      $data['data']['password'] = password_hash($default_pw, PASSWORD_BCRYPT);

      return $data;
    }

    public function getUsers($status='')
    {
      return $this->db->table('_tenantusers')->select(
      [
        '_tenantusers.id',
        'email',
        'firstname',
        'lastname',
        'rolename',
        '_tenantusers.lastinat',
        '_tenantusers.status'
      ])
      ->join('_tenantroles', '_tenantroles.id = _tenantusers.tenantroleid')
      ->join('_roles', '_roles.id = _tenantroles.roleid')
      ->where('_tenantusers.tenantid', 1)
      ->get()->getResultArray();
    }

    public function getUser($id=0)
    {
      $uarray = [];

      $user = $this->db->table('_tenantusers')->select(
      [
        '_tenantusers.id',
        'email',
        'firstname',
        'lastname',
        'rolename',
        '_tenantusers.lastinat',
        '_tenantusers.status'
      ])
      ->join('_tenantroles', '_tenantroles.id = _tenantusers.tenantroleid')
      ->join('_roles', '_roles.id = _tenantroles.roleid')
      ->where('_tenantusers.id', $id)
      ->get()->getResult();

    

      if ( !empty($user) ) {
        $uarray = [
            'id' => $user[0]->id,
            'email' => $user[0]->email,
            'firstname' => $user[0]->firstname,
            'lastname' => $user[0]->lastname,
            'rolename' => $user[0]->rolename,
            'lastinat' => $user[0]->lastinat,
            'status' => $user[0]->status
        ];
      }

      return json_decode(json_encode($uarray));

    }

    public function save_user($data=[])
    {
      return is_null($data) ? false : ( $this->insert($data) ? true : false );
    }

}
<?php
/**
 *
 * @author Samu
 */
namespace App\Models\Core;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table      = '_users';
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
      if ( isset($data['data']['password']) ) {
        $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_BCRYPT);
      }

      return $data;
    }

    public function fetchPassword($id=0) {
      return $this->where('id', $id)->get()->getResultArray()[0]['password'];
    }

    public function getUsers($exclude=[])
    {
      $query = $this->db->table('_users')->select(
      [
        '_users.id',
        'email',
        'firstname',
        'lastname',
        'rolename',
        '_users.lastinat',
        '_users.status'
      ])
      ->join('_roles', '_roles.id = _users.roleid')
      ->where('_users.tenantid', 1);

      if (isset($exclude) && !empty($exclude)) {
        foreach ($exclude as $key => $value) {
          $query->where($key.' !=', $value);
        }
      }

      return $query->get()->getResultArray();
    }

    public function getUsersByRole($roles=[]) {
      
      $this->table('_users')->select(
        [
          '_users.id',
          'email',
          'firstname',
          'lastname',
          'rolename',
          '_users.lastinat',
          '_users.status'
        ])
        ->join('_roles', '_roles.id = _users.roleid')
        ->where('_users.tenantid', 1);

        $count = 0;
        foreach ($roles as $role) {
          if ($count==0) {
            $this->where('rolename', $role);
          } else {
            $this->orWhere('rolename', $role);
          }
          
          $count++;
        }

        return $this->get()->getResultArray();
    }

    public function getUser($id=0)
    {
      $uarray = [];

      $user = $this->db->table('_users')->select(
      [
        '_users.id',
        'email',
        'firstname',
        'lastname',
        'dob',
        'gender',
        'mobile',
        'address1',
        'address2',
        'city',
        'country',
        'roleid',
        'rolename',
        '_users.lastinat',
        '_users.status'
      ])
      ->join('_roles', '_roles.id = _users.roleid')
      ->where('_users.id', $id)
      ->where('_users.tenantid', 1)
      ->get()->getResult();

      if ( !empty($user) ) {
        $uarray = [
            'id' => $user[0]->id,
            'email' => $user[0]->email,
            'firstname' => $user[0]->firstname,
            'lastname' => $user[0]->lastname,
            'dob' => $user[0]->dob,
            'gender' => $user[0]->gender,
            'mobile' => $user[0]->mobile,
            'address1' => $user[0]->address1,
            'address2' => $user[0]->address2,
            'city' => $user[0]->city,
            'country' => $user[0]->country,
            'roleid' => $user[0]->roleid,
            'rolename' => $user[0]->rolename,
            'lastinat' => $user[0]->lastinat,
            'status' => $user[0]->status
        ];
      }

      return json_decode(json_encode($uarray));

    }

    public function save_user($data=[])
    {
      $data['password'] = 'abc123';
      return is_null($data) ? false : ( $this->insert($data) ? true : false );
    }

    public function update_user($data=[], $id=null)
    {
      if ( empty($data) ) { return false; }
      return $this->set($data)->where('id', $id)->update();
    }

}
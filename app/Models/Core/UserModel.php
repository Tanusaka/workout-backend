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

    public function getUsers($allUsers=false, $status='')
    {
		try {
			$users = 
			$this->db->table('_users')->select('_users.id, _users.tenantid, _users.email, rolename, firstname, lastname, _files.type, _files.path AS profileimage,
			_users.lastinat, _users.status, _users.islogged AS active')
			->join('_roles', '_roles.id = _users.roleid')
			->join('_files', '_files.id = _users.profileimageid', 'left')
			->where('_users.tenantid', 1);

			if (!$allUsers) {
                $users->where('rolename !=', 'Super Administrator')->where('rolename !=', 'Administrator');
            }

			if ($status!='') {
				$users->where('status', $status);
			} 
			
			return $users->get()->getResultArray();

		} catch (\Exception $e) {
			throw new \Exception($e->getMessage());
		}
    }

    public function getUser($id=0)
    {
		try {
			$users = 
			$this->db->table('_users')->select('_users.id, _users.tenantid, email, roleid, rolename, firstname, lastname, dob, gender, _files.type, _files.path AS profileimage,
			description, mobile, address1, address2, city, country, lastinat, _users.status, _users.islogged AS active')
			->join('_roles', '_roles.id = _users.roleid')
			->join('_files', '_files.id = _users.profileimageid', 'left')
			->where('_users.tenantid', 1)
			->where('_users.id', $id)
			->where('rolename !=', 'Super Administrator');

			return $users->get()->getRowArray();

		} catch (\Exception $e) {
			throw new \Exception($e->getMessage());
		}
    }

	public function getTrainers($except=0) {
		try {
			
			$trainers = 
			$this->db->table('_users')->select('_users.id, rolename, firstname, lastname, email, _files.type, _files.path AS profileimage, description, _users.islogged AS active')
			->join('_roles', '_roles.id = _users.roleid')
			->join('_files', '_files.id = _users.profileimageid', 'left')
			->where('_users.tenantid', 1)
			->where('_users.status', 'A')
			->where('_roles.rolename', 'Trainer');
			
			if ($except > 0) {
			$trainers->where('_users.id !=', $except);
			}

			return $trainers->get()->getResultArray();

		} catch (\Exception $e) {
			throw new \Exception($e->getMessage());
		}
	}
  
	public function getUserProfile($id=0) {
		try {
			
			$profile = 
			$this->db->table('_users')->select('_users.id, rolename, firstname, lastname, email, _files.type, _files.path AS profileimage, description, _users.islogged AS active')
			->join('_roles', '_roles.id = _users.roleid')
			->join('_files', '_files.id = _users.profileimageid', 'left')
			->where('_users.id', $id)
			->get()->getRowArray();

			return $profile;

		} catch (\Exception $e) {
			throw new \Exception($e->getMessage());
		}
	}

    public function saveUser($data=[])
    {
		$data['password'] = 'abc123';
		return is_null($data) ? false : ( $this->insert($data) ? true : false );
    }

    public function updateUser($data=[], $id=null)
    {
		if ( is_null($data) ) { return false; }

		if ( isset($data['roleid']) ) { $this->set('roleid', $data['roleid']); }
		if ( isset($data['password']) ) { $this->set('password', $data['password']); }
		if ( isset($data['firstname']) ) { $this->set('firstname', $data['firstname']); }
		if ( isset($data['lastname']) ) { $this->set('lastname', $data['lastname']); }
		if ( isset($data['dob']) ) { $this->set('dob', $data['dob']); }
		if ( isset($data['gender']) ) { $this->set('gender', $data['gender']); }
		if ( isset($data['profileimageid']) ) { $this->set('profileimageid', $data['profileimageid']); }
		if ( isset($data['description']) ) { $this->set('description', $data['description']); }
		if ( isset($data['mobile']) ) { $this->set('mobile', $data['mobile']); }
		if ( isset($data['address1']) ) { $this->set('address1', $data['address1']); }
		if ( isset($data['address2']) ) { $this->set('address2', $data['address2']); }
		if ( isset($data['city']) ) { $this->set('city', $data['city']); }
		if ( isset($data['country']) ) { $this->set('country', $data['country']); }
		if ( isset($data['status']) ) { $this->set('status', $data['status']); }

		return $this->where('id', $id)->update();
    }

}
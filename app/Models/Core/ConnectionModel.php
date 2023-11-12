<?php
/**
 *
 * @author Samu
 */
namespace App\Models\Core;

use CodeIgniter\Model;

class ConnectionModel extends Model
{
    protected $table      = '_connections';
    protected $primaryKey = 'id';

    protected $protectFields    = false;

    // Callbacks
    protected $allowCallbacks       = true;
    protected $beforeInsert         = ['setStatus'];
    protected $afterInsert          = [];
    protected $beforeUpdate         = [];
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

    public function hasConnection($tenantid=0, $userid=0, $connid=0) {
		try {
			
            $connection  = $this->where('tenantid', $tenantid)->where('userid', $userid)->where('connid', $connid)->get()->getRowArray();

			if (!empty($connection)) {
                return true;
            }

            return false;

		} catch (\Exception $e) {
			throw new \Exception($e->getMessage());
		}
	}

	public function getUserConnections($userid=0) {
		try {
			
			$connections = 
			$this->db->table('_connections')->select('_connections.id, _connections.userid, _connections.connid, _users.firstname, _users.lastname, _files.type, CONCAT(_files.path, _files.name) AS profileimage, _connections.contype')
			->join('_users', '_users.id = _connections.connid')
			->join('_files', '_files.id = _users.profileimageid', 'left')
			->where('_connections.tenantid', 1)
			->where('_connections.status', 'A')
			->where('_connections.userid', $userid);
			

			return $connections->get()->getResultArray();

		} catch (\Exception $e) {
			throw new \Exception($e->getMessage());
		}
	}

	public function getUserConnectionsForChat($userid=0) {
		try {
			
			$where = "chat_members.connid IS NULL AND _connections.tenantid='1' AND _connections.status='A' AND _connections.userid='".$userid."'";

			$connections = 
			$this->db->table('_connections')->select('_connections.id, _connections.connid, _roles.rolename, _users.firstname, _users.lastname, CONCAT(_files.path, _files.name) AS profileimage, _connections.contype')
			->join('chat_members', 'chat_members.userid = _connections.userid AND chat_members.connid = _connections.connid', 'left')
			->join('_users', '_users.id = _connections.connid')
			->join('_roles', '_roles.id = _users.roleid')
			->join('_files', '_files.id = _users.profileimageid', 'left')
			->where($where);
		
			return $connections->get()->getResultArray();

		} catch (\Exception $e) {
			throw new \Exception($e->getMessage());
		}
	}

    public function getUserConnection($tenantid=0, $userid=0, $connid=0) {
		try {
			
			$connection = 
			$this->db->table('_connections')->select('_connections.id, _connections.userid, _connections.connid, _users.firstname, _users.lastname, _files.type, CONCAT(_files.path, _files.name) AS profileimage, _connections.contype')
			->join('_users', '_users.id = _connections.connid')
			->join('_files', '_files.id = _users.profileimageid', 'left')
			->where('_connections.tenantid', $tenantid)
			->where('_connections.userid', $userid)
            ->where('_connections.connid', $connid);

			return $connection->get()->getRowArray();

		} catch (\Exception $e) {
			throw new \Exception($e->getMessage());
		}
	}

	public function getUserRoleConnections($userid=0, $rolename="") {
		try {

			if ($rolename=="Trainer") {
				$where = "_connections.userid IS NULL AND _users.tenantid='1' AND _users.status='A' AND (_users.roleid='4' OR _users.roleid='5')";
			} elseif ($rolename=="Student") {
				$where = "_connections.userid IS NULL AND _users.tenantid='1' AND _users.status='A' AND (_users.roleid='3' OR _users.roleid='5')";
			} elseif ($rolename=="Parent") {
				$where = "_connections.userid IS NULL AND _users.tenantid='1' AND _users.status='A' AND (_users.roleid='3' OR _users.roleid='4')";
			} else {
				$where = '';
			}
        
			$users = 
			$this->db->table('_users')->select('_users.id, _users.tenantid, rolename, firstname, lastname, _files.type, CONCAT(_files.path, _files.name) AS profileimage, _users.status')
			->join('_roles', '_roles.id = _users.roleid')
			->join('_files', '_files.id = _users.profileimageid', 'left')
			->join('_connections', '_connections.connid = _users.id AND _connections.userid = '.$userid, 'left');

			if ($where!='') {
				return $users->where($where)->get()->getResultArray();
			}
			
			return [];

		} catch (\Exception $e) {
			throw new \Exception($e->getMessage());
		}
	}

	public function getTrainerConnections($userid=0) {
		try {

			$where = "_connections.userid IS NULL AND _users.tenantid='1' AND _users.status='A' AND _users.roleid='3'";
        
			$users = 
			$this->db->table('_users')->select('_users.id, _users.tenantid, rolename, firstname, lastname, _files.type, CONCAT(_files.path, _files.name) AS profileimage, _users.status')
			->join('_roles', '_roles.id = _users.roleid')
			->join('_files', '_files.id = _users.profileimageid', 'left')
			->join('_connections', '_connections.connid = _users.id AND _connections.userid = '.$userid, 'left')
			->where($where)
			->get()->getResultArray();

			return $users;

		} catch (\Exception $e) {
			throw new \Exception($e->getMessage());
		}
	}

	public function getStudentConnections($userid=0) {
		try {

			$where = "_connections.userid IS NULL AND _users.tenantid='1' AND _users.status='A' AND _users.roleid='4'";
        
			$users = 
			$this->db->table('_users')->select('_users.id, _users.tenantid, rolename, firstname, lastname, _files.type, CONCAT(_files.path, _files.name) AS profileimage, _users.status')
			->join('_roles', '_roles.id = _users.roleid')
			->join('_files', '_files.id = _users.profileimageid', 'left')
			->join('_connections', '_connections.connid = _users.id AND _connections.userid = '.$userid, 'left')
			->where($where)
			->get()->getResultArray();

			return $users;

		} catch (\Exception $e) {
			throw new \Exception($e->getMessage());
		}
	}

	public function getParentConnections($userid=0) {
		try {

			$where = "_connections.userid IS NULL AND _users.tenantid='1' AND _users.status='A' AND _users.roleid='5'";
        
			$users = 
			$this->db->table('_users')->select('_users.id, _users.tenantid, rolename, firstname, lastname, _files.type, CONCAT(_files.path, _files.name) AS profileimage, _users.status')
			->join('_roles', '_roles.id = _users.roleid')
			->join('_files', '_files.id = _users.profileimageid', 'left')
			->join('_connections', '_connections.connid = _users.id AND _connections.userid = '.$userid, 'left')
			->where($where)
			->get()->getResultArray();

			return $users;

		} catch (\Exception $e) {
			throw new \Exception($e->getMessage());
		}
	}

    public function saveConnection($data=[])
    {
		return is_null($data) ? false : ( $this->insertBatch($data) ? true : false );
    }

	public function deleteConnections($data=[])
	{
		if (!empty($data)) {
			return $this->delete($data);
		} 

		return false;
	}


}
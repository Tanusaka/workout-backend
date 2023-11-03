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
			$this->db->table('_connections')->select('_connections.id, _connections.userid, _connections.connid, _users.firstname, _users.lastname, CONCAT(_files.path, _files.name) AS profileimage, _connections.contype')
			->join('_users', '_users.id = _connections.connid')
			->join('_files', '_files.id = _users.profileimageid', 'left')
			->where('_connections.tenantid', 1)
			->where('_connections.status', 'A')
			->where('_connections.userid', $userid);
			
			// if ($except > 0) {
			// $connections->where('_users.id !=', $except);
			// }

			return $connections->get()->getResultArray();

		} catch (\Exception $e) {
			throw new \Exception($e->getMessage());
		}
	}

    public function getUserConnection($tenantid=0, $userid=0, $connid=0) {
		try {
			
			$connection = 
			$this->db->table('_connections')->select('_connections.id, _connections.userid, _connections.connid, _users.firstname, _users.lastname, CONCAT(_files.path, _files.name) AS profileimage, _connections.contype')
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

    public function saveConnection($data=[])
    {
		return is_null($data) ? false : ( $this->insertBatch($data) ? true : false );
    }


}
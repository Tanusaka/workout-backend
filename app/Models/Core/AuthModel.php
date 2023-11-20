<?php
/**
 *
 * @author Samu
 */
namespace App\Models\Core;


use CodeIgniter\Model;
use CodeIgniter\I18n\Time;

class AuthModel extends Model
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
    if (! isset($data['data']['password'] ) ) {
        return $data;
    }

    $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_BCRYPT);

    return $data;
  }

  public function getAuth($email=null)
  {
    return $this->where('email', $email)->first();
  }

  // public function getAuth($email=null, $tenantid=1)
  // {
  //   //replace tenantid=1 later with real tenant id
  //   return $this->where('tenantid', $tenantid)->where('email', $email)->first();
  // }

  public function isAuthActive($id=0)
  {
    try {
        return $this->where('id', $id)->first()['status'] === 'A' ? true : false;
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  public function isLoggedin($email=null)
  {
    try {
      return $this->getAuth($email)['islogged'] ? true : false;
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  public function getAuthID($email=null)
  {
    try {
      return $this->getAuth($email)['id'];
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  public function getAuthRoleID($email=null)
  {
    try {
      return $this->getAuth($email)['roleid'];
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  public function getAuthRole($email=null) {
    try {
      $roleid = $this->getAuthRoleID($email);
      return $this->db->table('_roles')->select('rolename')->where('_roles.id', $roleid)->get()->getRowArray();
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  public function getAccessToken($email=null)
  {
    try {
      return $this->getAuth($email)['atoken'];
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  public function getRefreshToken($email=null)
  {
    try {
      return $this->getAuth($email)['rtoken'];
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  public function updateTokens($tokens=[], $email=null)
  {
    try {
      return $this->set('atoken', $tokens['accessToken'])->set('rtoken', $tokens['refreshToken'])->where('email', $email)->update();
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  public function updateLogin($email=null)
  {
    return $this->set('islogged', 1)->set('lastinat', date('Y-m-d H:i:s'))->where('email', $email)->update();
  }

  public function updateLogout($email=null)
  {
    return $this->set('islogged', 0)->set('atoken', NULL)->set('rtoken', NULL)->set('lastoutat', date('Y-m-d H:i:s'))->where('email', $email)->update();
  }

  public function getAllPermissions($email=null)
  {
    try {

      $permissions =  $this->db->table('_rolepermissions')->select(
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
      ->where('_roles.id', $this->getAuthRoleID($email))
      ->get()->getResultArray();  

      $p_array=[];

      foreach ($permissions as $permission) {
        $p_array[$permission['permissionslug']] = $permission['access'];
      }
      
      return $p_array;

    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
    
  }

  public function getGuardPermission($email=null, $guard=null)
  {
    try {

      $permission =  $this->db->table('_rolepermissions')->select(
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
      ->where('_roles.id', $this->getAuthRoleID($email))->where('_permissions.permissionslug', $guard)
      ->get()->getRowArray();  
      
      if (empty($permission)) {
        return false;
      }
      
      return $permission['access'];

    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
    
  }

  public function saveToken($data=[])
  {
    return is_null($data) ? false : ( $this->db->table('_tokens')->insert($data) ? true : false );
  }

  public function getAuthUser($email)  {
    try {
			$users =        
			$this->db->table('_users')->select('_users.id, _users.tenantid, email, roleid, rolename, firstname, lastname, CONCAT(_files.path, _files.name) AS profileimage')
			->join('_roles', '_roles.id = _users.roleid')
      ->join('_files', '_files.id = _users.profileimageid', 'left')
			->where('_users.tenantid', 1)
			->where('_users.email', $email)
			->where('rolename !=', 'Super Administrator');

			return $users->get()->getRowArray();

		} catch (\Exception $e) {
			throw new \Exception($e->getMessage());
		}
  }
  
}

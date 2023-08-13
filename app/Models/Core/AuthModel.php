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
    if (! isset($data['data']['password'] ) ) {
        return $data;
    }

    $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_BCRYPT);

    return $data;
  }

  public function getAuth($email=null, $tenantid=1)
  {
    //replace tenantid=1 later with real tenant id
    return $this->where('tenantid', $tenantid)->where('email', $email)->first();
  }

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

  public function getRtID($email=null)
  {
    try {
      return $this->getAuth($email)['rtid'];
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

  public function getAllPermissions($email=null, $tenantid=1)
  {
    try {
      $parray = [];

      $permissions = 
      $this->db->table('_tenantrolepermissions')->select(['permissionname','r_access','w_access','d_access'])
      ->join('_tenantusers', '_tenantusers.tenantroleid = _tenantrolepermissions.trid')
      ->join('_permissions', '_permissions.id = _tenantrolepermissions.pmid')
      ->where('_tenantusers.id', $this->getAuthID($email))
      ->get()->getResultArray();

      foreach ($permissions as $p) {
        $access = [ 'read' => $p['r_access'], 'write' => $p['w_access'], 'delete' => $p['d_access'] ];
        $parray[$p['permissionname']] = $access;
      }

      return json_decode(json_encode($parray));

    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  public function getGuardPermissions($email=null, $guard=null)
  {
    try {
      $parray = [];

      $permissions = 
      $this->db->table('_tenantrolepermissions')->select(['permissionname','r_access','w_access','d_access'])
      ->join('_tenantusers', '_tenantusers.tenantroleid = _tenantrolepermissions.trid')
      ->join('_permissions', '_permissions.id = _tenantrolepermissions.pmid')
      ->where('_tenantusers.id', $this->getAuthID($email))->where('_permissions.permissionname', $guard)
      ->get()->getRowArray();

      if ( !is_null($permissions) ) {
        $parray = [ 'read' => $permissions['r_access'], 'write' => $permissions['w_access'], 'delete' => $permissions['d_access'] ];
      }                    
      
      return json_decode(json_encode($parray));

    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
    
  }

  public function saveToken($data=[])
  {
    return is_null($data) ? false : ( $this->db->table('_tenanttokens')->insert($data) ? true : false );
  }
  
}

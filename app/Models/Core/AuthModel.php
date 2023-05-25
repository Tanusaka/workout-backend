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

  protected $table      = '_auths';
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

  public function getAuth($username=null)
  {
    return $this->where('username', $username)->first();
  }

  public function isAuthActive($id=0)
  {
    try {
        return $this->where('id', $id)->first()['status'] === 'A' ? true : false;
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  public function isLoggedin($username=null)
  {
    try {
      return $this->getAuth($username)['islogged'] ? true : false;
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  public function getAuthID($username=null)
  {
    try {
      return $this->getAuth($username)['id'];
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  public function getRtID($username=null)
  {
    try {
      return $this->getAuth($username)['rtid'];
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  public function updateLogin($username=null)
  {
    return $this->set('islogged', 1)->set('lastinat', date('Y-m-d H:i:s'))->where('username', $username)->update();
  }

  public function updateLogout($username=null)
  {
    return $this->set('islogged', 0)->set('lastoutat', date('Y-m-d H:i:s'))->where('username', $username)->update();
  }

  public function getAllPermissions($authid=null)
  {
    try {
      $parray = [];

      $permissions = 
      $this->db->table('_privileges')->select(['permissionname','r_access','w_access','d_access'])
      ->join('_auths', '_auths.rtid = _privileges.rtid')
      ->join('_permissions', '_permissions.id = _privileges.pmid')
      ->where('_auths.id', $authid)
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

  public function getGuardPermissions($authid=null, $guard=null)
  {
    try {
      $parray = [];

      $permissions = 
      $this->db->table('_privileges')->select(['permissionname','r_access','w_access','d_access'])
      ->join('_auths', '_auths.rtid = _privileges.rtid')
      ->join('_permissions', '_permissions.id = _privileges.pmid')
      ->where('_auths.id', $authid)->where('_permissions.permissionname', $guard)
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
    return is_null($data) ? false : ( $this->db->table('_refreshtokens')->insert($data) ? true : false );
  }
  
}

<?php
/**
 *
 * @author Samu
 */
namespace App\Models\App\Linkedprofile;

use CodeIgniter\Model;

class LinkedprofileModel extends Model
{

    protected $table      = 'linked_profiles';
    protected $primaryKey = 'id';

    protected $protectFields    = false;

    // Dates
    protected $useTimestamps        = true;
    protected $dateFormat           = 'datetime';
    protected $createdField         = 'createdat';
    protected $updatedField         = 'updatedat';
    #protected $deletedField         = 'deleted_at';

    // Callbacks
    protected $allowCallbacks       = true;
    protected $beforeInsert         = ['setStatus'];
    // protected $afterInsert          = [];
    // protected $beforeUpdate         = [];
    // protected $afterUpdate          = [];
    // protected $beforeFind           = [];
    // protected $afterFind            = [];
    // protected $beforeDelete         = [];
    // protected $afterDelete          = [];


    protected function setStatus(array $data)
    {   
        $data['data']['status'] = 'A';
        return $data;
    }

    public function getLinkedProfiles($userid=0, $status='')
    {
        return $this->db->table('linked_profiles')->select(
        [
            '_users.id',
            'linked_profiles.id',
            'email',
            '_users.firstname',
            '_users.lastname',
            'rolename',
            '_users.status'
        ])
        ->join('_users', '_users.id = linked_profiles.linkedprofileid')
        ->join('_roles', '_roles.id = _users.roleid')
        ->where('linked_profiles.userid', $userid)
        ->get()->getResultArray();
    }

    public function getLinkedProfile($id=0)
    {
        return $this->db->table('linked_profiles')->select(
        [
            'linked_profiles.id',
            'linked_profiles.userid',
            'linked_profiles.linkedprofileid' ,
            'email',
            '_users.firstname',
            '_users.lastname',
            'rolename',
            '_users.status'
        ])
        ->join('_users', '_users.id = linked_profiles.linkedprofileid')
        ->join('_roles', '_roles.id = _users.roleid')
        ->where('linked_profiles.id', $id)->get()->getResult();   
    }

    public function getProfilesToLink($userid, $roles=[]) {

        $queryString = 'SELECT _users.id, firstname, lastname, rolename FROM _users 
        INNER JOIN _roles ON _roles.id = _users.roleid
        WHERE _users.id NOT IN ( SELECT linkedprofileid FROM linked_profiles WHERE userid = '.$userid.') 
        AND (_users.tenantid = 1)';

        $count = 0;
        foreach ($roles as $role) {
        if ($count==0) {
            $queryString = $queryString.' AND (_roles.rolename = "' .$role.'"';
        } else {
            $queryString = $queryString.' OR _roles.rolename = "' .$role.'"';
        }
        
        $count++;
        }

        $queryString = $queryString.');';

        return $this->db->query($queryString)->getResultArray();
        
    }  

    public function getLinkedRelatedProfile($userid, $linkid) {
        return $this->where('userid', $userid)->where('linkedprofileid', $linkid)->get()->getResult();  
    }

    public function saveLinkedProfile($data=[])
    {
        return is_null($data) ? false : ( $this->insert($data) ? true : false );
    }

    public function updateLinkedProfile($data=[], $id=null)
    {
        if ( empty($data) ) { return false; }
        return $this->set($data)->where('id', $id)->update();
    }

    public function deleteLinkedProfile($id=null)
    {
        return $this->where('id', $id)->delete();
    }

}
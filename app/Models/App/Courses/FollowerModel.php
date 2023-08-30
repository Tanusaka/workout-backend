<?php
/**
 *
 * @author Samu
 */
namespace App\Models\App\Courses;

use CodeIgniter\Model;

class FollowerModel extends Model
{

    protected $table      = 'course_followers';
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

    public function getFollowers($courseid=0) {
        return $this->db->table('course_followers')->select(
        [
            'course_followers.id',
            'course_followers.userid',
            'course_followers.type',
            '_users.firstname',
            '_users.lastname',
            'rolename'
        ])
        ->join('_users', '_users.id = course_followers.userid')
        ->join('_roles', '_roles.id = _users.roleid')
        ->where('course_followers.courseid', $courseid)
        ->get()->getResultArray();
    }

    public function getFollowersToLink($courseid=0) {

        $queryString = 'SELECT _users.id, CONCAT(firstname, " ", lastname) AS text FROM _users 
        INNER JOIN _roles ON _roles.id = _users.roleid
        WHERE _users.id NOT IN ( SELECT course_followers.userid FROM course_followers WHERE courseid = '.$courseid.') 
        AND (_users.tenantid = 1) AND (_roles.rolename = "Student" OR  _roles.rolename = "Parent") ';

        return $this->db->query($queryString)->getResultArray();
    } 

    public function getFollower($id=0) {
        return $this->db->table('course_followers')->select(
        [
            'course_followers.id',
            'course_followers.userid',
            'course_followers.type',
            '_users.firstname',
            '_users.lastname',
            'rolename'
        ])
        ->join('_users', '_users.id = course_followers.userid')
        ->join('_roles', '_roles.id = _users.roleid')
        ->where('course_followers.id', $id)
        ->get()->getResult();
    }

    public function saveFollower($data=[])
    {
        return is_null($data) ? false : ( $this->insert($data) ? true : false );
    }

    public function deleteFollower($id=null)
    {
        return $this->where('id', $id)->delete();
    }


}
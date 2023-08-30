<?php
/**
 *
 * @author Samu
 */
namespace App\Models\App\Courses;

use CodeIgniter\Model;

class InstructorModel extends Model
{

    protected $table      = 'course_instructors';
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

    public function getInstructors($courseid=0) {
        return $this->db->table('course_instructors')->select(
        [
            'course_instructors.id',
            'course_instructors.userid',
            'course_instructors.type',
            '_users.firstname',
            '_users.lastname',
            'rolename'
        ])
        ->join('_users', '_users.id = course_instructors.userid')
        ->join('_roles', '_roles.id = _users.roleid')
        ->where('course_instructors.courseid', $courseid)
        ->get()->getResultArray();
    }

    public function getInstructorsToLink($courseid=0) {

        $queryString = 'SELECT _users.id, CONCAT(firstname, " ", lastname) AS text FROM _users 
        INNER JOIN _roles ON _roles.id = _users.roleid
        WHERE _users.id NOT IN ( SELECT course_instructors.userid FROM course_instructors WHERE courseid = '.$courseid.') 
        AND (_users.tenantid = 1) AND _roles.rolename = "Trainer" ';

        return $this->db->query($queryString)->getResultArray();
    }  


    public function getInstructor($id=0) {
        return $this->db->table('course_instructors')->select(
        [
            'course_instructors.id',
            'course_instructors.userid',
            'course_instructors.type',
            '_users.firstname',
            '_users.lastname',
            'rolename'
        ])
        ->join('_users', '_users.id = course_instructors.userid')
        ->join('_roles', '_roles.id = _users.roleid')
        ->where('course_instructors.id', $id)
        ->get()->getResult();
    }

    public function saveInstructor($data=[])
    {
        return is_null($data) ? false : ( $this->insert($data) ? true : false );
    }

    public function deleteInstructor($id=null)
    {
        return $this->where('id', $id)->delete();
    }

}
<?php
/**
 *
 * @author Samu
 */
namespace App\Models\App\Courses;

use CodeIgniter\Model;

class EnrollmentModel extends Model
{

  protected $table      = 'course_enrollments';
  protected $primaryKey = 'id';

  protected $protectFields    = false;

  // Dates
  protected $useTimestamps        = true;
  protected $dateFormat           = 'datetime';
  protected $createdField         = 'createdat';
  protected $updatedField         = 'updatedat';
  #protected $deletedField        = 'deleted_at';

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
    $data['data']['Status'] = 'P';
    return $data;
  }

  public function getEnrollments($status='')
  {
    if ($status=='') {
      $enrollments = 
      $this->db->table('course_enrollments')->select(['course_enrollments.id','courseid','userid', 'firstname', 'lastname', 'profileimage', 'enrolleddate', 'course_enrollments.status'])
      ->join('_users', '_users.id = course_enrollments.userid')
      ->get()->getResultArray();
    } else {
      $enrollments = 
      $this->db->table('course_enrollments')->select(['course_enrollments.id','courseid','userid', 'firstname', 'lastname', 'profileimage', 'enrolleddate', 'course_enrollments.status'])
      ->join('_users', '_users.id = course_enrollments.userid')
      ->where('course_enrollments.status', $status)
      ->get()->getResultArray();
    }    

    return $enrollments;
  }

  public function getEnrollment($id=0) {
    return $this->where('id', $id)->first();
  }

  public function getCourseEnrollments($courseid=0, $limit=0)
  {
    try {
      
      $where = "course_enrollments.courseid = " . $this->escape($courseid) . " AND (_users.status='P' OR _users.status='A')";

      $enrollments = 
      $this->db->table('course_enrollments')->select('course_enrollments.id,course_enrollments.courseid,course_enrollments.userid, _users.firstname, _users.lastname, _files.type, CONCAT(_files.path, _files.name) AS profileimage, _roles.rolename, course_enrollments.enrolleddate, course_enrollments.status')
      ->join('_users', '_users.id = course_enrollments.userid')
      ->join('_files', '_files.id = _users.profileimageid', 'left')
      ->join('_roles', '_roles.id = _users.roleid')
      ->where($where)->orderBy('id', 'DESC');

      if ($limit>0) {
        $enrollments->limit($limit);
      }
      

      return $enrollments->get()->getResultArray();

    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  public function getUserEnrollments($userid=0)
  {
    try {

      $where = "course_enrollments.userid = " . $this->escape($userid) . " AND (_users.status='P' OR _users.status='A')";
      
      $enrollments = 
      $this->db->table('course_enrollments')->select('course_enrollments.id,courseid,userid, firstname, lastname, _files.type, CONCAT(_files.path, _files.name) AS profileimage, enrolleddate, course_enrollments.status')
      ->join('_users', '_users.id = course_enrollments.userid')
      ->join('_files', '_files.id = _users.profileimageid', 'left')
      ->where($where)
      ->get()->getResultArray();

      return $enrollments;

    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  public function getUsersForEnroll($courseid=0)
    {
      try {

        $where = "course_enrollments.userid IS NULL AND _users.tenantid='1' AND (_users.status='P' OR _users.status='A') AND (_users.roleid='4' OR _users.roleid='5')";
        
        $users = 
        $this->db->table('_users')->select('_users.id, _users.tenantid, rolename, firstname, lastname, _files.type, CONCAT(_files.path, _files.name) AS profileimage, _users.status')
        ->join('_roles', '_roles.id = _users.roleid')
        ->join('_files', '_files.id = _users.profileimageid', 'left')
        ->join('course_enrollments', 'course_enrollments.userid = _users.id AND course_enrollments.courseid = '.$courseid, 'left')
        ->where($where)
        ->get()->getResultArray();


        return $users;

      } catch (\Exception $e) {
        throw new \Exception($e->getMessage());
      }
    }

  public function saveCourseEnrollment($data=[])
  {
    return is_null($data) ? false : ( $this->insert($data) ? true : false );
  }

  public function updateCourseEnrollment($data=[], $id=null)
  {
    if ( is_null($data) ) { return false; }

    if ( isset($data['enrolleddate']) ) { $this->set('enrolleddate', $data['enrolleddate']); }
    if ( isset($data['status']) ) { $this->set('status', $data['status']); }

    return $this->where('id', $id)->update();
  }

}
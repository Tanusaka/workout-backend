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
    $data['data']['Status'] = 'A';
    return $data;
  }

  public function getEnrollments($courseid=0)
  {
    try {

      $where = "_users.tenantid='1' AND _users.status='A' AND (_users.roleid='4' OR _users.roleid='5')";

      $users = 
      $this->db->table('_users')->select('_users.id, _users.tenantid, rolename, firstname, lastname, _files.type, 
      _files.path AS profileimage, _users.status, 
      course_enrollments.id as enrolledid, course_enrollments.enrolleddate as enrolleddate, IF(course_enrollments.userid IS NULL, "0", "1") AS enrolled')
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

  public function getEnrollment($id=0) {
    return $this->where('id', $id)->first();
  }

  public function isEnrolled($courseid=0, $userid=0) {
    $enrollment = $this->where('courseid', $courseid)->where('userid', $userid)->get()->getRowArray();
    
    if (!empty($enrollment)) {
      return true;
    }

    return false;
  }

  public function saveEnrollment($data=[])
  {
    return is_null($data) ? false : ( $this->insert($data) ? true : false );
  }

}
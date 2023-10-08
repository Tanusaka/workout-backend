<?php
/**
 *
 * @author Samu
 */
namespace App\Models\App\Courses;

use CodeIgniter\Model;

class CourseEnrollmentsModel extends Model
{

  protected $table      = 'course_enrollments';
  protected $primaryKey = 'EnrollmentID';

  protected $protectFields    = false;

  // Dates
  protected $useTimestamps        = true;
  protected $dateFormat           = 'datetime';
  protected $createdField         = 'CreatedAt';
  protected $updatedField         = 'UpdatedAt';
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
    $data['data']['Status'] = 'A';
    return $data;
  }

  public function getEnrolledCourses($userId=0, $status='')
  {
    $selectColumns = ['EnrollmentID','UserID','CourseID','EnrollmentDate','CreatedAt', 'CreatedBy', 'UpdatedAt', 'UpdatedBy', 'Status'];

    if ($status=='') {
      return $this->select($selectColumns)->where('UserID', $userId)->findAll();
    } else {
      return $this->select($selectColumns)->where('UserID', $userId)->where('Status', $status)->findAll();
    }    
  }

  public function getEnrolledCourse($userId=0, $courseId=0)
  {
    try {
      $selectColumns = ['EnrollmentID','UserID','CourseID','EnrollmentDate','CreatedAt', 'CreatedBy', 'UpdatedAt', 'UpdatedBy', 'Status'];
  
      $courseEnrollment = $this->select($selectColumns)->where('UserID', $userId)->where('CourseID', $courseId)->first();

      if ( !isset($courseEnrollment) ) {
        return null;
      }      

      return $courseEnrollment;

    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  public function saveCourseEnrollment($data=[])
  {
    return is_null($data) ? false : ( $this->insert($data) ? true : false );
  }

  public function updateCourseEnrollment($enrolledCourse=[], $status='')
  {
    
    if ( is_null($enrolledCourse) ) { return false; }

    if ( isset($status) ) { $this->set('Status', $status); }

    return $this->where('EnrollmentID', $enrolledCourse['EnrollmentID'])->update();
  }

}
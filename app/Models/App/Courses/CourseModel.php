<?php
/**
 *
 * @author Samu
 */
namespace App\Models\App\Courses;

use CodeIgniter\Model;

class CourseModel extends Model
{

  protected $table      = 'courses';
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

  public function getCourses($status='')
  {
    try {

      $courses = 
      $this->db->table('courses')->select('courses.id, courses.tenantid, courses.coursename, courses.courseintro, 
      courses.coursedescription, courses.courselevel, courses.coursetype, _files.type, CONCAT(_files.path, _files.name) AS courseimage, 
      courses.instructorprofile, courses.priceplan, courses.price, courses.currencycode, courses.status,
      courses.createdat, courses.createdby, courses.updatedat, courses.updatedby')
      ->join('_files', '_files.id = courses.courseimageid', 'left')
      ->where('courses.tenantid', 1);
      
      if ($status!='') {
        $courses->where('courses.status', $status);
      }

      return $courses->get()->getResultArray();

    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }  
  }

  public function getCoursesByTrainer($trainerid=0, $status='')
  {
    try {

      $courses = 
      $this->db->table('courses')->select('courses.id, courses.tenantid, courses.coursename, courses.courseintro, 
      courses.coursedescription, courses.courselevel, courses.coursetype, _files.type, CONCAT(_files.path, _files.name) AS courseimage, 
      courses.instructorprofile, courses.priceplan, courses.price, courses.currencycode, courses.status,
      courses.createdat, courses.createdby, courses.updatedat, courses.updatedby')
      ->join('_files', '_files.id = courses.courseimageid', 'left')
      ->where('courses.tenantid', 1)
      ->where('instructorprofile', $trainerid);
      
      if ($status!='') {
        $courses->where('courses.status', $status);
      }

      return $courses->get()->getResultArray();

    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  public function getCoursesByEnrollment($userid=0, $status='')
  {
    try {
 
      $courses = 
      $this->db->table('courses')->select('courses.id, courses.tenantid, courses.coursename, courses.courseintro, 
      courses.coursedescription, courses.courselevel, courses.coursetype, _files.type, CONCAT(_files.path, _files.name) AS courseimage, 
      courses.instructorprofile, courses.priceplan, courses.price, courses.currencycode, courses.status,
      course_enrollments.id AS enrollmentid, course_enrollments.status AS enrolled, course_enrollments.enrolleddate,
      courses.createdat, courses.createdby, courses.updatedat, courses.updatedby')
      ->join('course_enrollments', 'course_enrollments.courseid = courses.id')
      ->join('_files', '_files.id = courses.courseimageid', 'left')
      ->where('courses.tenantid', 1)
      ->where('course_enrollments.userid', $userid);
      
      if ($status!='') {
        $courses->where('courses.status', $status);
      }

      return $courses->get()->getResultArray();

    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  public function getCourse($id=0)
  {
    try {

      $course = 
      $this->db->table('courses')->select('courses.id, courses.tenantid, courses.coursename, courses.courseintro, 
      courses.coursedescription, courses.courselevel, courses.coursetype, _files.type, CONCAT(_files.path, _files.name) AS courseimage, 
      courses.instructorprofile, courses.priceplan, courses.price, courses.currencycode, courses.status,
      courses.createdat, courses.createdby, courses.updatedat, courses.updatedby')
      ->join('_files', '_files.id = courses.courseimageid', 'left')
      ->where('courses.tenantid', 1)
      ->where('courses.id', $id);

      return $course->get()->getRowArray();

    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }  
  }

  public function getCourseByTrainer($id=0, $trainerid=0)
  {
    try {

      $course = 
      $this->db->table('courses')->select('courses.id, courses.tenantid, courses.coursename, courses.courseintro, 
      courses.coursedescription, courses.courselevel, courses.coursetype, _files.type, CONCAT(_files.path, _files.name) AS courseimage, 
      courses.instructorprofile, courses.priceplan, courses.price, courses.currencycode, courses.status,
      courses.createdat, courses.createdby, courses.updatedat, courses.updatedby')
      ->join('_files', '_files.id = courses.courseimageid', 'left')
      ->where('courses.tenantid', 1)
      ->where('courses.id', $id)
      ->where('instructorprofile', $trainerid);

      return $course->get()->getRowArray();

    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }  
  }

  public function getCourseByEnrollment($id=0, $userid=0)
  {
    try {
 
    $course = 
      $this->db->table('courses')->select('courses.id, courses.tenantid, courses.coursename, courses.courseintro, 
      courses.coursedescription, courses.courselevel, courses.coursetype, _files.type, CONCAT(_files.path, _files.name) AS courseimage, 
      courses.instructorprofile, courses.priceplan, courses.price, courses.currencycode, courses.status,
      course_enrollments.id AS enrollmentid, course_enrollments.status AS enrolled, course_enrollments.enrolleddate,
      courses.createdat, courses.createdby, courses.updatedat, courses.updatedby')
      ->join('course_enrollments', 'course_enrollments.courseid = courses.id')
      ->join('_files', '_files.id = courses.courseimageid', 'left')
      ->where('courses.tenantid', 1)
      ->where('courses.id', $id)
      ->where('course_enrollments.userid', $userid);
      
      return $course->get()->getRowArray();

    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  public function saveCourse($data=[])
  {
    return is_null($data) ? false : ( $this->insert($data) ? true : false );
  }

  public function updateCourse($data=[], $id=null)
  {
    if ( is_null($data) ) { return false; }

    if ( isset($data['coursename']) ) { $this->set('coursename', $data['coursename']); }
    if ( isset($data['courseintro']) ) { $this->set('courseintro', $data['courseintro']); }
    if ( isset($data['coursedescription']) ) { $this->set('coursedescription', $data['coursedescription']); }
    if ( isset($data['courselevel']) ) { $this->set('courselevel', $data['courselevel']); }
    if ( isset($data['coursetype']) ) { $this->set('coursetype', $data['coursetype']); }
    if ( isset($data['courseimageid']) ) { $this->set('courseimageid', $data['courseimageid']); }
    if ( isset($data['instructorprofile']) ) { $this->set('instructorprofile', $data['instructorprofile']); }
    if ( isset($data['priceplan']) ) { $this->set('priceplan', $data['priceplan']); }
    if ( isset($data['price']) ) { $this->set('price', $data['price']); }
    if ( isset($data['status']) ) { $this->set('status', $data['status']); }

    return $this->where('id', $id)->update();
  }

}
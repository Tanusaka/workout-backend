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

  private static $authrolename;
  private static $authuserid;


  public function __construct($authrolename='', $authuserid=0) {
    parent::__construct();
    self::$authrolename = $authrolename;
    self::$authuserid = $authuserid;
  }

  protected function setStatus(array $data)
  {   
    $data['data']['status'] = 'I';
    return $data;
  }

  public function getCourses()
  {
    try {

      if ( self::$authrolename  == 'Trainer' ) {
        $where = "courses.tenantid='1' AND courses.instructorprofile='".self::$authuserid."' AND (courses.status='A' OR courses.status='I')";
      } elseif ( self::$authrolename == 'Administrator' || self::$authrolename == 'Super Administrator') {
        $where = "courses.tenantid='1' AND (courses.status='A' OR courses.status='I')";
      } else {
        $where = "courses.tenantid='1' AND courses.status='A'";
      }

      $courses = 
      $this->db->table('courses')->select('courses.id, courses.tenantid, courses.coursename, courses.courseintro, 
      courses.coursedescription, courses.courselevel, courses.coursetype, _files.type, _files.path AS courseimage, 
      courses.instructorprofile, courses.priceplan, courses.price, courses.currencycode, courses.status,
      courses.createdat, courses.createdby, courses.updatedat, courses.updatedby')
      ->join('_files', '_files.id = courses.courseimageid', 'left')->where($where);

      return $courses->get()->getResultArray();

    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }  
  }

  public function getCourse($id=0)
  {
    try {

      if ( self::$authrolename  == 'Trainer' ) {
        $where = "courses.tenantid='1' AND courses.id='".$id."' AND courses.instructorprofile='".self::$authuserid."' AND (courses.status='A' OR courses.status='I')";
      } elseif ( self::$authrolename == 'Administrator' || self::$authrolename == 'Super Administrator') {
        $where = "courses.tenantid='1' AND courses.id='".$id."' AND (courses.status='A' OR courses.status='I')";
      } else {
        $where = "courses.tenantid='1' AND courses.id='".$id."' AND courses.status='A'";
      }

      $course = 
      $this->db->table('courses')->select('courses.id, courses.tenantid, courses.coursename, courses.courseintro, 
      courses.coursedescription, courses.courselevel, courses.coursetype, _files.type, _files.path AS courseimage, 
      courses.instructorprofile, courses.priceplan, courses.price, courses.currencycode, courses.status,
      courses.createdat, courses.createdby, courses.updatedat, courses.updatedby')
      ->join('_files', '_files.id = courses.courseimageid', 'left')->where($where);

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
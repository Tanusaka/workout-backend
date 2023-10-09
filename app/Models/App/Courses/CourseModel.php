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
    $selectColumns = ['id','tenantid','coursetype','coursename','courseintro', 'coursedescription', 'coursemediapath', 'priceplan','price','status'];

    if ($status=='') {
      return $this->select($selectColumns)->findAll();
    } else {
      return $this->select($selectColumns)->where('status', $status)->findAll();
    }    
  }

  public function getCourse($id=0, $format='ALL')
  {
    try {
      $selectColumns = ['id','tenantid','coursetype','coursename','courseintro', 'coursedescription', 'coursemediapath','status', 'createdby', 'createdat'];
  
      $course = $this->select($selectColumns)->where('id', $id)->first();

      if ( !isset($course) ) {
        return null;
      }

      if ( $format=='COURSE_ONLY' ) {
        return $course;
      }

      $sections = $this->db->table('course_sections')->select(['id', 'courseid', 'sectionname','status'])
      ->where('courseid', $course['id'])->where('status', 'A')
      ->orderBy('id', 'ASC')->get()->getResultArray();

      $allSections = [];

      foreach ($sections as $section) {
        
        $section['lessons'] = $this->db->table('course_lessons')->select(['id', 'sectionid', 'lessonname', 'lessonmediapath', 'lessondescription', 'lessonduration', 'status'])
        ->where('sectionid', $section['id'])->where('status', 'A')->get()->getResultArray();
        
        array_push($allSections, $section);
      }
      
      $course['sections'] = $allSections;

      $course['follower_count'] = $this->db->table('course_followers')->where(["courseid" => $course['id']])->countAllResults();
      $course['review_count'] = $this->db->table('course_reviews')->where(["courseid" => $course['id']])->countAllResults();

      return $course;

    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  public function save_course($data=[])
  {
    return is_null($data) ? false : ( $this->insert($data) ? true : false );
  }

  public function update_course($data=[], $id=null)
  {
    if ( is_null($data) ) { return false; }

    if ( isset($data['coursetype']) ) { $this->set('coursetype', $data['coursetype']); }
    if ( isset($data['coursename']) ) { $this->set('coursename', $data['coursename']); }
    if ( isset($data['courseintro']) ) { $this->set('courseintro', $data['courseintro']); }
    if ( isset($data['coursedescription']) ) { $this->set('coursedescription', $data['coursedescription']); }
    if ( isset($data['coursemediapath']) ) { $this->set('coursemediapath', $data['coursemediapath']); }
    if ( isset($data['status']) ) { $this->set('status', $data['status']); }

    return $this->where('id', $id)->update();
  }

}
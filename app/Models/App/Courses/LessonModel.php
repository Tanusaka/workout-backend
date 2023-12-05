<?php
/**
 *
 * @author Samu
 */
namespace App\Models\App\Courses;

use CodeIgniter\Model;

class LessonModel extends Model
{

  protected $table      = 'course_lessons';
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

  public function getLessons($sectionid=0, $status='')
  {
      try {

        $lessons = 
        $this->db->table('course_lessons')->select('course_lessons.id, course_lessons.courseid, course_lessons.sectionid, course_lessons.lessonname, course_lessons.lessonduration, course_lessons.lessondescription, 
        _files.type, CONCAT(_files.path, _files.name) AS lessonmedia, course_lessons.lessonorder, course_lessons.status,
        course_lessons.createdat, course_lessons.createdby, course_lessons.updatedat, course_lessons.updatedby')
        ->join('_files', '_files.id = course_lessons.lessonmediaid', 'left')
        ->where('course_lessons.sectionid', $sectionid);
        
        if ($status!='') {
          $lessons->where('course_lessons.status', $status);
        }

        $lessons->orderBy('lessonorder', 'ASC');
  
        return $lessons->get()->getResultArray();
  
      } catch (\Exception $e) {
        throw new \Exception($e->getMessage());
      } 
  }

  public function getLesson($id=0)
  {
      try {

        $lesson = 
        $this->db->table('course_lessons')->select('course_lessons.id, course_lessons.courseid, course_lessons.sectionid, course_lessons.lessonname, course_lessons.lessonduration, course_lessons.lessondescription, 
        _files.type, CONCAT(_files.path, _files.name) AS lessonmedia, course_lessons.lessonorder, course_lessons.status,
        course_lessons.createdat, course_lessons.createdby, course_lessons.updatedat, course_lessons.updatedby')
        ->join('_files', '_files.id = course_lessons.lessonmediaid', 'left')
        ->where('course_lessons.id', $id);
  
        return $lesson->get()->getRowArray();
  
      } catch (\Exception $e) {
        throw new \Exception($e->getMessage());
      } 
  }

  public function getNextLesson($courseid=0, $nextLessonIndex=0)
  {
      try {
        $lesson = 
        $this->db->table('course_lessons')->select('course_lessons.id, course_lessons.courseid, course_lessons.sectionid, course_lessons.lessonname, course_lessons.lessonduration, course_lessons.lessondescription, 
        _files.type, CONCAT(_files.path, _files.name) AS lessonmedia, course_lessons.lessonorder, course_lessons.status,
        course_lessons.createdat, course_lessons.createdby, course_lessons.updatedat, course_lessons.updatedby')
        ->join('_files', '_files.id = course_lessons.lessonmediaid', 'left')
        ->where('course_lessons.courseid', $courseid)
        ->where('course_lessons.lessonorder', $nextLessonIndex);

        return $lesson->get()->getRowArray();

      } catch (\Exception $e) {
        throw new \Exception($e->getMessage());
      } 
  }

  public function getPreviousLesson($courseid=0, $previousLessonIndex=0)
  {
      try {
        $lesson = 
        $this->db->table('course_lessons')->select('course_lessons.id, course_lessons.courseid, course_lessons.sectionid, course_lessons.lessonname, course_lessons.lessonduration, course_lessons.lessondescription, 
        _files.type, CONCAT(_files.path, _files.name) AS lessonmedia, course_lessons.lessonorder, course_lessons.status,
        course_lessons.createdat, course_lessons.createdby, course_lessons.updatedat, course_lessons.updatedby')
        ->join('_files', '_files.id = course_lessons.lessonmediaid', 'left')
        ->where('course_lessons.courseid', $courseid)
        ->where('course_lessons.lessonorder', $previousLessonIndex);

        return $lesson->get()->getRowArray();

      } catch (\Exception $e) {
        throw new \Exception($e->getMessage());
      }  
  }

  public function getLessonOrderID($sectionid=0) {
    
    $lastLesson = $this->where('sectionid', $sectionid)->orderBy('lessonorder', 'DESC')->limit(1)->get()->getRowArray();
    
    if (!empty($lastLesson)) {
      return $lastLesson['lessonorder']+1;
    }

    return 1;
  }

  public function saveLesson($data=[])
  {
    return is_null($data) ? false : ( $this->insert($data) ? true : false );
  }

  public function updateLesson($data=[], $id=null)
  {
    if ( is_null($data) ) { return false; }

    if ( isset($data['lessonname']) ) { $this->set('lessonname', $data['lessonname']); }
    if ( isset($data['lessonduration']) ) { $this->set('lessonduration', $data['lessonduration']); }
    if ( isset($data['lessondescription']) ) { $this->set('lessondescription', $data['lessondescription']); }
    if ( isset($data['lessonmediaid']) ) { $this->set('lessonmediaid', $data['lessonmediaid']); }
    if ( isset($data['lessonorder']) ) { $this->set('lessonorder', $data['lessonorder']); }
    if ( isset($data['status']) ) { $this->set('status', $data['status']); }

    return $this->where('id', $id)->update();
  }

  public function deleteLessonsBySection($id=null)
  {
      return $this->where('sectionid', $id)->delete();
  }

}
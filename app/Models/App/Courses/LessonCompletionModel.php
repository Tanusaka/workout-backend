<?php
/**
 *
 * @author Tanu
 */
namespace App\Models\App\Courses;

use CodeIgniter\Model;

class LessonCompletionModel extends Model
{

  protected $table      = 'lesson_completion';
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
  protected $beforeInsert         = [];
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

  public function getLesson($id=0)
  {
    try {
      $selectColumns = ['id', 'sectionid', 'lessonname', 'lessonmediapath', 'lessondescription', 'lessonduration', 'status'];
      return $this->select($selectColumns)->where('id', $id)->first();
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  public function saveLessonDuration($data=[])
  {
    return is_null($data) ? false : ( $this->insert($data) ? true : false );
  }

  public function updateLessonDuration($data=[], $id=null)
  {
    if ( is_null($data) ) { return false; }

    if ( isset($data['lessonname']) ) { $this->set('lessonname', $data['lessonname']); }
    if ( isset($data['lessonmediapath']) ) { $this->set('lessonmediapath', $data['lessonmediapath']); }
    if ( isset($data['lessondescription']) ) { $this->set('lessondescription', $data['lessondescription']); }
    if ( isset($data['lessonduration']) ) { $this->set('lessonduration', $data['lessonduration']); }
    if ( isset($data['status']) ) { $this->set('status', $data['status']); }

    return $this->where('id', $id)->update();
  }

  public function deleteLessonsBySection($id=null)
  {
      return $this->where('sectionid', $id)->delete();
  }

}

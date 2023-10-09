<?php
/**
 *
 * @author Tanu
 */
namespace App\Models\App\Courses;

use CodeIgniter\Model;

class LessonDurationModel extends Model
{

  protected $table      = 'lesson_duration';
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

  public function getLessonDuration($user_id=0,$lessonid=0)
  {
    try {
      $selectColumns = ['id', 'lessonid', 'userid', 'duration', 'completed'];
      return $this->select($selectColumns)->where('userid', $user_id)->where('lessonid', $lessonid)->first();
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

    $this->set('duration', $data['duration']+1);

    return $this->where('id', $id)->update();
  }

  public function updateLessonDurationCompletion($data=[], $id=null)
  {
    if ( is_null($data) ) { return false; }

    $this->set('completed', $data['completed']);

    return $this->where('id', $id)->update();
  }

}

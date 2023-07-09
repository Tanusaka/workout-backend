<?php
/**
 *
 * @author Samu
 */
namespace App\Models\App\Courses;

use CodeIgniter\Model;

class SectionModel extends Model
{

  protected $table      = 'course_sections';
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

  public function getSection($id=0)
  {
    try {
      $selectColumns = ['id', 'courseid', 'sectionname',' status'];
      return $this->select($selectColumns)->where('id', $id)->first();
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  public function save_section($data=[])
  {
    return is_null($data) ? false : ( $this->insert($data) ? true : false );
  }

  public function update_section($data=[], $id=null)
  {
    if ( is_null($data) ) { return false; }

    if ( isset($data['sectionname']) ) { $this->set('sectionname', $data['sectionname']); }
    if ( isset($data['status']) ) { $this->set('status', $data['status']); }

    return $this->where('id', $id)->update();
  }

}
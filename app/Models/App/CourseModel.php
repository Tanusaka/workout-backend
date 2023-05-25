<?php
/**
 *
 * @author Samu
 */
namespace App\Models\App;

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
    $selectColumns = ['id','prid','type','title','subtitle','level','description', 'covermediatype', 'covermedia','status'];

    if ($status=='') {
      return $this->select($selectColumns)->findAll();
    } else {
      return $this->select($selectColumns)->where('status', $status)->findAll();
    }    
  }

  public function getCourse($id=0, $format='ALL')
  {
    try {
      $selectColumns = ['id','prid','type','title','subtitle','level','description', 'covermediatype', 'covermedia','status'];
  
      $course = $this->select($selectColumns)->where('id', $id)->first();

      if ( !isset($course) ) {
        return null;
      }

      if ( $format=='COURSE_ONLY' ) {
        return $course;
      }

      $sections = $this->db->table('sections')->select(['id','title','status'])
      ->where('courseid', $course['id'])->where('status', 'A')
      ->orderBy('id', 'ASC')->get()->getResultArray();

      $allSections = [];

      foreach ($sections as $section) {
        
        $section['contents'] = $this->db->table('contents')->select(['id','type','title','contentmedia','status'])
        ->where('sectionid', $section['id'])->where('status', 'A')->get()->getResultArray();
        
        array_push($allSections, $section);
      }
      
      $course['sections'] = $allSections;

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

    if ( isset($data['type']) ) { $this->set('type', $data['type']); }
    if ( isset($data['title']) ) { $this->set('title', $data['title']); }
    if ( isset($data['subtitle']) ) { $this->set('subtitle', $data['subtitle']); }
    if ( isset($data['level']) ) { $this->set('level', $data['level']); }
    if ( isset($data['description']) ) { $this->set('description', $data['description']); }
    if ( isset($data['covermediatype']) ) { $this->set('covermediatype', $data['covermediatype']); }
    if ( isset($data['covermedia']) ) { $this->set('covermedia', $data['covermedia']); }
    if ( isset($data['status']) ) { $this->set('status', $data['status']); }

    return $this->where('id', $id)->update();
  }

}
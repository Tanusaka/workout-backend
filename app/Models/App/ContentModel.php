<?php
/**
 *
 * @author Samu
 */
namespace App\Models\App;

use CodeIgniter\Model;

class ContentModel extends Model
{

  protected $table      = 'contents';
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

  public function getContent($id=0)
  {
    try {
      $selectColumns = ['id','sectionid','type','title','contentmedia','duration','status'];
      return $this->select($selectColumns)->where('id', $id)->first();
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  public function save_content($data=[])
  {
    return is_null($data) ? false : ( $this->insert($data) ? true : false );
  }

  public function update_content($data=[], $id=null)
  {
    if ( is_null($data) ) { return false; }

    if ( isset($data['sectionid']) ) { $this->set('sectionid', $data['sectionid']); }
    if ( isset($data['type']) ) { $this->set('type', $data['type']); }
    if ( isset($data['title']) ) { $this->set('title', $data['title']); }
    if ( isset($data['contentmedia']) ) { $this->set('contentmedia', $data['contentmedia']); }
    if ( isset($data['duration']) ) { $this->set('duration', $data['duration']); }
    if ( isset($data['status']) ) { $this->set('status', $data['status']); }

    return $this->where('id', $id)->update();
  }

}
<?php
/**
 *
 * @author Samu
 */
namespace App\Models\App\Media;

use CodeIgniter\Model;

class MediaModel extends Model
{

    protected $table      = 'media';
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

    public function getMedia($filters=[])
    {
        $query = $this->select();
        
        if (isset($filters) && !empty($filters)) {
            foreach ($filters as $key => $value) {
                if ($value!='') {
                    $query->where($key, $value);
                }
            }
        }

        return $query->get()->getResultArray();
    }

    public function saveMedia($data=[])
    {
        return is_null($data) ? false : ( $this->insert($data) ? true : false );
    }

}
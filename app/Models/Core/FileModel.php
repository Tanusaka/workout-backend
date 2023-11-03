<?php
/**
 *
 * @author Samu
 */
namespace App\Models\Core;

use App\Models\Core\BaseModel;

class FileModel extends BaseModel
{

    protected $table      = '_files';
    protected $primaryKey = 'id';

    protected $protectFields    = false;

    // Callbacks
    protected $allowCallbacks       = true;
    protected $beforeInsert         = ['setBeforeInsert'];
    // protected $afterInsert          = [];
    // protected $beforeUpdate         = [];
    // protected $afterUpdate          = [];
    // protected $beforeFind           = [];
    // protected $afterFind            = [];
    // protected $beforeDelete         = [];
    // protected $afterDelete          = [];


    protected function setBeforeInsert(array $data)
    {   
        $data['data']['status'] = 'A';
        $data['data']['createdby'] = $this->currentuser;
        return $data;
    }

    public function getFiles($filters=[])
    {
        try {
            $files = 
            $this->db->table('_files')->select('id, tenantid, rootdir, path, name, type, ext, size, 
            displayname, filedescription, sharelink, download, accesslevel, status, 
            createdat, createdby, updatedat, updatedby')
            ->where('tenantid', 1);

            if (isset($filters) && !empty($filters)) {
                foreach ($filters as $key => $value) {
                    if ($value!='') {
                        $files->where($key, $value);
                    }
                }
            }
        
            return $files->get()->getResultArray();
    
        } catch (\Exception $e) {
        throw new \Exception($e->getMessage());
        }  
    }

    public function getFile($id=0)
    {
        try {
            $file = 
            $this->db->table('_files')->select('id, tenantid, rootdir, path, name, type, ext, size, 
            displayname, filedescription, sharelink, download, accesslevel, status, 
            createdat, createdby, updatedat, updatedby')
            ->where('id', $id);
        
            return $file->get()->getRowArray();
    
        } catch (\Exception $e) {
        throw new \Exception($e->getMessage());
        }  
    }

    public function saveFile($data=[])
    {
        return is_null($data) ? 0 : ( $this->insert($data) ? $this->getInsertID() : 0 );
    }

}
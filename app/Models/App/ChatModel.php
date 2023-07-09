<?php
/**
 *
 * @author Samu
 */
namespace App\Models\App;

use CodeIgniter\Model;

class ChatModel extends Model
{
    protected $table      = 'chats';
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

    public function save_chat($data=[])
    {
        return is_null($data) ? false : ( $this->insert($data) ? true : false );
    }
}
<?php
/**
 *
 * @author Samu
 */
namespace App\Models\App\Chats;

use CodeIgniter\Model;

class ChatmemberModel extends Model
{

    protected $table = 'chat_members';
    protected $primaryKey = 'id';
    
    protected $protectFields    = false;

    
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

    public function getChatMembers($chatid=0)
    {
        try {

            $chatmembers = 
            $this->db->table('chat_members')->select('chat_members.id, chat_members.role, _users.firstname, _users.lastname, 
            CONCAT(_files.path, _files.name) AS profileimage, _users.islogged AS active, chat_members.status, chat_members.createdat')
            ->join('_users', '_users.id = chat_members.userid')
            ->join('_files', '_files.id = _users.profileimageid', 'left')
            ->where('chat_members.chatid', $chatid);

            
            return $chatmembers->get()->getResultArray();
      
        } catch (\Exception $e) {
        throw new \Exception($e->getMessage());
        }       
    }

    public function getChatMember($chatid=0, $userid=0)
    {
        try {

            $chat = 
            $this->db->table('chat_members')->select()
            ->where('chat_members.chatid', $chatid)
            ->where('chat_members.userid', $userid);
      
            return $chat->get()->getRowArray();
      
        } catch (\Exception $e) {
        throw new \Exception($e->getMessage());
        }       
    }

    public function isMember($chatid=0, $userid=0) {
        
        if (!empty($this->getChatMember($chatid, $userid))) {
            return true;
        } 

        return false;
    }

    public function saveChatMember($data=[])
    {
        return is_null($data) ? false : ( $this->insertBatch($data) ? true : false );
    }

}

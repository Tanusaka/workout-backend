<?php
/**
 *
 * @author Samu
 */
namespace App\Models\App\Chats;

use CodeIgniter\Model;

class ChatModel extends Model
{

    protected $table = 'chats';
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

    public function getChats($userid=0, $status='')
    {
        try {

            $chats = 
            $this->db->table('chats')->select('chats.id, chats.type, chat_info.name, chat_info.about, CONCAT(_files.path, _files.name) AS chatimage,
            chats.status, chats.createdat, chats.createdby, chats.updatedat, chats.updatedby')
            ->join('chat_members', 'chat_members.chatid = chats.id', 'left')
            ->join('chat_info', 'chat_info.chatid = chats.id', 'left')
            ->join('_files', '_files.id = chat_info.chatimageid', 'left')
            ->where('chats.tenantid', 1)
            ->where('chat_members.userid', $userid);

            
            if ($status!='') {
              $chats->where('chats.status', $status);
            }
      
            return $chats->get()->getResultArray();
      
        } catch (\Exception $e) {
        throw new \Exception($e->getMessage());
        }       
    }

    public function getChat($chatid=0)
    {
        try {

            $chat = 
            $this->db->table('chats')->select('chats.id, chats.type, chat_info.name, chat_info.about, CONCAT(_files.path, _files.name) AS chatimage,
            chats.status, chats.createdat, chats.createdby, chats.updatedat, chats.updatedby')
            ->join('chat_info', 'chat_info.chatid = chats.id', 'left')
            ->join('_files', '_files.id = chat_info.chatimageid', 'left')
            ->where('chats.tenantid', 1)
            ->where('chats.id', $chatid);
      
            return $chat->get()->getRowArray();
      
        } catch (\Exception $e) {
        throw new \Exception($e->getMessage());
        }       
    }

    public function hasPersonalChat($tenantid=0, $userid=0, $memberid=0)
    {
        try {

            $chat = 
            $this->db->table('chats')->select()
            ->join('chat_members', 'chat_members.chatid = chats.id')
            ->where('chats.tenantid', 1)
            ->where('chats.type', 'Personal')
            ->where('chats.status', 'A')
            ->where('chat_members.userid', $userid)
            ->where('chat_members.connid', $memberid)->limit(1)
			->get()->getRowArray();
            
            if (isset($chat) && !empty($chat)) {
                return true;
            }

            return false;
      
            return;
        } catch (\Exception $e) {
        throw new \Exception($e->getMessage());
        }       
    }

    public function saveChat($data=[])
    {
        return is_null($data) ? 0 : ( $this->insert($data) ? $this->getInsertID() : 0 );
    }

    // public function send($sender_id, $receiver_id, $message_text) {
    //     $data = [
    //         'sender_id' => $sender_id,
    //         'receiver_id' => $receiver_id,
    //         'message_text' => $message_text,
    //         'timestamp' => date('Y-m-d H:i:s')
    //     ];

    //     $this->insert($data);

    //     return $this->insertID();
    // }

    // public function retrieve($user_id, $other_user_id, $limit, $offset) {
    //     $builder = $this->table('messages');
    //     $builder->where('sender_id', $user_id)->where('receiver_id', $other_user_id)->orWhere('sender_id', $other_user_id)->where('receiver_id', $user_id);
    //     $builder->orderBy('timestamp', 'DESC');
    //     $builder->limit($limit, $offset);

    //     return $builder->get()->getResult();
    // }

    // public function retrieveChats($user_id) {
    //     $queryString = 'SELECT linked_profiles.linkedprofileid, _users.email, _users.firstname, _users.lastname, MAX(chats.message_id) as message_id, chats.sender_id, chats.receiver_id, chats.message_text, chats.timestamp FROM linked_profiles
    //     LEFT JOIN chats ON (linked_profiles.linkedprofileid = chats.sender_id AND chats.receiver_id = '.$user_id.') OR (linked_profiles.linkedprofileid = chats.receiver_id AND chats.sender_id = '.$user_id.' ) 
    //     JOIN _users ON linked_profiles.linkedprofileid = _users.id
    //     WHERE linked_profiles.userid = '.$user_id.'
    //     GROUP BY linked_profiles.linkedprofileid';
        
    //     return $this->db->query($queryString)->getResultArray();

    // }
}

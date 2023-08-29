<?php
/**
 *
 * @author Samu
 */
namespace App\Models\App;

use CodeIgniter\Model;

class ChatModel extends Model
{

    protected $table = 'chats';
    protected $primaryKey = 'message_id';
    protected $allowedFields = ['sender_id', 'receiver_id', 'message_text', 'timestamp'];

    
    protected $protectFields    = false;

    
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

    public function getChats()
    {
        $selectColumns = ['message_id','sender_id', 'receiver_id', 'message_text', 'timestamp'];
                
        return $this->select($selectColumns)->findAll();        
    }

    public function send($sender_id, $receiver_id, $message_text) {
        $data = [
            'sender_id' => $sender_id,
            'receiver_id' => $receiver_id,
            'message_text' => $message_text,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        $this->insert($data);

        return $this->insertID();
    }

    public function retrieve($user_id, $other_user_id, $limit, $offset) {
        $builder = $this->table('messages');
        $builder->where('sender_id', $user_id)->where('receiver_id', $other_user_id)->orWhere('sender_id', $other_user_id)->where('receiver_id', $user_id);
        $builder->orderBy('timestamp', 'DESC');
        $builder->limit($limit, $offset);

        return $builder->get()->getResult();
    }

    public function retrieveChats($user_id) {
        $queryString = 'SELECT chats.message_id, chats.sender_id, chats.receiver_id, chats.message_text, max(chats.timestamp) FROM linked_profiles
        LEFT JOIN chats ON linked_profiles.linkedprofileid = chats.sender_id OR linked_profiles.linkedprofileid = chats.receiver_id
        WHERE linked_profiles.userid = '.$user_id.'
        GROUP BY linked_profiles.linkedprofileid
        ORDER BY chats.timestamp';
        
        return $this->db->query($queryString)->getResultArray();

    }
}

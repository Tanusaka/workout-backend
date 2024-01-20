<?php
/**
 *
 * @author Samu
 */
namespace App\Models\App\Chats;

use CodeIgniter\Model;

class ChatmessageModel extends Model
{

    protected $table = 'chat_messages';
    protected $primaryKey = 'id';
    
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


    public function getChatMessages($chatid=0, $currentuser=0)
    {
        try {

            $select = 'chat_messages.id, chat_messages.type, chat_messages.content, 
            chat_messages.createdat, _users.firstname AS sendername, _files.path AS senderimage, 
            IF(chat_messages.createdby='.$currentuser.', "1", "0") AS sentbyme';

            $chatmessages = 
            $this->db->table('chat_messages')->select($select)
            ->join('_users', '_users.id = chat_messages.createdby')
            ->join('_files', '_files.id = _users.profileimageid', 'left')
            ->where('chat_messages.chatid', $chatid)->orderBy('chat_messages.createdat', 'ASC');

            return $chatmessages->get()->getResultArray();
      
        } catch (\Exception $e) {
        throw new \Exception($e->getMessage());
        }       
    }

    public function getChatMessage($messageid=0)
    {
        try {

            $chatmessage = 
            $this->db->table('chat_messages')->select('chat_messages.id, chat_messages.type, chat_messages.resourceid, chat_messages.content, 
            chat_messages.createdat, chat_messages.createdby, chat_messages.updatedat, chat_messages.updatedby')
            ->where('chat_messages.id', $messageid);

            return $chatmessage->get()->getRowArray();
      
        } catch (\Exception $e) {
        throw new \Exception($e->getMessage());
        }       
    }

    public function saveChatMessage($data=[])
    {
        return is_null($data) ? 0 : ( $this->insert($data) ? $this->getInsertID() : 0 );
    }

}

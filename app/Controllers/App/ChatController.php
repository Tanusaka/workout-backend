<?php
/**
 *
 * @author Samu
 */
namespace App\Controllers\App;

use App\Controllers\Core\AuthController;
use App\Models\App\ChatModel;

class ChatController extends AuthController
{
    protected $chatmodel;

    public function __construct() {
        parent::__construct();
        $this->chatmodel = new ChatModel();
    }

    public function index()
    {
	$user_id = $this->getAuthID();//$this->request->getVar('user_id');
	$messages = $this->chatmodel->retrieveChats($user_id);
	
	$response = [
	    'threads' => $messages,
	    'status' => 'success'
	];
	
	return $this->respond($response);        
    }

    public function retrieveChatThread() {
	//$user_id = $this->request->getVar('user_id');
	$user_id = $this->getAuthID();
	$other_user_id = $this->request->getVar('other_user_id');
	$limit = $this->request->getVar('limit');
	$offset = $this->request->getVar('offset');
	
	$messages = $this->chatmodel->retrieve($user_id, $other_user_id, $limit, $offset);
	
	$response = [
	    'messages' => $messages,
	    'status' => 'success'
	];
	
	return $this->respond($response);
    }

    public function get()
    {
        try {
            $id = $this->request->getVar('id');

            if ( !isset($id) ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $chat = $this->chatmodel->getChat($id);

            if ( is_null($chat) ) {
                return $this->respond($this->errorResponse(404,"Chat cannot be found."), 404);
            }

            return $this->respond($this->successResponse(200, "", $chat), 200);

        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
        }
    }

    public function save()
	{
	$sender_id = $this->getAuthID();
	//$sender_id = $this->request->getVar('sender_id');
        $receiver_id = $this->request->getVar('receiver_id');
        $message_text = $this->request->getVar('message_text');
        
        $message_id = $this->chatmodel->send($sender_id, $receiver_id, $message_text);

	$response = [
            'message_id' => $message_id,
            'timestamp' => date('Y-m-d H:i:s'),
            'status' => 'success'
        ];

	return $this->respond($response);

    }

    private function setValidationRules($type='')
    {
        if ( $type == 'save' ) {
            $this->validation->setRules([
                'prid' => [
                    'label'  => 'Primary ID',
                    'rules'  => 'required'
                ],
                'type' => [
                    'label'  => 'Chat Type',
                    'rules'  => 'required'
                ],
                'name' => [
                    'label'  => 'Chat Name',
                    'rules'  => 'required|is_unique[chats.name]'
				]
            ]);
        } else {
            $this->validation->setRules([]);
        }
    }    
}

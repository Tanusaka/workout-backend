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
        return $this->respond($this->successResponse(200, "", $this->chatmodel->getChats()), 200);
    }

    public function retrieveChatThread() {
	$user_id = $this->request->getVar('user_id');
	$other_user_id = $this->request->getVar('other_user_id');
	$limit = $this->request->getVar('limit');
	$offset = $this->request->getVar('offset');
	
	$messages = $chatmodel->retrieve($user_id, $other_user_id, $limit, $offset);
	
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
		$this->setValidationRules('save');

        if ( $this->isValid() ) {           
        
			$chat = [
                'prid'=> trim($this->request->getVar('prid')), 
                'type'=> trim($this->request->getVar('type')),
				'name'=> trim($this->request->getVar('name')),
                'about'=> trim($this->request->getVar('about')),
                'image'=> trim($this->request->getVar('image')),
			];

			if ( !$this->chatmodel->save_chat($chat) ) {
				return $this->failServerError(HTTP_500);
			}

			return $this->respond($this->getSuccessResponse(API_MSG_SUCCESS_CHAT_CREATED), 200);
        
		} else {
            return $this->failValidationErrors($this->errors);
        }
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

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
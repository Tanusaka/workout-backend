<?php
/**
 *
 * @author Samu
 */
namespace App\Controllers\App;

use App\Controllers\Core\AuthController;
use App\Models\App\ChatmessageModel;

class ChatmessageController extends AuthController
{
    protected $chatmessagemodel;

    public function __construct() {
        parent::__construct();
        $this->chatmessagemodel = new ChatmessageModel();
    }

    public function save()
	{
		$this->setValidationRules('save');

        if ( $this->isValid() ) {           
        
			$chat = [
                'chatid'=> trim($this->request->getVar('chatid')), 
                'userid'=> trim($this->request->getVar('userid'))
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
                'chatid' => [
                    'label'  => 'Chat ID',
                    'rules'  => 'required'
                ],
                'userid' => [
                    'label'  => 'User ID',
                    'rules'  => 'required'
                ],
            ]);
        } else {
            $this->validation->setRules([]);
        }
    }  
}
<?php
/**
 *
 * @author Samu
 */
namespace App\Controllers\App\Chats;

use App\Controllers\Core\ConnectionController;
use App\Models\App\Chats\ChatModel;
use App\Models\App\Chats\ChatmemberModel;
use App\Models\App\Chats\ChatmessageModel;


class ChatController extends ConnectionController
{
    protected $chatmodel;
    protected $chatmembermodel;
    protected $chatmessagemodel;

    public function __construct() {
        parent::__construct();
        $this->chatmodel = new ChatModel();
        $this->chatmembermodel = new ChatmemberModel();
        $this->chatmessagemodel = new ChatmessageModel();
    }

    public function index()
    {

        $chats = $this->chatmodel->getChats($this->getAuthID(), 'A');

        $allChats = [];

        foreach ($chats as $chat) {
            
            if ($chat['type']=='Personal') {
                $currentmember = $this->chatmembermodel->getChatMember($chat['id'], $this->getAuthID());
                $connmember = $this->usermodel->getUser($currentmember['connid']);

                $chat['name'] = $connmember['firstname'].' '.$connmember['lastname'];
                $chat['chatimage'] = $connmember['profileimage'];
                $chat['about'] = $connmember['rolename'];
                $chat['active'] = $connmember['active'];
            } 
            
            array_push($allChats, $chat);
        }

        return $this->respond($this->successResponse(200, "", $allChats), 200);
    }

    public function get()
    {
        try {
            $chatid = $this->request->getVar('id');

            if ( !isset($chatid) ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            if ( !$this->chatmembermodel->isMember($chatid, $this->getAuthID()) ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $chat = $this->chatmodel->getChat($chatid);

            if (empty($chat)) {
                return $this->respond($this->errorResponse(404,"Chat cannot be found."), 404);
            }

            if ($chat['type']=='Personal') {
                $currentmember = $this->chatmembermodel->getChatMember($chat['id'], $this->getAuthID());
                $connmember = $this->usermodel->getUser($currentmember['connid']);

                $chat['name'] = $connmember['firstname'].' '.$connmember['lastname'];
                $chat['chatimage'] = $connmember['profileimage'];
                $chat['about'] = $connmember['rolename'];
                $chat['active'] = $connmember['active'];
            } 

            $chat['members'] = $this->chatmembermodel->getChatMembers($chatid);
            $chat['messages'] = $this->chatmessagemodel->getChatMessages($chatid, $this->getAuthID());

            return $this->respond($this->successResponse(200, "", $chat), 200);

        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
        }
    }

    public function getPersonalChatConnections()
    {
        try {

            $connections = $this->connectionmodel->getUserConnectionsForChat($this->getAuthID());

            return $this->respond($this->successResponse(200, "", $connections), 200);

        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
        }
    }

    public function savePersonalChat()
	{
		$this->setValidationRules('save_personal_chat');

        if ( $this->isValid() ) {           
        
            $userid  = trim($this->request->getVar('userid'));

            $member = $this->usermodel->getUser($userid);

            if ( empty($member) ) {
                return $this->respond($this->errorResponse(404,"Member cannot be found."), 404);
            }

            if ( $this->chatmodel->hasPersonalChat(1, $this->getAuthID(), $userid) ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            if ( !$this->connectionmodel->hasConnection(1, $this->getAuthID(), $userid) ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

			$chat = [
                'tenantid'=> 1, //get this from token email
				'type'=> 'Personal',
			];

            $chatid = $this->chatmodel->saveChat($chat);
			
            if ( $chatid==0 ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            $chatmembers = [
                [
                    'chatid'=> $chatid, 
                    'userid'=> $this->getAuthID(),
                    'connid'=> $userid,
                    'role'=> 'Admin'
                ],
                [
                    'chatid'=> $chatid, 
                    'userid'=> $userid,
                    'connid'=> $this->getAuthID(),
                    'role'=> 'Member'
                ],
            ];

            
            if ( !$this->chatmembermodel->saveChatMember($chatmembers)) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}
            
            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_CHAT_CREATED, 
            ['chat'=>$this->chatmodel->getChat($chatid)]), 200);
        
		} else {
            return $this->respond($this->errorResponse(400,$this->errors), 400);
        }
	}

    public function savePersonalChatMessage()
	{
		$this->setValidationRules('save_personal_chat_message');

        if ( $this->isValid() ) {           
        
            $chatid  = trim($this->request->getVar('chatid'));

            if ( !$this->chatmembermodel->isMember($chatid, $this->getAuthID()) ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

			$chatmessage = [
                'chatid'=> $chatid,
				'type'=>  trim($this->request->getVar('type')),
                'resourceid'=>  0,
                'content' => trim($this->request->getVar('message')),
                'createdby' => $this->getAuthID()
			];

            $messageid = $this->chatmessagemodel->saveChatMessage($chatmessage);
			
            if ( $messageid==0 ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}
            
            return $this->respond($this->successResponse(200, "", 
            ['message'=>$this->chatmessagemodel->getChatMessage($messageid)]), 200);
        
		} else {
            return $this->respond($this->errorResponse(400,$this->errors), 400);
        }
	}

    public function deletePersonalChat() {

        $this->setValidationRules('delete_personal_chat');

        if ( $this->isValid() ) {           
        
            // $id = trim($this->request->getVar('id'));

            // $primaryconnection = $this->connectionmodel->find($id);

            // if ( empty($primaryconnection) ) {
            //     return $this->respond($this->errorResponse(404,"Connection cannot be found."), 404);
            // }

            // $secondaryconnection = $this->connectionmodel->getUserConnection(
            //     $primaryconnection['tenantid'], 
            //     $primaryconnection['connid'],
            //     $primaryconnection['userid']
            // );

            // $connections = [];

            // array_push($connections, $primaryconnection['id']);

            // if (!empty($secondaryconnection)) {
            //     array_push($connections, $secondaryconnection['id']);
            // }

			// if ( !$this->connectionmodel->deleteConnections($connections) ) {
			// 	return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			// }

            // return $this->respond($this->successResponse(200, API_MSG_SUCCESS_CONNECTION_DELETED, 
            // ['connections'=>$this->connectionmodel->getUserConnections($primaryconnection['userid'])]), 200);
        
		} else {
            return $this->respond($this->errorResponse(400,$this->errors), 400);
        }
    }

    // public function retrieveChatThread() {
	// //$user_id = $this->request->getVar('user_id');
	// $user_id = $this->getAuthID();
	// $other_user_id = $this->request->getVar('other_user_id');
	// $limit = $this->request->getVar('limit');
	// $offset = $this->request->getVar('offset');
	
	// $messages = $this->chatmodel->retrieve($user_id, $other_user_id, $limit, $offset);
	
	// $response = [
	//     'messages' => $messages,
	//     'status' => 'success'
	// ];
	
	// return $this->respond($response);
    // }

    // public function get()
    // {
    //     try {
    //         $id = $this->request->getVar('id');

    //         if ( !isset($id) ) {
    //             return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
    //         }

    //         $chat = $this->chatmodel->getChat($id);

    //         if ( is_null($chat) ) {
    //             return $this->respond($this->errorResponse(404,"Chat cannot be found."), 404);
    //         }

    //         return $this->respond($this->successResponse(200, "", $chat), 200);

    //     } catch (\Exception $e) {
    //         log_message('error', '[ERROR] {exception}', ['exception' => $e]);
    //         return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
    //     }
    // }

    // public function save()
	// {
	// $sender_id = $this->getAuthID();
	// //$sender_id = $this->request->getVar('sender_id');
    //     $receiver_id = $this->request->getVar('receiver_id');
    //     $message_text = $this->request->getVar('message_text');
        
    //     $message_id = $this->chatmodel->send($sender_id, $receiver_id, $message_text);

	// $response = [
    //         'message_id' => $message_id,
    //         'timestamp' => date('Y-m-d H:i:s'),
    //         'status' => 'success'
    //     ];

	// return $this->respond($response);

    // }

    private function setValidationRules($type='')
    {
        if ( $type == 'save_personal_chat' ) {
            $this->validation->setRules([
                'userid' => [
                    'label'  => 'Chat Member',
                    'rules'  => 'required'
                ],
            ]);
        } elseif ( $type == 'save_personal_chat_message' ) {
            $this->validation->setRules([
                'chatid' => [
                    'label'  => 'Chat ID',
                    'rules'  => 'required'
                ],
                'type' => [
                    'label'  => 'Message Type',
                    'rules'  => 'required'
                ],
                'message' => [
                    'label'  => 'Message',
                    'rules'  => 'required'
                ],
            ]);
        } elseif ( $type == 'delete_personal_chat' ) {
            $this->validation->setRules([
                'chatid' => [
                    'label'  => 'Chat ID',
                    'rules'  => 'required'
                ]
            ]);
        } else {
            $this->validation->setRules([]);
        }
    }    
}

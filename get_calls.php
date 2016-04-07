<?php
require_once ('./Ocean_rpc_functions.php');

class OceanCDR extends OceanHandlerModule {
    public $incall;
    public $tags;
    public $settings;
    public $defaultDepartmentId = 'default';
    public $systemUserId;

    public $conversation;
    public $messageGroup;
    public $agent;
    public $user;


    public function getCalls($date_from, $date_inc = 864000) {

        $url = 'https://pbx000056.air-sip.com/?_action=cdr_extended&_login=b5f311ea7dd598ea30fad48dfba991697ca980f5&' .
            '_secret=44eb82c1115186bf6ee4a2047e794bb59a92d49d&date_from='
            . (string)$date_from . '&date_to=' . (string)($date_from + $date_inc);

        $defaults = array(
            CURLOPT_URL => $url,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_TIMEOUT => 138
        );
        $ch = curl_init();
        curl_setopt_array($ch, $defaults);
        if( ! $result = curl_exec($ch))
        {
            throw new Exception(curl_error($ch));
        }
        curl_close($ch);
        $response = json_decode($result);
        $data = $response->data;

        return $data;
    }

    public function processCalls($calls) {
        if (is_array($calls)) {

            $this->systemUserId = $this->getSystemUserId();
            $conversationId = 0;

            foreach ($calls as $incall) {
                try {
                    $conversationId = $this->getConversationIdByCallId($incall->uniqueid);
                } catch (Exception $e) {

                    // Check if user is new
                    $userid = $this->getUserByPhone($incall->caller);

                    if (!empty($userid)) {
                        $this->user = new La_Model_User_Visitor();
                        $this->user->setId($userid);
                    } else {
                        $this->user = $this->createUserByPhone($incall->caller);
                    }
                    var_dump($this->user->getId());die('**--*');
                    // Create a conversation and new message
                    $this->conversation = $this->createConversation((string)$incall->uniqueid, $incall->calldate);
                    $this->messageGroup = $this->createMessageGroup(
                        $this->user->getId(), $this->conversation->getId()
                    );
                    $this->addMessageExtraInfo($this->user->getId(), 'From: ' . $incall->caller);
                    $this->addMessageExtraInfo($this->user->getId(), 'To: ' . $incall->called_number);
                    $this->addMessageExtraInfo($this->user->getId(), 'Date: ' . $incall->calldate);

                    // If was answered by agent, assign conversation to him and update statictics
                    if ($incall->callee != '') {
                        $this->agent = $this->getAgentByPhone($incall->callee);
                        $agentAttendant = new La_Model_Conversation_Attendant_Agent($this->conversation->getDepartmentId(), $this->agent->getId());
                        $this->conversation->changeAttendant($agentAttendant);

                        $this->createConversationUserReport();
                        $workReport = La_WorkReport_WorkReport::get($this->agent->getId(), $this->conversation->getId());
                        La_WorkReport_UserSlotReport::process($this->defaultDepartmentId, $this->agent->getId());
                        $workReport->addCall();

                        $this->conversation->removeTag($this->settings['tags']['Ocean_TAG_ANSWERED'], $this->systemUserId);
                        $this->conversation->changeState(La_Model_Conversation_State::RNEW);
                    }

                    // Add recorded file to the conversation
                    switch ($incall->status) {
                        case 'ANSWER':
                            if ($incall->recording != '') {
                                $this->addMessageExtraInfo($this->conversation->getOwner()->getId(),
                                    'Conversation was recorded'
                                );

                                $voiceData = $this->getVoiceData($incall->recording, $incall->duration);

                                $this->addMessageExtraInfo($this->messageGroup->getUserId(),
                                    'Duration: ' . $incall->duration . 's'
                                );

                                $this->addMessageExtraInfo($this->messageGroup->getUserId(),
                                    Gpf_Rpc_Json::encodeStatic($voiceData->toObject()),
                                    La_Model_Message::TYPE_VOICE,
                                    La_Model_Message::FORMAT_JSON
                                );

                                $this->conversation->updatePreview($this->messageGroup->getPreview());
                            }
                            break;
                        case '':
                        case 'NOANSWER':
                        case 'CANCEL':
                        case 'CHANUNAVAIL':
                        case 'BUSY':
                            if ($incall->recording != '') {
                                $this->addMessageExtraInfo($this->conversation->getOwner()->getId(),
                                    'Left a voice mail. Status: ' .  $incall->status
                                );

                                $voiceData = $this->getVoiceData($incall->recording, $incall->duration);

                                $this->addMessageExtraInfo($this->messageGroup->getUserId(),
                                    'Duration: ' . $incall->duration . 's'
                                );

                                $this->addMessageExtraInfo($this->messageGroup->getUserId(),
                                    Gpf_Rpc_Json::encodeStatic($voiceData->toObject()),
                                    La_Model_Message::TYPE_VOICE,
                                    La_Model_Message::FORMAT_JSON
                                );

                                $this->conversation->updatePreview($this->messageGroup->getPreview());
                            }
                            break;
                    }

                }
                die('-1-');
            }

        }
    }
}

$now = time();
$i = 1;
$date_inc = 864000;
//$date_from = 1420070400;
$date_from = 1438387200;

$common_pages = ceil(($now - $date_from) / $date_inc);
echo 'Amount: ' . $common_pages . ' pages.<br>';

while ($date_from < $now) {
    $cdr = new OceanCDR();
    $calls = $cdr->getCalls($date_from, $date_inc);
    $result = $cdr->processCalls($calls);

    $date_from += $date_inc;
    var_dump($calls);
    echo $i++;
}
<?php


namespace diduhless\parties\form;


use diduhless\parties\form\element\GoBackPartyButton;
use diduhless\parties\session\Session;
use diduhless\parties\utils\StoresSession;
use EasyUI\element\Button;
use EasyUI\variant\SimpleForm;
use pocketmine\player\Player;

class InvitationsForm extends SimpleForm {
    use StoresSession;

    public function __construct(Session $session) {
        $this->session = $session;
        parent::__construct("Lời mời");
    }

    protected function onCreation(): void {
        $invitations = $this->session->getInvitations();

        if(!empty($invitations)) {
            $this->setHeaderText("Các lời mời của bạn:");
            foreach($invitations as $invitation) {
                $this->addButton(new Button($invitation->getSender()->getUsername() . "'s Party", null, function(Player $player) use ($invitation) {
                    $player->sendForm(new ConfirmInvitationForm($this->session, $invitation));
                }));
            }
        } else {
            $this->setHeaderText("Bạn không có lời mời nào :(");
        }
        $this->addButton(new GoBackPartyButton());
    }

}
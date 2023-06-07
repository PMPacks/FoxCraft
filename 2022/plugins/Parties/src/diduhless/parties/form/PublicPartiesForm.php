<?php


namespace diduhless\parties\form;


use diduhless\parties\form\element\GoBackPartyButton;
use diduhless\parties\party\Invitation;
use diduhless\parties\party\PartyFactory;
use diduhless\parties\session\Session;
use diduhless\parties\utils\StoresSession;
use EasyUI\element\Button;
use EasyUI\variant\SimpleForm;
use pocketmine\player\Player;

class PublicPartiesForm extends SimpleForm {
    use StoresSession;

    public function __construct(Session $session) {
        $this->session = $session;
        parent::__construct("Tham gia một Party");
    }

    protected function onCreation(): void {
        foreach(PartyFactory::getParties() as $party) {
            if($party->isPublic() and !$party->isFull()) {
                $this->addButton(new Button($party->getLeaderName() . "'s Party", null, function(Player $player) use ($party) {
                    $player->sendForm(new ConfirmInvitationForm($this->session, new Invitation($party->getLeader(), $this->session, $party->getId())));
                }));
            }
        }
        if(!empty($this->getButtons())) {
            $this->setHeaderText("Ấn vào nếu bạn muốn tham gia");
        } else {
            $this->setHeaderText("Chưa có bất kỳ party nào để bạn tham gia");
        }
        $this->addButton(new GoBackPartyButton());
    }

}
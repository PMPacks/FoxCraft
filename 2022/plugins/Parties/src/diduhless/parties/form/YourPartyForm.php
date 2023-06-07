<?php


namespace diduhless\parties\form;


use diduhless\parties\event\PartyDisbandEvent;
use diduhless\parties\event\PartyLeaveEvent;
use diduhless\parties\party\PartyFactory;
use diduhless\parties\session\Session;
use diduhless\parties\utils\StoresSession;
use EasyUI\element\Button;
use EasyUI\variant\SimpleForm;
use pocketmine\player\Player;

class YourPartyForm extends SimpleForm {
    use StoresSession;

    public function __construct(Session $session) {
        $this->session = $session;
        parent::__construct("Party của bạn", "Bạn muốn kiểm tra thứ gì tại đây?");
    }

    protected function onCreation(): void {
        $this->addMembersButton();
        if(!$this->session->isPartyLeader()) {
            $this->addLeavePartyButton();
        } else {
            $this->addPlayerInviteButton();
            $this->addPartyOptionsButton();
            $this->addDisbandPartyButton();
        }
    }

    private function addMembersButton(): void {
        if(!$this->session->hasParty()) return;

        $this->addButton(new Button("Thành viên", null, function(Player $player) {
            $player->sendForm(new PartyMembersForm($this->session));
        }));
    }

    private function addPlayerInviteButton(): void {
        if(!$this->session->hasParty()) return;

        $this->addButton(new Button("Mời một người chơi", null, function(Player $player) {
            $player->sendForm(new PartyInviteForm($this->session));
        }));
    }

    private function addPartyOptionsButton(): void {
        $this->addButton(new Button("Party Options", null, function(Player $player) {
            $player->sendForm(new PartyOptionsForm($this->session));
        }));
    }

    public function addDisbandPartyButton(): void {
        $button = new Button("Giải tán Party");
        $button->setSubmitListener(function(Player $player) {
            $party = $this->session->getParty();

            $event = new PartyDisbandEvent($party, $this->session);
            $event->call();
            if($event->isCancelled()) return;

            foreach($party->getMembers() as $member) {
                $party->remove($member);
            }
            PartyFactory::removeParty($party);
        });
        $this->addButton($button);
    }

    private function addLeavePartyButton(): void {
        if(!$this->session->hasParty()) return;

        $button = new Button("Rời khỏi Party");
        $button->setSubmitListener(function(Player $player) {;
            $party = $this->session->getParty();

            $event = new PartyLeaveEvent($party, $this->session);
            $event->call();

            if(!$event->isCancelled()) {
                $party->remove($this->session);
            }
        });
        $this->addButton($button);
    }

}
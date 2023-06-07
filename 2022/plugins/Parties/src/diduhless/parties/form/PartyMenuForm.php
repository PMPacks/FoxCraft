<?php


namespace diduhless\parties\form;


use diduhless\parties\event\PartyCreateEvent;
use diduhless\parties\party\Party;
use diduhless\parties\party\PartyFactory;
use diduhless\parties\session\Session;
use diduhless\parties\utils\StoresSession;
use EasyUI\element\Button;
use EasyUI\variant\SimpleForm;
use pocketmine\player\Player;

class PartyMenuForm extends SimpleForm {
    use StoresSession;

    public function __construct(Session $session) {
        $this->session = $session;
        parent::__construct("Party Menu", "Bạn không có Party! Tạo một Party hoặc nhận lời mời từ một Party khác.");
    }

    protected function onCreation(): void {
        $this->addCreatePartyButton();
        $this->addPublicPartiesButton();
        $this->addInvitationsButton();
    }

    private function addCreatePartyButton(): void {
        $button = new Button("Tạo Party");
        $button->setSubmitListener(function(Player $player) {
            $party = new Party(uniqid(), $this->session);
            $event = new PartyCreateEvent($party, $this->session);

            $event->call();
            if(!$event->isCancelled()) {				
                $party->add($this->session);
                PartyFactory::addParty($party);
                $this->session->openPartyForm();
            }
        });
        $this->addButton($button);
    }

    public function addPublicPartiesButton(): void {
        $this->addButton(new Button("Tham gia một Party", null, function(Player $player) {
            $player->sendForm(new PublicPartiesForm($this->session));
        }));
    }

    public function addInvitationsButton(): void {
        $this->addButton(new Button("Lời mời [" . count($this->session->getInvitations()) . "]", null, function(Player $player) {
            $player->sendForm(new InvitationsForm($this->session));
        }));
    }

}
<?php


namespace diduhless\parties\form;


use diduhless\parties\event\PartyLeaderPromoteEvent;
use diduhless\parties\event\PartyMemberKickEvent;
use diduhless\parties\form\element\GoBackButton;
use diduhless\parties\session\Session;
use diduhless\parties\utils\StoresSession;
use EasyUI\element\Button;
use EasyUI\variant\SimpleForm;
use pocketmine\player\Player;
use hachkingtohach1\Dungeon\Dungeon;

class PartyMemberForm extends SimpleForm {
    use StoresSession;

    private Session $member;

    public function __construct(Session $session, Session $member) {
        $this->session = $session;
        $this->member = $member;
        parent::__construct("Các thành viên trong Party", "Bạn muốn làm gì với các thành viên?");
    }

    protected function onCreation(): void {
        $this->addKickButton();
        $this->addPromoteButton();
		$this->addTeleportButton();
        $this->addButton(new GoBackButton(new PartyMembersForm($this->session)));
    }

    private function addKickButton(): void {
        $button = new Button("Kick cậu ta ra khỏi");
        $button->setSubmitListener(function(Player $player) {
            if(!$this->member->isOnline()) return;
            $party = $this->session->getParty();

            $event = new PartyMemberKickEvent($party, $this->session, $this->member);
            $event->call();

            if(!$event->isCancelled()) {
                $party->remove($this->member);
            }
        });
        $this->addButton($button);
    }

    private function addPromoteButton(): void {
        $button = new Button("Trao quyền cho họ là chủ Party");
        $button->setSubmitListener(function(Player $player) {
            if(!$this->member->isOnline()) return;
            $party = $this->session->getParty();

            $event = new PartyLeaderPromoteEvent($party, $this->session, $this->member);
            $event->call();

            if(!$event->isCancelled()) {
                $party->setLeader($this->member);
            }
        });
        $this->addButton($button);
    }

    private function addTeleportButton(): void {
        $button = new Button("Dịch chuyển cậu ta đến đây");
        $button->setSubmitListener(function(Player $player) {
            if(!$this->member->isOnline()) return;
            if(!Dungeon::getInstance()->inGame($this->session->getPlayer())){
			    $party = $this->session->getParty();
                $this->member->getPlayer()->teleport($this->session->getPlayer()->getLocation());
			}else{
				$this->session->getPlayer()->sendMessage("Bạn không thể làm điều này khi đang trong Dungeon!");
			}
        });
        $this->addButton($button);
    }
}
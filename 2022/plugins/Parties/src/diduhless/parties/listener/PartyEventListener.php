<?php


namespace diduhless\parties\listener;


use diduhless\parties\event\PartyCreateEvent;
use diduhless\parties\event\PartyDisbandEvent;
use diduhless\parties\event\PartyInviteEvent;
use diduhless\parties\event\PartyJoinEvent;
use diduhless\parties\event\PartyLeaderPromoteEvent;
use diduhless\parties\event\PartyLeaveEvent;
use diduhless\parties\event\PartyMemberKickEvent;
use diduhless\parties\event\PartyPvpDisableEvent;
use diduhless\parties\event\PartyPvpEnableEvent;
use diduhless\parties\event\PartySetPrivateEvent;
use diduhless\parties\event\PartySetPublicEvent;
use diduhless\parties\event\PartyUpdateSlotsEvent;
use diduhless\parties\event\PartyWorldTeleportDisableEvent;
use diduhless\parties\event\PartyWorldTeleportEnableEvent;
use pocketmine\event\Listener;

class PartyEventListener implements Listener {

    /**
     * @param PartyCreateEvent $event
     * @ignoreCancelled
     * @priority HIGHEST
     */
    public function onCreate(PartyCreateEvent $event): void {
        //$event->getSession()->message("{GREEN}You have created a party!");
    }

    /**
     * @param PartyDisbandEvent $event
     * @ignoreCancelled
     * @priority HIGHEST
     */
    public function onDisband(PartyDisbandEvent $event): void {
        $session = $event->getSession();
        $party = $event->getParty();

        $session->message("{RED}Party đã bị giải tán!");
        $party->message("{RED}Party đã bị giải tán bởi {WHITE}" . $party->getLeaderName() . " {RED}đã thoát game.", $session);
    }

    /**
     * @param PartyInviteEvent $event
     * @ignoreCancelled
     * @priority HIGHEST
     */
    public function onInvite(PartyInviteEvent $event): void {
        $session = $event->getSession();
        $target = $event->getTarget();

        $targetName = $target->getUsername();

        $session->message("{GREEN}Bạn đã được mời {WHITE}$targetName {GREEN}đến Party! Thời gian hết hạn sau {WHITE}1 phút {GREEN}để chấp nhận lời mời.");
        $target->message("{GREEN}Bạn đã được mời để tham gia Party của {WHITE}" . $session->getUsername() . "{GREEN}");
        $event->getParty()->message("$targetName {GREEN}đã được mời tới Party!", $session);
    }

    /**
     * @param PartyJoinEvent $event
     * @ignoreCancelled
     * @priority HIGHEST
     */
    public function onJoin(PartyJoinEvent $event): void {
        $session = $event->getSession();
        $party = $event->getParty();

        $session->message("{GREEN}Bạn đã tham gia của {WHITE}" . $party->getLeaderName() . "{GREEN}");
        $party->message($session->getUsername() . " {GREEN}đã tham gia Party!");
    }

    /**
     * @param PartyLeaderPromoteEvent $event
     * @ignoreCancelled
     * @priority HIGHEST
     */
    public function onLeaderPromote(PartyLeaderPromoteEvent $event): void {
        $session = $event->getSession();
        $newLeader = $event->getNewLeader();
        $party = $event->getParty();

        $sessionName = $session->getUsername();
        $newLeaderName = $newLeader->getUsername();

        $session->message("{GREEN}Bạn đưa quyền cho {WHITE}$newLeaderName {GREEN}để làm chủ Party!");
        $newLeader->message("{GREEN}Bạn đã được bầu bởi {WHITE}$sessionName {GREEN}để làm chủ Party!");
        $party->message("$sessionName {GREEN}đã bầu cho {WHITE}$newLeaderName {GREEN}để làm chủ Party!", $session);
    }

    /**
     * @param PartyLeaveEvent $event
     * @ignoreCancelled
     * @priority HIGHEST
     */
    public function onLeave(PartyLeaveEvent $event): void {
        $session = $event->getSession();
        $party = $event->getParty();

        $session->message("{RED}Bạn đã thoát khỏi Party {WHITE}" . $party->getLeaderName() . "{RED}'s party!");
        $party->message($session->getUsername() . " {RED}đã thoát khỏi Party!", $session);
    }

    /**
     * @param PartyMemberKickEvent $event
     * @ignoreCancelled
     * @priority HIGHEST
     */
    public function onMemberKick(PartyMemberKickEvent $event): void {
        $member = $event->getMember();

        $member->message("{RED}Bạn đã bị kick khỏi Party bởi {WHITE}" . $event->getSession()->getUsername() . "{RED}'s party!");
        $event->getParty()->message($member->getUsername() . " {RED}đã bị kick khỏi Party!", $member);
    }

    /**
     * @param PartyPvpEnableEvent $event
     * @ignoreCancelled
     * @priority HIGHEST
     */
    public function onEnablePvp(PartyPvpEnableEvent $event): void {
        $event->getParty()->message("{GREEN}Chế độ PvP giữa các thành viên trong Party {WHITE}enabled{GREEN}!");
    }

    /**
     * @param PartyPvpDisableEvent $event
     * @ignoreCancelled
     * @priority HIGHEST
     */
    public function onDisablePvp(PartyPvpDisableEvent $event): void {
        $event->getParty()->message("{GREEN}Chế độ PvP giữa các thành viên trong Party {WHITE}disabled{GREEN}!");
    }

    /**
     * @param PartyWorldTeleportEnableEvent $event
     * @ignoreCancelled
     * @priority HIGHEST
     */
    public function onWorldTeleportEnable(PartyWorldTeleportEnableEvent $event): void {
        $event->getParty()->message("{GREEN}Cho phép các thành viên có thể tự do dịch chuyển sang thế giới khác đã được {WHITE}Bật{GREEN}!");
    }

    /**
     * @param PartyWorldTeleportDisableEvent $event
     * @ignoreCancelled
     * @priority HIGHEST
     */
    public function onWorldTeleportDisable(PartyWorldTeleportDisableEvent $event): void {
        $event->getParty()->message("{GREEN}Cho phép các thành viên có thể tự do dịch chuyển sang thế giới khác đã được {WHITE}Tắt{GREEN}!");
    }

    /**
     * @param PartySetPrivateEvent $event
     * @ignoreCancelled
     * @priority HIGHEST
     */
    public function onLock(PartySetPrivateEvent $event): void {
        $event->getParty()->message("{GREEN}Party giờ ở chế độ {WHITE}riêng tư{GREEN}!");
    }

    /**
     * @param PartySetPublicEvent $event
     * @ignoreCancelled
     * @priority HIGHEST
     */
    public function onUnlock(PartySetPublicEvent $event): void {
        $event->getParty()->message("{GREEN}Party giờ ở chế độ {WHITE}công cộng{GREEN}!");
    }

    /**
     * @param PartyUpdateSlotsEvent $event
     * @ignoreCancelled
     * @priority HIGHEST
     */
    public function onUpdateSlots(PartyUpdateSlotsEvent $event): void {
        $event->getParty()->message("{GREEN}Slots của party giờ là {WHITE}" . $event->getSlots() . "{GREEN}!");
    }

}
<?php
/**
 * Připomněnka - CallListController
 *
 * Seznam k provolání
 */

declare(strict_types=1);

namespace Controllers;

use Models\Reminder;

class CallListController extends BaseController
{
    private Reminder $reminder;

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->reminder = new Reminder();
    }

    /**
     * Seznam k provolání dnes
     */
    public function today(array $params): void
    {
        $today = new \DateTime();
        $calls = $this->reminder->getForCallDate($today);

        $this->view('admin/calls/today', [
            'title' => 'Dnes volat',
            'calls' => $calls,
            'date' => $today,
        ], 'admin');
    }

    /**
     * Přehled týdne
     */
    public function week(array $params): void
    {
        $days = [];
        $today = new \DateTime();

        for ($i = 0; $i < 7; $i++) {
            $date = (new \DateTime())->modify("+{$i} days");
            $calls = $this->reminder->getForCallDate($date);
            $days[] = [
                'date' => $date,
                'calls' => $calls,
                'count' => count($calls),
                'isToday' => $i === 0,
            ];
        }

        $this->view('admin/calls/week', [
            'title' => 'Tento týden',
            'days' => $days,
        ], 'admin');
    }

    /**
     * Zaznamenat výsledek volání
     */
    public function logCall(array $params): void
    {
        $this->validateCsrf();

        $queueId = (int) $params['id'];
        $action = $this->input('action');

        // Najít záznam ve frontě
        $queueItem = $this->db->fetchOne(
            "SELECT cq.*, r.customer_id FROM call_queue cq
             JOIN reminders r ON r.id = cq.reminder_id
             WHERE cq.id = ?",
            [$queueId]
        );

        if (!$queueItem) {
            flash('error', 'Záznam nenalezen.');
            $this->redirect('/admin/dnes');
        }

        switch ($action) {
            case 'completed':
                $this->handleCompleted($queueItem);
                break;
            case 'no_answer':
                $this->handleNoAnswer($queueItem);
                break;
            case 'declined':
                $this->handleDeclined($queueItem);
                break;
            case 'postponed':
                $this->handlePostponed($queueItem);
                break;
            default:
                flash('error', 'Neplatná akce.');
        }

        $this->redirect('/admin/dnes');
    }

    /**
     * Vyřízeno
     */
    private function handleCompleted(array $queueItem): void
    {
        $orderAmount = $this->input('order_amount') ? (float) $this->input('order_amount') : null;
        $note = $this->input('note');

        // Zalogovat hovor
        $this->db->insert('call_logs', [
            'reminder_id' => $queueItem['reminder_id'],
            'call_date' => date('Y-m-d'),
            'status' => 'completed',
            'order_amount' => $orderAmount,
            'admin_note' => $note,
        ]);

        // Aktualizovat frontu
        $this->db->update('call_queue', ['status' => 'completed'], 'id = ?', [$queueItem['id']]);

        flash('success', 'Hovor zaznamenán jako vyřízený.');
    }

    /**
     * Nezvedá
     */
    private function handleNoAnswer(array $queueItem): void
    {
        $attemptCount = $queueItem['attempt_count'] + 1;

        // Zalogovat pokus
        $this->db->insert('call_logs', [
            'reminder_id' => $queueItem['reminder_id'],
            'call_date' => date('Y-m-d'),
            'status' => 'no_answer',
        ]);

        if ($attemptCount >= 5) {
            // Po 5 pokusech vzdát
            $this->db->update('call_queue', ['status' => 'gave_up'], 'id = ?', [$queueItem['id']]);
            flash('warning', 'Zákazník nedostupný po 5 pokusech. Letos už nevoláme.');
        } else {
            // Přesunout na další pracovní den
            $nextDate = $this->getNextWorkday();

            $this->db->update('call_queue', ['status' => 'no_answer'], 'id = ?', [$queueItem['id']]);

            // Vytvořit nový záznam na další den
            $this->db->query(
                "INSERT INTO call_queue (reminder_id, scheduled_date, attempt_count, priority, status)
                 VALUES (?, ?, ?, ?, 'pending')
                 ON DUPLICATE KEY UPDATE attempt_count = ?, status = 'pending'",
                [$queueItem['reminder_id'], $nextDate, $attemptCount, $attemptCount > 2 ? 1 : 0, $attemptCount]
            );

            flash('info', 'Přesunuto na ' . format_date($nextDate) . '. Pokus č. ' . $attemptCount);
        }
    }

    /**
     * Nechce letos
     */
    private function handleDeclined(array $queueItem): void
    {
        // Zalogovat
        $this->db->insert('call_logs', [
            'reminder_id' => $queueItem['reminder_id'],
            'call_date' => date('Y-m-d'),
            'status' => 'declined',
        ]);

        // Aktualizovat frontu
        $this->db->update('call_queue', ['status' => 'declined'], 'id = ?', [$queueItem['id']]);

        flash('info', 'OK, letos nevoláme.');
    }

    /**
     * Odložit na jindy
     */
    private function handlePostponed(array $queueItem): void
    {
        $postponedTo = $this->input('postponed_to');

        if (!$postponedTo) {
            flash('error', 'Vyberte datum.');
            return;
        }

        // Zalogovat
        $this->db->insert('call_logs', [
            'reminder_id' => $queueItem['reminder_id'],
            'call_date' => date('Y-m-d'),
            'status' => 'postponed',
            'postponed_to' => $postponedTo,
        ]);

        // Aktualizovat frontu
        $this->db->update('call_queue', ['status' => 'postponed'], 'id = ?', [$queueItem['id']]);

        // Vytvořit nový záznam
        $this->db->query(
            "INSERT INTO call_queue (reminder_id, scheduled_date, attempt_count, priority, status)
             VALUES (?, ?, ?, 0, 'pending')
             ON DUPLICATE KEY UPDATE status = 'pending'",
            [$queueItem['reminder_id'], $postponedTo, $queueItem['attempt_count']]
        );

        flash('info', 'Přesunuto na ' . format_date($postponedTo));
    }

    /**
     * Získat další pracovní den
     */
    private function getNextWorkday(): string
    {
        $date = new \DateTime('tomorrow');
        $workdays = [1, 2, 3, 4, 5]; // Po-Pá

        while (!in_array((int) $date->format('N'), $workdays)) {
            $date->modify('+1 day');
        }

        // TODO: Kontrola státních svátků

        return $date->format('Y-m-d');
    }
}

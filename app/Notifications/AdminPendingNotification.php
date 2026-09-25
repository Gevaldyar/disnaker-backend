<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdminPendingNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $type,
        public string $title,
        public string $message,
        public ?int $companyId = null,
        public ?int $jobId = null,
    ) {
    }

    /**
     * Channel notification.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Data yang disimpan ke database.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => $this->type,
            'title' => $this->title,
            'message' => $this->message,
            'company_id' => $this->companyId,
            'job_id' => $this->jobId,
        ];
    }

    /**
     * Tipe notification yang disimpan pada kolom type.
     */
    public function databaseType(object $notifiable): string
    {
        return $this->type;
    }
}
<?php

namespace App\Notifications;

use App\Models\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class JobStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Job $job,
        public string $status
    ) {
    }

    /**
     * Tentukan channel notification.
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
        if ($this->status === 'approved') {
            return [
                'type' => 'job_approved',
                'title' => 'Lowongan Disetujui',
                'message' => 'Lowongan "' . $this->job->title . '" telah disetujui oleh Admin Disnaker dan sekarang dapat tampil di publik.',
                'job_id' => $this->job->id,
                'job_title' => $this->job->title,
                'status' => 'approved',
                'rejection_reason' => null,
            ];
        }

        return [
            'type' => 'job_rejected',
            'title' => 'Lowongan Ditolak',
            'message' => 'Lowongan "' . $this->job->title . '" ditolak oleh Admin Disnaker.',
            'job_id' => $this->job->id,
            'job_title' => $this->job->title,
            'status' => 'rejected',
            'rejection_reason' => $this->job->rejection_reason,
        ];
    }

    /**
     * Nama tipe notification yang disimpan di database.
     */
    public function databaseType(object $notifiable): string
    {
        return $this->status === 'approved'
            ? 'job-approved'
            : 'job-rejected';
    }
}
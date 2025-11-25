<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\ClientRequest;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'content',
        'read_at',
        'is_reported',
        'status',
        'moderation_reason',
        'moderated_at',
        'moderated_by',
        'admin_read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'moderated_at' => 'datetime',
        'admin_read_at' => 'datetime',
        'is_reported' => 'boolean',
    ];

    /**
     * Get the sender of the message.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the receiver of the message.
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Get the client request associated with this message.
     */
    public function clientRequest(): BelongsTo
    {
        return $this->belongsTo(ClientRequest::class, 'client_request_id');
    }

    /**
     * Get the moderator who moderated this message.
     */
    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    /**
     * Scope to get unread messages for a user.
     */
    public function scopeUnreadFor($query, $userId)
    {
        return $query->where('receiver_id', $userId)->whereNull('read_at');
    }

    /**
     * Scope to get messages between two users.
     */
    public function scopeBetweenUsers($query, $user1Id, $user2Id)
    {
        return $query->where(function ($q) use ($user1Id, $user2Id) {
            $q->where('sender_id', $user1Id)->where('receiver_id', $user2Id);
        })->orWhere(function ($q) use ($user1Id, $user2Id) {
            $q->where('sender_id', $user2Id)->where('receiver_id', $user1Id);
        });
    }

    /**
     * Mark the message as read.
     */
    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }

    /**
     * Check if the message is read.
     */
    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    /**
     * Scope to get reported messages.
     */
    public function scopeReported($query)
    {
        return $query->where('is_reported', true);
    }

    /**
     * Scope to get messages by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get visible messages (not hidden or deleted).
     */
    public function scopeVisible($query)
    {
        return $query->whereNotIn('status', ['hidden', 'deleted']);
    }

    /**
     * Report this message.
     */
    public function report($reason = null)
    {
        $this->update([
            'is_reported' => true,
            'moderation_reason' => $reason,
        ]);
    }

    /**
     * Moderate this message.
     */
    public function moderate($status, $moderatorId, $reason = null)
    {
        $this->update([
            'status' => $status,
            'moderation_reason' => $reason,
            'moderated_at' => now(),
            'moderated_by' => $moderatorId,
        ]);
    }

    /**
     * Check if the message is moderated.
     */
    public function isModerated(): bool
    {
        return !is_null($this->moderated_at);
    }

    /**
     * Check if the message is visible to users.
     */
    public function isVisible(): bool
    {
        return !in_array($this->status, ['hidden', 'deleted']);
    }

    /**
     * Get an excerpt of the message content.
     *
     * @param int $length
     * @return string
     */
    public function getExcerpt($length = 100)
    {
        if (strlen($this->content) <= $length) {
            return $this->content;
        }
        
        return substr($this->content, 0, $length) . '...';
    }
}
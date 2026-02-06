<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSession extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'hotspot_user_id',
        'router_id',
        'session_id',
        'mac_address',
        'ip_address',
        'started_at',
        'ended_at',
        'bytes_in',
        'bytes_out',
        'duration',
        'termination_reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'bytes_in' => 'integer',
        'bytes_out' => 'integer',
        'duration' => 'integer',
    ];

    /**
     * Relationships
     */

    public function hotspotUser(): BelongsTo
    {
        return $this->belongsTo(HotspotUser::class);
    }

    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class);
    }

    /**
     * Scopes
     */

    public function scopeActive($query)
    {
        return $query->whereNull('ended_at');
    }

    public function scopeEnded($query)
    {
        return $query->whereNotNull('ended_at');
    }

    public function scopeForHotspotUser($query, int $hotspotUserId)
    {
        return $query->where('hotspot_user_id', $hotspotUserId);
    }

    public function scopeForRouter($query, int $routerId)
    {
        return $query->where('router_id', $routerId);
    }

    public function scopeStartedBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('started_at', [$startDate, $endDate]);
    }

    public function scopeEndedBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('ended_at', [$startDate, $endDate]);
    }

    /**
     * Helper Methods
     */

    public function isActive(): bool
    {
        return is_null($this->ended_at);
    }

    public function isEnded(): bool
    {
        return !is_null($this->ended_at);
    }

    public function endSession(?string $terminationReason = null): void
    {
        $this->update([
            'ended_at' => now(),
            'duration' => $this->calculateDuration(),
            'termination_reason' => $terminationReason,
        ]);
    }

    public function getTotalBytes(): int
    {
        return $this->bytes_in + $this->bytes_out;
    }

    public function getFormattedBytesIn(): string
    {
        return $this->formatBytes($this->bytes_in);
    }

    public function getFormattedBytesOut(): string
    {
        return $this->formatBytes($this->bytes_out);
    }

    public function getFormattedTotalBytes(): string
    {
        return $this->formatBytes($this->getTotalBytes());
    }

    public function getFormattedDuration(): string
    {
        $hours = floor($this->duration / 3600);
        $minutes = floor(($this->duration % 3600) / 60);
        $seconds = $this->duration % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    public function updateUsageStats(int $bytesIn, int $bytesOut): void
    {
        $this->update([
            'bytes_in' => $bytesIn,
            'bytes_out' => $bytesOut,
            'duration' => $this->calculateDuration(),
        ]);
    }

    private function calculateDuration(): int
    {
        if (!$this->started_at) {
            return 0;
        }

        $endTime = $this->ended_at ?? now();
        return $this->started_at->diffInSeconds($endTime);
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}

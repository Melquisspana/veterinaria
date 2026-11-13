<?php

namespace App\Models;

use App\Enums\AttachmentCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    protected $fillable = [
        'attachable_type', 'attachable_id', 'title', 'category', 'file_path', 'original_name', 'description',
    ];

    protected function casts(): array
    {
        return [
            'category' => AttachmentCategory::class,
        ];
    }

    protected static function booted(): void
    {
        static::deleted(function (Attachment $attachment) {
            Storage::disk(config('vet.uploads_disk'))->delete($attachment->file_path);
        });
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function url(): string
    {
        $disk = config('vet.uploads_disk');

        if ($disk === 'public') {
            return Storage::disk($disk)->url($this->file_path);
        }

        return Storage::disk($disk)->temporaryUrl($this->file_path, now()->addMinutes(30));
    }

    public function isImage(): bool
    {
        return (bool) preg_match('/\.(jpe?g|png|gif|webp|heic)$/i', $this->file_path);
    }
}

<?php

namespace App;

use App\Traits\Auditable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TicketDescription extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia, Auditable, HasFactory;

    public $table = 'ticket_descriptions';

    protected $appends = [
        'supporting_files',
        'supporting_photo',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'ticket_category_id',
        'ticket_description',
        'ticket_title',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')->fit('crop', 50, 50);
        $this->addMediaConversion('preview')->fit('crop', 120, 120);
    }

    public function ticketDescriptionTicketResponses()
    {
        return $this->hasMany(TicketResponse::class, 'ticket_description_id', 'id');
    }

    public function ticket_category()
    {
        return $this->belongsTo(Ticketcategory::class, 'ticket_category_id');
    }

    public function getSupportingFilesAttribute()
    {
        return $this->getMedia('supporting_files');
    }

    public function getSupportingPhotoAttribute()
    {
        $files = $this->getMedia('supporting_photo');
        $files->each(function ($item) {
            $item->url       = $item->getUrl();
            $item->thumbnail = $item->getUrl('thumb');
            $item->preview   = $item->getUrl('preview');
        });

        return $files;
    }
}

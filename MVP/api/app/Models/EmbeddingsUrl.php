<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Embeddingsurl extends Model
{
    use HasFactory;

    protected $table = 'embeddingsurl';
    public $incrementing = false;

    protected $casts = [
        'embedding' => 'array',
        'cmetadata' => 'array',
    ];

    protected $fillable = [
        'embeddingurl_collections_id',
        'embedding',
        'document',
        'cmetadata',
        'custom_id',
        'uuid'
    ];
}

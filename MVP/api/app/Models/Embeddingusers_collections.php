<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Embeddingusers_collections extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $primaryKey = 'uuid';
    protected $table = 'embeddingusers_collections';
    protected $fillable = [
        "name", "cmetadata", "uuid"
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->uuid = Uuid::uuid4()->toString();
        });
    }
}

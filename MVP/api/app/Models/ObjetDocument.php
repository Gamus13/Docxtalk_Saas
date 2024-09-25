<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ObjetDocument extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id', 'document_type', 'ai_response'];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->id = (string) Str::uuid(); // Génération d'un UUID
        });
    }
}

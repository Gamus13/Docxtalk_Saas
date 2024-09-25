<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Str;

class Userpdf extends Model
{
    use HasFactory;

    protected $table = 'userpdf'; // Spécifie la table associée si nécessaire

    public $incrementing = false; // Utilisation d'UUID
    protected $keyType = 'string'; // Type de clé primaire

    protected $fillable = [
        'id', // UUID
        'userpdf_path',
        'usertitle',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid()->toString(); // Génération d'un UUID
            }
        });
    }
}

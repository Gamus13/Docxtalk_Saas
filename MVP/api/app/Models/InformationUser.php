<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InformationUser extends Model
{
    use HasFactory;

    protected $table = 'information_user';

    protected $fillable = [
        'expediteur_info', // Utiliser une seule colonne
    ];
}

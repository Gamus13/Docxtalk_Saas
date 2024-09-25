<?php

namespace App\Http\Controllers;


use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Services\WebToPdfService;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ProcessUsersExtraction;
use App\Models\Userpdf;

class UserPdfController extends Controller
{
    //

    public function upload(Request $request)
    {
        // Valider la demande
        $request->validate([
            'file' => 'required|mimes:pdf|max:20480', // Max 20MB
        ]);

        // Obtenir le fichier uploadé
        $file = $request->file('file');

        // Déterminer le chemin de stockage dans le dossier 'userspdf'
        $filePath = $file->store('userspdf', 'public');
        $fileUrl = Storage::url($filePath);

        // Créez une nouvelle instance de Userpdf
        $userpdf = Userpdf::create([
            'userpdf_path' => storage_path('app/public/' . $filePath),
            'usertitle' => $file->getClientOriginalName(),
        ]);

        // Déclenche le processus de traitement en arrière-plan
        Queue::push(new ProcessUsersExtraction($userpdf));

        return response()->json([
            'message' => 'Fichier uploadé et traitement démarré.',
            'file_path' => $fileUrl,
            'file_name' => $file->getClientOriginalName(),
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\WebToPdfService;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Log;
use App\Jobs\ProcessTextExtraction;
use Illuminate\Support\Facades\Http;
use App\Models\UrlPdf;
use App\Models\Embeddingsurl;
use App\Models\FinalDataUser;
use App\Models\ObjetDocument;
use App\Models\GeneratePdf; // Import du modèle
use Illuminate\Support\Str;
use App\Models\ContextUser;


class UrlPdfController extends Controller
{
    //
    public function __construct(WebToPdfService $webToPdfService)
    {
        $this->webToPdfService = $webToPdfService;
    }

    public function convert(Request $request)
    {
        $url = $request->input('url');
        $result = $this->webToPdfService->convertUrlToPdf($url);

        if (!is_array($result)) {
            return response()->json(['error' => 'Unexpected response format'], 500);
        }

        if (isset($result['error']) && $result['error']) {
            return response()->json(['error' => $result['message']], 500);
        }

        // Créez une nouvelle instance de UrlPdf
        $urlPdf = UrlPdf::create([
            'pdf_path' => $result['file_path'] ?? '',
            'title' => $result['file_url'] ?? '',
        ]);

        // Déclenche le processus de traitement en arrière-plan
        Queue::push(new ProcessTextExtraction($urlPdf));

        return response()->json([
            'message' => $result['message'] ?? 'Conversion successful',
            'file_path' => $result['file_path'] ?? '',
            'file_url' => $result['file_url'] ?? ''
        ]);
    }
}

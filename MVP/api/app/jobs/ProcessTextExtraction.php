<?php

namespace App\Jobs;

use App\Models\UrlPdf;
use App\Models\embeddingurl_collections;
use App\Models\EmbeddingsUrl;
use App\Http\Repository\DocumentRepository;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Mis3085\Tiktoken\Facades\Tiktoken;
use Smalot\PdfParser\Parser;

class ProcessTextExtraction implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 2 * 60 * 60; // 2 hours

    /**
     * Create a new job instance.
     *
     * @param UrlPdf $urlPdf
     */
    public function __construct(public UrlPdf $urlPdf)
    {
        Log::info("Initialized ProcessTextExtraction job for UrlPdf ID: " . $this->urlPdf->id);
    }

    /**
     * Execute the job.
     */
    public function handle(DocumentRepository $documentRepository): void
    {
        try {
            // Chemin du fichier PDF directement depuis la base de données
            $pdf_path = $this->urlPdf->pdf_path;

            // Log the original path from database
            Log::info("Original PDF path from database: $pdf_path");

            // Vérifiez si le fichier PDF existe
            if (!file_exists($pdf_path)) {
                Log::error("PDF file does not exist at path: $pdf_path");
                return;
            }

            $total_token_embed = 0;

            // Créer une nouvelle collection d'embedding
            $embeddingurl_collections = embeddingurl_collections::create([
                'name' => basename($pdf_path), // Use only the file name
                'cmetadata' => json_encode([
                    "total_token" => 0,
                    "title" => $this->urlPdf->title
                ])
            ]);
            Log::info("Created embeddingurl_collections with ID: " . $embeddingurl_collections->id);

            // Initialiser le parser de PDF
            $parser = new Parser();
            try {
                $pdf = $parser->parseFile($pdf_path);
                Log::info("PDF parsed successfully");

                // Parcourir chaque page du PDF et extraire le texte
                $pages = $pdf->getPages();
                foreach ($pages as $pageNumber => $page) {
                    // Extraction du texte brut de la page
                    $text = $page->getText();

                    // Loguer le texte brut extrait, même s'il peut contenir des caractères mal formés
                    Log::info("Texte brut extrait de la page " . ($pageNumber + 1) . ": " . $text);

                    if (empty($text)) {
                        Log::warning("No text found on page " . ($pageNumber + 1));
                        continue;
                    }

                    // Forcer l'encodage en UTF-8
                    Log::info("Tentative de conversion du texte en UTF-8 pour la page " . ($pageNumber + 1));
                    $text = mb_convert_encoding($text, 'UTF-8', 'auto');

                    // Vérifier si le texte est bien encodé en UTF-8
                    if (!mb_check_encoding($text, 'UTF-8')) {
                        Log::error("Page " . ($pageNumber + 1) . ": Texte mal encodé après conversion UTF-8");
                        continue;
                    }

                    Log::info("Texte correctement encodé en UTF-8 pour la page " . ($pageNumber + 1));

                    // Compter les tokens et générer les vecteurs pour chaque page
                    $total_token = Tiktoken::count($text);
                    $total_token_embed += $total_token;

                    // Vérifier que les vecteurs ne sont pas vides
                    $vectors = $documentRepository->getQueryEmbedding($text);
                    if (empty($vectors) || !is_array($vectors)) {
                        Log::error("Page " . ($pageNumber + 1) . ": Les vecteurs générés sont vides ou invalides");
                        continue;
                    }

                    // Créer les embeddings pour chaque page
                    EmbeddingsUrl::create([
                        "uuid" => \Illuminate\Support\Str::uuid(),  // Générer un UUID
                        "embeddingurl_collections_id" => $embeddingurl_collections->uuid,  // Utiliser l'UUID de la collection créée
                        "embedding" => $vectors,
                        "document" => $text,
                        "cmetadata" => json_encode([
                            "total_token" => $total_token,
                            "page" => $pageNumber + 1,
                            "path" => $pdf_path,
                            "title" => $this->urlPdf->title
                        ])
                    ]);

                    Log::info("Created embedding for page " . ($pageNumber + 1));

                    // Mettre à jour le statut du PDF pour chaque page traitée
                    $this->urlPdf->update([
                        'status' => "Embedding page " . ($pageNumber + 1)
                    ]);
                    Log::info("Updated status for UrlPdf ID: " . $this->urlPdf->id);
                }
            } catch (Exception $e) {
                Log::error('Error parsing PDF: ' . $e->getMessage());
                return;
            }

            // Mettre à jour les métadonnées de la collection
            $embeddingurl_collections->update([
                'cmetadata' => json_encode([
                    "total_token" => $total_token_embed,
                    "title" => $this->urlPdf->title
                ])
            ]);
            Log::info("Updated embeddingurl_collections metadata");

            // Mettre à jour le statut final du PDF dans la base de données
            $this->urlPdf->update([
                'status' => "complete"
            ]);
            Log::info("Processing complete for PDF: $pdf_path with total tokens: $total_token_embed");

        } catch (Exception $e) {
            Log::error('Error during text extraction processing: ' . $e->getMessage());
        }
    }
}

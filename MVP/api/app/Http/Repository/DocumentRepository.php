<?php

namespace App\Http\Repository;

use App\Models\Embeddingurl_collections;
use App\Library\VoiceRSS;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Embedding;
use App\Models\EmbeddingsInformationuser;
use App\Models\Embeddingsurl;



class DocumentRepository
{
    private string $chat_model = 'mistral-small-latest';
    private string $embedding_model = 'mistral-embed';

    // La fonction getQueryEmbedding intervient à l'étape 5 du processus global. Son rôle spécifique est de convertir la question de l'utilisateur en un vecteur d'encodage.
    // Ce vecteur permet de mesurer la similarité entre la question posée et les passages du document extrait

    public function getQueryEmbedding($question): array
    {
        // Log::info(' Mistral AI question', ['question' => $question]);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . env('MISTRAL_SECRET'),
        ])->post(env('MISTRAL_EMBEDDING_API_URL'), [
            'model' => $this->embedding_model,
            'input' => [$question],
            'encoding_format' => 'float',
        ]);

        $result = $response->json();

        // Log::info(' Mistral AI result', ['result' => $result]);

        // Vérifier la présence de la clé 'data' dans la réponse
        if (isset($result['data']) && count($result['data']) > 0) {
            return $result['data'][0]['embedding'];
        } else {
            return [];
        }
    }

    public function findRelevantDocument($document_path, $question): array
    {
        Log::info('Valeur de la question reçue', ['question' => $question]);
        // 1. Récupérer l'embedding de la question en appelant getQueryEmbedding()
        $query_embedding = $this->getQueryEmbedding($question);

        // 2. Vérifier que l'embedding a bien été récupéré
        if (empty($query_embedding)) {
            Log::error('Aucun embedding trouvé pour la question', ['question' => $question]);
            return ['context' => '', 'metadata' => []];
        }

        // 3. Appeler la méthode findEmbedding avec l'embedding récupéré
        return $this->findEmbedding($document_path, $query_embedding);
    }

    public function findEmbedding($document_path, $query_embedding): array
    {
        Log::debug('findEmbedding function started', ['document_path' => $document_path]);

        // Requête pour sélectionner les embeddings avec la distance de similarité
        $query = <<<EOT
        SELECT embeddingsusers.embeddingusers_collections_id, embeddingsusers.embedding, embeddingsusers.document, embeddingsusers.cmetadata, embeddingsusers.custom_id, embeddingsusers.uuid, embeddingsusers.embedding <=> '%s'::vector AS distance
        FROM embeddingsusers
        JOIN embeddingusers_collections ON embeddingsusers.embeddingusers_collections_id = embeddingusers_collections.uuid
        WHERE embeddingusers_collections.name = '{collection_name}'
        ORDER BY distance ASC
        LIMIT 4
        EOT;

        // On cherche la collection correspondante dans la table 'embeddingusers_collections' par son nom
        $embedding_collection = Embeddingusers_collections::where('name', $document_path)->first();

        if (!$embedding_collection) {
            Log::error('Embedding collection not found for path: ' . $document_path);
            return ['context' => '', 'metadata' => []];
        }

        // Remplacer les placeholders dans la requête
        $query = str_replace("%s", json_encode($query_embedding), $query);
        $query = str_replace("{collection_name}", $embedding_collection->name, $query);

        // Exécution de la requête SQL
        $records = DB::select($query);

        $context = "";
        $metadata = [];

        // Parcourir les enregistrements retournés et construire le contexte et les métadonnées
        foreach ($records as $record) {
            $context .= $record->document;

            // Décoder les métadonnées JSON de `cmetadata`
            $meta = json_decode($record->cmetadata);

            if (json_last_error() === JSON_ERROR_NONE) {
                // Si c'est un string JSON, redécodez si nécessaire
                if (is_string($meta)) {
                    $meta = json_decode($meta);
                }

                // Ajoutez les métadonnées si la page est présente
                if (json_last_error() === JSON_ERROR_NONE && isset($meta->page)) {
                    $metadata[] = ['page' => $meta->page];
                } else {
                    Log::warning('Failed to decode meta or page not set', ['cmetadata' => $record->cmetadata]);
                }
            } else {
                Log::warning('Failed to decode cmetadata', ['cmetadata' => $record->cmetadata]);
            }
        }

        Log::info('Returning data from findEmbedding', ['context' => $context, 'metadata' => $metadata]);

        return ['context' => $context, 'metadata' => $metadata];
    }








}

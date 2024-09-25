<?php

namespace App\Http\Repository;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Embeddingusers_collections;

class DocumentUserRepository
{
    private string $chat_model = 'mistral-small-latest';
    private string $embedding_model = 'mistral-embed';

    /**
     * La fonction getQueryEmbedding convertit la question de l'utilisateur en un vecteur d'encodage
     * @param string $question
     * @return array
     */
    public function getQueryUsersEmbedding($question): array
    {
        Log::info('Mistral AI question question', ['question' => $question]);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . env('MISTRAL_SECRET'),
        ])->post(env('MISTRAL_EMBEDDING_API_URL'), [
            'model' => $this->embedding_model,
            'input' => [$question],
            'encoding_format' => 'float',
        ]);

        $result = $response->json();
         Log::info('Mistral AI response', ['response' => $response]);

        // Vérifier la présence de la clé 'data' dans la réponse
        if (isset($result['data']) && count($result['data']) > 0) {
            return $result['data'][0]['embedding'];
        } else {
            return [];
        }
    }


}

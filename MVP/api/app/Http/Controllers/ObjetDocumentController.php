<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Jobs\ProcessAIResponseJob;
use App\Http\Repository\DocumentUserRepository; // Utilisez DocumentUserRepository
use App\Models\ObjetDocument;

class ObjetDocumentController extends Controller
{
    protected $chat_model = 'mistral-small-latest'; // Assurez-vous de définir votre modèle ici
    protected $documentUserRepository;

    /**
     * Crée une nouvelle instance du contrôleur.
     *
     * @param  DocumentUserRepository  $documentUserRepository
     * @return void
     */
    public function __construct(DocumentUserRepository $documentUserRepository)
    {
        $this->documentUserRepository = $documentUserRepository;
    }

    public function store(Request $request)
    {
        // Valider les données entrantes
        $validatedData = $request->validate([
            'document_type' => 'required|string',
        ]);

        // Créer le contexte dynamique basé uniquement sur le type de document
        $context = $this->createDynamicContext($validatedData['document_type']);

        // Appeler la fonction askQuestion avec le contexte dynamique
        $response = $this->askQuestion($context);

        // Extraire uniquement les informations nécessaires pour le destinataire
        $ai_response_content = $response['choices'][0]['message']['content']; // Récupérer le contenu de la réponse

        // Créer un nouvel enregistrement dans la base de données avec la réponse de l'IA
        $objetDocument = ObjetDocument::create([
            'document_type' => $validatedData['document_type'],
            'ai_response' => $ai_response_content, // Stocker la réponse de l'IA
        ]);

        // Déclencher la job avec la réponse AI et l'instance DocumentUserRepository
        ProcessAIResponseJob::dispatch($ai_response_content, $this->documentUserRepository);

        // Loguer la réponse de l'IA
        // Log::info('AI Response:', ['response' => $response]);

        return response()->json([
            'message' => 'Document type stored successfully',
            'data' => $objetDocument,
            'ai_response' => $ai_response_content
        ]);
    }

    /**
     * Crée un contexte dynamique basé uniquement sur le type de document.
     *
     * @param  string  $documentType
     * @return array
     */
    protected function createDynamicContext(string $documentType): array
    {
        // Créer un contexte de base avec des instructions détaillées
        $context = [
            ['role' => 'system', 'content' => 'Vous êtes un assistant intelligent pour la génération de documents.'],
            ['role' => 'user', 'content' => "Je veux créer un document de type: $documentType. Pour ce document, pourriez-vous fournir les éléments suivants :"],
            ['role' => 'user', 'content' => "1. Les informations nécessaires à inclure pour le destinataire du document."],
            ['role' => 'user', 'content' => "2. Les informations nécessaires à inclure pour l'expéditeur du document."],
            ['role' => 'user', 'content' => "Merci de détailler les éléments essentiels que l'on doit inclure dans chaque partie pour rendre le document complet et professionnel."]
        ];

        // Ajouter des instructions spécifiques si nécessaire
        if (stripos($documentType, 'en allemand') !== false) {
            $context[] = ['role' => 'system', 'content' => 'L\'utilisateur souhaite que le document soit en allemand.'];
        }

        return $context;
    }

    /**
     * Pose une question à l'IA en utilisant un contexte donné.
     *
     * @param  array  $messages
     * @return array
     */
    public function askQuestion(array $messages)
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . env('MISTRAL_SECRET'),
        ])->post(env('MISTRAL_API_URL'), [
            'model' => $this->chat_model,
            'messages' => $messages,
            'temperature' => 0.7,
            'top_p' => 1,
            'max_tokens' => 512,
            'stream' => false,
            'safe_prompt' => false,
            'random_seed' => 1337,
        ]);

        return $response->json();
    }
}

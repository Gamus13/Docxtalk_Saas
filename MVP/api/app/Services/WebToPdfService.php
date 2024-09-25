<?php

namespace App\Services;

use GuzzleHttp\Client;
use \ConvertApi\ConvertApi;

class WebToPdfService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
        ConvertApi::setApiCredentials(env('CONVERT_API_SECRET'));

    }

    public function convertUrlToPdf($url)
    {
        try {
            $response = $this->client->post('https://v2.convertapi.com/convert/web/to/pdf', [
                'query' => ['Secret' => env('CONVERT_API_SECRET')],
                'json' => [
                    'Parameters' => [
                        ['Name' => 'Url', 'Value' => $url],
                        ['Name' => 'StoreFile', 'Value' => true],
                    ],
                ],
                'headers' => ['Content-Type' => 'application/json'],
            ]);

            $body = json_decode($response->getBody(), true);

            if (!is_array($body)) {
                return ['error' => true, 'message' => 'Invalid response format'];
            }

            if (isset($body['Files'][0]['Url'])) {
                // Télécharger le fichier PDF
                $pdfUrl = $body['Files'][0]['Url'];
                $pdfContent = file_get_contents($pdfUrl);

                // Créez un chemin pour stocker le PDF
                $fileName = basename($pdfUrl);
                $storagePath = storage_path('app/public/pdfs/' . $fileName);

                // Enregistrez le fichier PDF dans le système de fichiers
                file_put_contents($storagePath, $pdfContent);

                return [
                    'success' => true,
                    'message' => 'Conversion réussie',
                    'file_path' => $storagePath,
                    'file_url' => url('storage/pdfs/' . $fileName)
                ];
            }

            return ['error' => true, 'message' => 'Conversion failed'];

        } catch (\Exception $e) {
            return [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }
    }


}

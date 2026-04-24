<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    /**
     * URL du service IA
     */
    private $aiServiceUrl;
    
    /**
     * Timeout pour les requêtes API (en secondes)
     */
    private $timeout = 30;

    public function __construct()
    {
        $this->aiServiceUrl = env('AI_SERVICE_URL', 'http://localhost:5000');
    }

    /**
     * Affiche la page de chat
     */
    public function index()
    {
        return view('chat');
    }

    /**
     * Envoie un message au service IA et retourne la réponse
     */
    public function sendMessage(Request $request)
    {
        try {
            // Validation de la requête
            $request->validate([
                'message' => 'required|string|max:1000'
            ]);

            $message = $request->input('message');
            
            // Journalisation du message
            Log::info('Message envoyé au service IA', [
                'user_id' => auth()->id(),
                'message' => substr($message, 0, 100) . '...'
            ]);

            // Appel au service IA
            $response = Http::timeout($this->timeout)
                ->post($this->aiServiceUrl . '/chat', [
                    'message' => $message
                ]);

            // Gestion des erreurs HTTP
            if ($response->failed()) {
                Log::error('Erreur du service IA', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);

                return response()->json([
                    'error' => 'Service IA temporairement indisponible',
                    'status' => 'service_error'
                ], $response->status());
            }

            $responseData = $response->json();

            // Validation de la réponse
            if (!isset($responseData['response'])) {
                Log::error('Réponse invalide du service IA', [
                    'response' => $responseData
                ]);

                return response()->json([
                    'error' => 'Réponse invalide du service IA',
                    'status' => 'invalid_response'
                ], 500);
            }

            // Journalisation de la réponse
            Log::info('Réponse reçue du service IA', [
                'user_id' => auth()->id(),
                'response_length' => strlen($responseData['response']),
                'status' => $responseData['status'] ?? 'unknown'
            ]);

            return response()->json([
                'response' => $responseData['response'],
                'status' => $responseData['status'] ?? 'success'
            ]);

        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('Exception de connexion au service IA', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'error' => 'Impossible de contacter le service IA. Veuillez réessayer plus tard.',
                'status' => 'connection_error'
            ], 503);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Message invalide',
                'status' => 'validation_error',
                'details' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Erreur inattendue dans ChatController', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'error' => 'Erreur interne du serveur',
                'status' => 'internal_error'
            ], 500);
        }
    }

    /**
     * Vérifie l'état du service IA
     */
    public function healthCheck()
    {
        try {
            $response = Http::timeout(5)->get($this->aiServiceUrl . '/health');
            
            if ($response->successful()) {
                return response()->json([
                    'status' => 'healthy',
                    'service' => $response->json()
                ]);
            }

            return response()->json([
                'status' => 'unhealthy',
                'error' => 'Service IA non répondant'
            ], 503);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'Impossible de contacter le service IA'
            ], 503);
        }
    }

    /**
     * Récupère le schéma de la base de données (pour debug/admin)
     */
    public function getSchema()
    {
        // Vérifier si l'utilisateur est administrateur
        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'error' => 'Non autorisé'
            ], 403);
        }

        try {
            $response = Http::timeout(10)->get($this->aiServiceUrl . '/schema');
            
            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json([
                'error' => 'Impossible de récupérer le schéma'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération du schéma'
            ], 500);
        }
    }
}

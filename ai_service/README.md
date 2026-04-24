# Service IA - Chatbot SQL pour Système de Gestion Scolaire

Micro-service Python/Flask avec agent SQL LangChain intégré à Google Gemini pour interroger la base de données du système de gestion scolaire.

## Architecture

- **Backend IA**: Flask (port 5000) + LangChain + Google Gemini 1.5 Flash
- **Base de données**: MySQL avec connexion via PyMySQL
- **Agent SQL**: LangChain SQL Agent avec requêtes naturelles → SQL

## Installation

1. **Créer l'environnement virtuel**:
```bash
python -m venv venv
source venv/bin/activate  # Sur Windows: venv\Scripts\activate
```

2. **Installer les dépendances**:
```bash
pip install -r requirements.txt
```

3. **Configurer l'environnement**:
```bash
cp .env.example .env
# Éditer .env avec vos configurations
```

4. **Démarrer le service**:
```bash
python app_ai.py
```

Le service sera disponible sur `http://localhost:5000`

## Configuration requise

### Variables d'environnement (.env)

- `DB_HOST`: Hôte de la base de données MySQL (défaut: 127.0.0.1)
- `DB_PORT`: Port MySQL (défaut: 3306)
- `DB_USERNAME`: Utilisateur MySQL (défaut: root)
- `DB_PASSWORD`: Mot de passe MySQL
- `DB_DATABASE`: Nom de la base de données (défaut: school_management_system)
- `GOOGLE_API_KEY**: Clé API Google Gemini (obligatoire)
- `FLASK_DEBUG`: Mode debug Flask (défaut: False)

### Clé API Google Gemini

1. Allez sur [Google AI Studio](https://makersuite.google.com/app/apikey)
2. Créez une nouvelle clé API
3. Ajoutez-la à votre fichier `.env`

## Endpoints API

### POST /chat
Endpoint principal pour le chat avec l'agent IA.

**Request**:
```json
{
    "message": "Combien d'élèves sont inscrits cette année ?"
}
```

**Response**:
```json
{
    "response": "Il y a 150 élèves inscrits cette année...",
    "status": "success"
}
```

### GET /health
Vérification de l'état du service.

**Response**:
```json
{
    "status": "healthy",
    "database": "connected",
    "llm": "initialized",
    "agent": "ready"
}
```

### GET /schema
Retourne le schéma de la base de données (pour debug/admin).

## Capacités de l'Agent

L'agent peut répondre à des questions sur:

- **Élèves**: Nombre, informations, classes, notes
- **Professeurs**: Liste, matières enseignées, classes
- **Classes**: Effectifs, niveaux, programmes
- **Notes**: Moyennes, statistiques, performances
- **Assiduité**: Présences, absences, statistiques
- **Emplois du temps**: Planning, horaires

## Exemples de requêtes

```sql
-- Questions naturelles supportées:
"Combien d'élèves sont dans la classe 3A?"
"Quelle est la moyenne en mathématiques?"
"Liste des professeurs de physique"
"Les élèves absents aujourd'hui"
"Quelles sont les classes de 2ème année?"
```

## Intégration Laravel

Le contrôleur `ChatController.php` dans Laravel fait office de proxy:

```php
// Routes dans Laravel
Route::middleware('auth')->prefix('chat')->name('chat.')->group(function () {
    Route::get('/', [ChatController::class, 'index'])->name('index');
    Route::post('/send', [ChatController::class, 'sendMessage'])->name('send');
    Route::get('/health', [ChatController::class, 'healthCheck'])->name('health');
});
```

## Sécurité

- CORS activé pour les requêtes depuis le domaine Laravel
- Validation des entrées utilisateur
- Protection contre les injections SQL via LangChain
- Middleware d'authentification requis côté Laravel

## Dépannage

### Problèmes courants

1. **"GOOGLE_API_KEY non définie"**:
   - Vérifiez que la clé API est bien dans `.env`
   - Redémarrez le service après modification

2. **"Erreur de connexion à la base de données"**:
   - Vérifiez les identifiants MySQL dans `.env`
   - Assurez-vous que MySQL est en cours d'exécution

3. **"Agent non disponible"**:
   - Vérifiez la clé API Gemini
   - Consultez les logs du service

### Logs

Les logs sont activés par défaut. Pour voir les erreurs:

```bash
# Le service affiche les logs directement dans la console
python app_ai.py
```

## Développement

Pour le développement avec Flask debug:

```bash
export FLASK_DEBUG=True
python app_ai.py
```

Le service se rechargera automatiquement lors des modifications.

## Production

Pour la production:

1. Utilisez un WSGI server (Gunicorn, uWSGI)
2. Configurez un reverse proxy (Nginx)
3. Désactivez le mode debug
4. Utilisez HTTPS

```bash
gunicorn -w 4 -b 0.0.0.0:5000 app_ai:app
```

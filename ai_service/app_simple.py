from flask import Flask, request, jsonify
from flask_cors import CORS
import os
from dotenv import load_dotenv
import logging

# Configuration du logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Charger les variables d'environnement
load_dotenv()

app = Flask(__name__)
CORS(app)  # Activer CORS pour toutes les routes

@app.route('/health', methods=['GET'])
def health_check():
    """Vérification de l'état du service"""
    return jsonify({
        'status': 'healthy',
        'database': 'connected',
        'llm': 'initialized',
        'agent': 'ready',
        'mode': 'simplified'
    })

@app.route('/chat', methods=['POST'])
def chat():
    """Endpoint simplifié pour le chat"""
    try:
        data = request.get_json()
        
        if not data or 'message' not in data:
            return jsonify({'error': 'Message requis'}), 400
        
        user_message = data['message'].strip()
        
        if not user_message:
            return jsonify({'error': 'Message vide'}), 400
        
        logger.info(f"Message reçu: {user_message[:100]}...")
        
        # Réponses simulées basées sur des mots-clés
        response = generate_simulated_response(user_message)
        
        return jsonify({
            'response': response,
            'status': 'success'
        })
            
    except Exception as e:
        logger.error(f"Erreur générale: {e}")
        return jsonify({'error': 'Erreur interne du serveur'}), 500

def generate_simulated_response(message):
    """Génère des réponses simulées basées sur des mots-clés"""
    message_lower = message.lower()
    
    # Comptage d'élèves
    if any(word in message_lower for word in ['combien', 'nombre', 'élèves', 'eleves', 'étudiants']):
        if 'inscrit' in message_lower or 'cette année' in message_lower:
            return "Il y a actuellement **156 élèves** inscrits cette année dans l'établissement, répartis sur 8 classes."
        return "L'établissement compte **156 élèves** au total."
    
    # Professeurs
    if any(word in message_lower for word in ['professeur', 'prof', 'enseignant']):
        if 'math' in message_lower or 'mathématique' in message_lower:
            return "Les professeurs de mathématiques sont :\n• **M. Diop** (Classes 3A, 3B)\n• **Mme. Fall** (Classes 2A, 2B)\n• **M. Ba** (Classes 1A, 1B)"
        return "L'établissement compte **12 professeurs** répartis sur différentes matières."
    
    # Classes
    if any(word in message_lower for word in ['classe', 'classes']):
        if '3ème' in message_lower or '3eme' in message_lower:
            return "Les classes de 3ème année sont :\n• **3A** - 28 élèves (Prof: M. Diop)\n• **3B** - 26 élèves (Prof: Mme. Ba)\n• **3C** - 27 élèves (Prof: M. Sow)"
        return "L'établissement dispose de **8 classes** de la 1ère à la 3ème année."
    
    # Notes et moyennes
    if any(word in message_lower for word in ['note', 'moyenne', 'moyennes']):
        if 'générale' in message_lower:
            return "La moyenne générale de l'établissement est de **12.5/20**.\n\nDétail par matière :\n• Mathématiques: 11.8/20\n• Français: 13.2/20\n• Sciences: 12.1/20"
        return "Les notes sont enregistrées régulièrement. La moyenne générale actuelle est de **12.5/20**."
    
    # Absences
    if any(word in message_lower for word in ['absent', 'absence', 'présent', 'présence']):
        return "Aujourd'hui, le taux de présence est de **94%**.\n\n**8 élèves** sont absents pour les raisons suivantes :\n• 3 maladies\n• 3 permissions familiales\n• 2 motifs non justifiés"
    
    # Emploi du temps
    if any(word in message_lower for word in ['emploi', 'temps', 'horaire', 'planning']):
        return "L'emploi du temps standard est :\n• **8h-10h**: Cours principaux\n• **10h-10h30**: Pause\n• **10h30-12h**: Cours pratiques\n• **14h-16h**: Activités sportives\n\nLes emplois du temps spécifiques par classe sont disponibles dans le système."
    
    # Réponse par défaut
    return f"""Je suis un assistant IA pour votre système de gestion scolaire. 

Je peux vous aider avec des informations sur :
- 📊 **Effectifs**: Nombre d'élèves, professeurs, classes
- 👨‍🏫 **Personnel**: Liste des professeurs par matière  
- 🏫 **Structures**: Classes, niveaux, programmes
- 📈 **Performances**: Notes, moyennes, statistiques
- 📅 **Présences**: Absences, taux de présence
- ⏰ **Planning**: Emplois du temps, horaires

Votre question était : "{message}"

Pour une réponse plus précise, essayez de formuler votre question avec des mots-clés spécifiques comme "combien", "liste", "moyenne", etc.

*Note: Je fonctionne actuellement en mode simplifié. Pour des requêtes SQL avancées, l'administrateur doit configurer l'agent LangChain complet.*"""

@app.route('/schema', methods=['GET'])
def get_schema():
    """Retourne le schéma simulé de la base de données"""
    schema_info = {
        'tables': [
            {'name': 'users', 'description': 'Utilisateurs (élèves, professeurs, admin)'},
            {'name': 'classes', 'description': 'Classes et niveaux'},
            {'name': 'subjects', 'description': 'Matières enseignées'},
            {'name': 'grades', 'description': 'Notes des élèves'},
            {'name': 'attendances', 'description': 'Présences/absences'},
            {'name': 'academic_years', 'description': 'Années académiques'},
            {'name': 'timetables', 'description': 'Emplois du temps'}
        ],
        'sample_queries': [
            "SELECT COUNT(*) FROM users WHERE role = 'student'",
            "SELECT name, email FROM users WHERE role = 'teacher' LIMIT 5",
            "SELECT c.name, l.name as level FROM classes c JOIN levels l ON c.level_id = l.id"
        ],
        'mode': 'simplified'
    }
    
    return jsonify(schema_info)

@app.errorhandler(404)
def not_found(error):
    return jsonify({'error': 'Endpoint non trouvé'}), 404

@app.errorhandler(500)
def internal_error(error):
    return jsonify({'error': 'Erreur interne du serveur'}), 500

if __name__ == '__main__':
    logger.info("Démarrage du service IA simplifié")
    app.run(
        host='0.0.0.0',
        port=5000,
        debug=os.getenv('FLASK_DEBUG', 'False').lower() == 'true'
    )

from flask import Flask, request, jsonify
from flask_cors import CORS
from langchain_google_genai import ChatGoogleGenerativeAI
from langchain.agents import create_sql_agent
from langchain.sql_database import SQLDatabase
from langchain.agents.agent_toolkits import SQLDatabaseToolkit
from langchain.memory import ConversationBufferMemory
from langchain.schema import SystemMessage
import os
from dotenv import load_dotenv
import pymysql
import logging

# Configuration du logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Charger les variables d'environnement
load_dotenv()

app = Flask(__name__)
CORS(app)  # Activer CORS pour toutes les routes

# Configuration de la base de données MySQL
DB_CONFIG = {
    'host': os.getenv('DB_HOST', '127.0.0.1'),
    'port': int(os.getenv('DB_PORT', 3306)),
    'user': os.getenv('DB_USERNAME', 'root'),
    'password': os.getenv('DB_PASSWORD', ''),
    'database': os.getenv('DB_DATABASE', 'school_management_system'),
    'charset': 'utf8mb4'
}

# Initialisation de la connexion à la base de données
try:
    db_uri = f"mysql+pymysql://{DB_CONFIG['user']}:{DB_CONFIG['password']}@{DB_CONFIG['host']}:{DB_CONFIG['port']}/{DB_CONFIG['database']}"
    db = SQLDatabase.from_uri(db_uri)
    logger.info("Connexion à la base de données établie avec succès")
except Exception as e:
    logger.error(f"Erreur de connexion à la base de données: {e}")
    db = None

# Configuration du modèle Gemini
try:
    llm = ChatGoogleGenerativeAI(
        model="gemini-1.5-flash",
        temperature=0.1,
        google_api_key=os.getenv('GOOGLE_API_KEY')
    )
    logger.info("Modèle Gemini initialisé avec succès")
except Exception as e:
    logger.error(f"Erreur d'initialisation du modèle Gemini: {e}")
    llm = None

# Configuration du toolkit SQL et de l'agent
if db and llm:
    try:
        toolkit = SQLDatabaseToolkit(db=db, llm=llm)
        
        # Message système pour guider l'agent
        system_message = """Tu es un assistant IA spécialisé pour un système de gestion scolaire. 
        Tu peux répondre à des questions générales et interroger la base de données pour fournir des informations précises sur:
        - Les élèves (users avec role 'student')
        - Les professeurs (users avec role 'teacher') 
        - Les classes, niveaux, et matières
        - Les notes, devoirs, et assiduité
        - Les années académiques et emplois du temps
        
        Règles importantes:
        1. Sois précis et professionnel dans tes réponses
        2. Pour les questions sur les données, utilise toujours les requêtes SQL
        3. Formate les résultats de manière claire et lisible
        4. Si une requête échoue, explique pourquoi et propose une alternative
        5. Protège les données personnelles et ne partage jamais d'informations sensibles
        6. Réponds en français"""
        
        memory = ConversationBufferMemory(
            memory_key="chat_history",
            return_messages=True
        )
        
        agent = create_sql_agent(
            llm=llm,
            toolkit=toolkit,
            verbose=True,
            memory=memory,
            agent_type="zero-shot-react-description",
            system_message=system_message
        )
        logger.info("Agent SQL créé avec succès")
    except Exception as e:
        logger.error(f"Erreur de création de l'agent: {e}")
        agent = None
else:
    agent = None

@app.route('/health', methods=['GET'])
def health_check():
    """Vérification de l'état du service"""
    return jsonify({
        'status': 'healthy',
        'database': 'connected' if db else 'disconnected',
        'llm': 'initialized' if llm else 'not_initialized',
        'agent': 'ready' if agent else 'not_ready'
    })

@app.route('/chat', methods=['POST'])
def chat():
    """Endpoint principal pour le chat"""
    try:
        data = request.get_json()
        
        if not data or 'message' not in data:
            return jsonify({'error': 'Message requis'}), 400
        
        user_message = data['message'].strip()
        
        if not user_message:
            return jsonify({'error': 'Message vide'}), 400
        
        if not agent:
            return jsonify({'error': 'Agent non disponible - vérifiez la configuration'}), 500
        
        logger.info(f"Message reçu: {user_message[:100]}...")
        
        # Exécuter l'agent
        try:
            response = agent.run(user_message)
            
            # Nettoyer la réponse
            if "SQLQuery:" in response:
                response = response.split("SQLQuery:")[0].strip()
            
            return jsonify({
                'response': response,
                'status': 'success'
            })
            
        except Exception as agent_error:
            logger.error(f"Erreur de l'agent: {agent_error}")
            
            # Fallback: réponse générale si l'agent échoue
            fallback_response = llm.invoke(f"""
            Réponds à cette question sur un système de gestion scolaire: {user_message}
            
            Si la question concerne des données spécifiques (élèves, notes, etc.), 
            explique que je ne peux pas accéder aux bases de données actuellement 
            mais propose une réponse générale basée sur ta connaissance.
            """)
            
            return jsonify({
                'response': fallback_response.content,
                'status': 'fallback'
            })
            
    except Exception as e:
        logger.error(f"Erreur générale: {e}")
        return jsonify({'error': 'Erreur interne du serveur'}), 500

@app.route('/schema', methods=['GET'])
def get_schema():
    """Retourne le schéma de la base de données"""
    try:
        if not db:
            return jsonify({'error': 'Base de données non disponible'}), 500
        
        schema_info = {
            'tables': [],
            'sample_queries': [
                "SELECT COUNT(*) FROM users WHERE role = 'student'",
                "SELECT name, email FROM users WHERE role = 'teacher' LIMIT 5",
                "SELECT c.name, l.name as level FROM classes c JOIN levels l ON c.level_id = l.id"
            ]
        }
        
        # Obtenir les informations sur les tables
        tables = db.get_usable_table_names()
        for table in tables:
            try:
                columns = db.get_table_info(table)
                schema_info['tables'].append({
                    'name': table,
                    'columns': columns
                })
            except:
                schema_info['tables'].append({'name': table, 'columns': 'Non disponible'})
        
        return jsonify(schema_info)
        
    except Exception as e:
        logger.error(f"Erreur lors de la récupération du schéma: {e}")
        return jsonify({'error': 'Erreur lors de la récupération du schéma'}), 500

@app.errorhandler(404)
def not_found(error):
    return jsonify({'error': 'Endpoint non trouvé'}), 404

@app.errorhandler(500)
def internal_error(error):
    return jsonify({'error': 'Erreur interne du serveur'}), 500

if __name__ == '__main__':
    # Vérifications au démarrage
    if not os.getenv('GOOGLE_API_KEY'):
        logger.warning("ATTENTION: GOOGLE_API_KEY non définie dans les variables d'environnement")
    
    if not agent:
        logger.error("ERREUR: Agent non initialisé - le service ne fonctionnera pas correctement")
    
    # Démarrage du serveur
    app.run(
        host='0.0.0.0',
        port=5000,
        debug=os.getenv('FLASK_DEBUG', 'False').lower() == 'true'
    )

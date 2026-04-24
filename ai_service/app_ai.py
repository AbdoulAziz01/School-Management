from flask import Flask, request, jsonify
from flask_cors import CORS
from langchain_google_genai import ChatGoogleGenerativeAI
from langchain.agents import create_agent, AgentExecutor
from langchain.sql_database import SQLDatabase
from langchain.agents.agent_toolkits import SQLDatabaseToolkit
from langchain.memory import ConversationBufferMemory
from langchain.schema import SystemMessage, HumanMessage, AIMessage
from langchain import hub
import os
from dotenv import load_dotenv
import pymysql
import logging
import sys

# Configuration du logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Charger les variables d'environnement
load_dotenv()

app = Flask(__name__)
CORS(app)

# Vérification critique de la clé API
GOOGLE_API_KEY = os.getenv('GOOGLE_API_KEY')
if not GOOGLE_API_KEY:
    logger.error("ERREUR CRITIQUE: GOOGLE_API_KEY non définie dans les variables d'environnement")
    logger.error("Veuillez définir GOOGLE_API_KEY dans votre fichier .env")
    sys.exit("ERREUR: GOOGLE_API_KEY requise pour démarrer le service")

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
    logger.info("✅ Connexion à la base de données établie avec succès")
    
    # Test de connexion
    tables = db.get_usable_table_names()
    logger.info(f"📊 Tables trouvées: {len(tables)} - {tables[:5]}")
    
except Exception as e:
    logger.error(f"❌ Erreur de connexion à la base de données: {e}")
    sys.exit("ERREUR: Impossible de se connecter à la base de données")

# Configuration du modèle Gemini avec validation
try:
    llm = ChatGoogleGenerativeAI(
        model="gemini-1.5-flash",
        temperature=0.1,
        google_api_key=GOOGLE_API_KEY
    )
    
    # Test du modèle
    test_response = llm.invoke("Test")
    logger.info("✅ Modèle Gemini initialisé et testé avec succès")
    
except Exception as e:
    logger.error(f"❌ Erreur d'initialisation du modèle Gemini: {e}")
    logger.error("Vérifiez votre clé API Gemini")
    sys.exit("ERREUR: Impossible d'initialiser le modèle Gemini")

# Configuration du toolkit SQL et de l'agent
try:
    toolkit = SQLDatabaseToolkit(db=db, llm=llm)
    
    # Message système conversationnel pour guider l'agent
    system_message = """Tu es un assistant IA intelligent et conversationnel pour un système de gestion scolaire sénégalais.

RÈGLES IMPORTANTES:
1. Sois naturel et conversationnel. Réponds "Bonjour !" de manière amicale si on te dit bonjour.
2. Pour les questions sur les données, utilise TOUJOURS les requêtes SQL avec le toolkit fourni.
3. Sois précis et donne des réponses concrètes basées sur les données réelles.
4. Quand on demande "le meilleur élève" ou "la meilleure élève", fais un JOIN entre les tables users, grades, et classes pour trouver la personne avec la meilleure moyenne.
5. Formate les nombres avec 2 décimales pour les notes.
6. Réponds en français de manière naturelle.

STRUCTURE DES TABLES:
- users: contient les élèves (role='student' ou 'eleve') et professeurs (role='teacher' ou 'professeur')
- grades: contient les notes des élèves
- classes: contient les informations sur les classes
- subjects: contient les matières

EXEMPLES DE RÉPONSES:
- "Bonjour !" → "Bonjour ! Comment puis-je vous aider aujourd'hui ?"
- "Qui est le meilleur élève de seconde S ?" → "Le meilleur élève de seconde S est [Nom] avec une moyenne de [Note]/20"

IMPORTANT: Ne donne jamais de réponses génériques. Base-toi toujours sur les données réelles de la base."""
    
    # Configuration de la mémoire conversationnelle
    memory = ConversationBufferMemory(
        memory_key="chat_history",
        return_messages=True
    )
    
    # Création de l'agent SQL conversationnel avec la nouvelle API
    try:
        # Utiliser un prompt template pour l'agent
        prompt = hub.pull("hwchase17/react-chat")
        
        # Créer l'agent avec le toolkit
        agent = create_agent(
            llm=llm,
            tools=toolkit.get_tools(),
            prompt=prompt
        )
        
        # Créer l'AgentExecutor
        agent_executor = AgentExecutor(
            agent=agent,
            tools=toolkit.get_tools(),
            verbose=True,
            memory=memory,
            handle_parsing_errors=True
        )
        
        logger.info("🤖 Agent SQL conversationnel créé avec succès")
        
    except Exception as e:
        logger.error(f"❌ Erreur de création de l'agent: {e}")
        sys.exit("ERREUR: Impossible de créer l'agent SQL")
    
except Exception as e:
    logger.error(f"❌ Erreur de création de l'agent: {e}")
    sys.exit("ERREUR: Impossible de créer l'agent SQL")

@app.route('/health', methods=['GET'])
def health_check():
    """Vérification de l'état du service"""
    return jsonify({
        'status': 'healthy',
        'database': 'connected',
        'llm': 'initialized',
        'agent': 'ready',
        'mode': 'langchain_sql_agent'
    })

@app.route('/chat', methods=['POST'])
def chat():
    """Endpoint principal pour le chat avec agent SQL"""
    try:
        data = request.get_json()
        
        if not data or 'message' not in data:
            return jsonify({'error': 'Message requis'}), 400
        
        user_message = data['message'].strip()
        
        if not user_message:
            return jsonify({'error': 'Message vide'}), 400
        
        logger.info(f"📨 Message reçu: {user_message}")
        
        # Exécuter l'agent LangChain
        try:
            response = agent_executor.invoke({"input": user_message})
            
            # Extraire la réponse
            if "output" in response:
                response_text = response["output"]
            else:
                response_text = str(response)
            
            # Nettoyer la réponse pour enlever les artefacts SQL
            if "SQLQuery:" in response_text:
                response_text = response_text.split("SQLQuery:")[0].strip()
            
            logger.info(f"✅ Réponse générée: {response_text[:100]}...")
            
            return jsonify({
                'response': response_text,
                'status': 'success'
            })
            
        except Exception as agent_error:
            logger.error(f"❌ Erreur de l'agent: {agent_error}")
            
            # En cas d'erreur SQL, essayer une réponse conversationnelle simple
            if any(word in user_message.lower() for word in ['bonjour', 'salut', 'hello', 'hi']):
                return jsonify({
                    'response': 'Bonjour ! Comment puis-je vous aider avec votre système de gestion scolaire ?',
                    'status': 'success'
                })
            
            return jsonify({
                'error': f'Erreur lors du traitement: {str(agent_error)}',
                'status': 'agent_error'
            }), 500
            
    except Exception as e:
        logger.error(f"❌ Erreur générale: {e}")
        return jsonify({'error': 'Erreur interne du serveur'}), 500

@app.route('/schema', methods=['GET'])
def get_schema():
    """Retourne le schéma détaillé de la base de données"""
    try:
        schema_info = {
            'tables': [],
            'sample_queries': [
                "SELECT COUNT(*) FROM users WHERE role IN ('student', 'eleve')",
                "SELECT u.name, AVG(g.grade) as moyenne FROM users u JOIN grades g ON u.id = g.user_id WHERE u.role = 'student' GROUP BY u.id ORDER BY moyenne DESC LIMIT 5",
                "SELECT c.name, COUNT(u.id) as eleves FROM classes c LEFT JOIN users u ON c.id = u.class_id GROUP BY c.id"
            ]
        }
        
        # Obtenir les informations détaillées sur les tables
        tables = db.get_usable_table_names()
        for table in tables:
            try:
                # Récupérer le schéma de la table
                table_info = db.get_table_info(table)
                
                # Compter les enregistrements
                try:
                    count_result = db.run(f"SELECT COUNT(*) as count FROM {table}")
                    count = int(count_result.split()[0]) if count_result else 0
                except:
                    count = 0
                
                schema_info['tables'].append({
                    'name': table,
                    'columns': table_info,
                    'count': count
                })
            except Exception as e:
                logger.warning(f"⚠️ Erreur pour la table {table}: {e}")
                schema_info['tables'].append({
                    'name': table,
                    'columns': 'Non disponible',
                    'count': 0
                })
        
        return jsonify(schema_info)
        
    except Exception as e:
        logger.error(f"❌ Erreur lors de la récupération du schéma: {e}")
        return jsonify({'error': 'Erreur lors de la récupération du schéma'}), 500

@app.route('/test', methods=['POST'])
def test_query():
    """Endpoint de test pour vérifier une requête spécifique"""
    try:
        data = request.get_json()
        query = data.get('query', 'SELECT COUNT(*) FROM users')
        
        result = db.run(query)
        
        return jsonify({
            'query': query,
            'result': result,
            'status': 'success'
        })
        
    except Exception as e:
        return jsonify({
            'error': str(e),
            'query': query,
            'status': 'error'
        }), 500

@app.errorhandler(404)
def not_found(error):
    return jsonify({'error': 'Endpoint non trouvé'}), 404

@app.errorhandler(500)
def internal_error(error):
    return jsonify({'error': 'Erreur interne du serveur'}), 500

if __name__ == '__main__':
    logger.info("🚀 Démarrage du service IA avec LangChain SQL Agent")
    logger.info(f"🔗 URL: http://localhost:5000")
    logger.info("💡 Le service utilise un véritable agent SQL conversationnel")
    
    app.run(
        host='0.0.0.0',
        port=5000,
        debug=os.getenv('FLASK_DEBUG', 'False').lower() == 'true'
    )

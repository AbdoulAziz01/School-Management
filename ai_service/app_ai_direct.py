from flask import Flask, request, jsonify
from flask_cors import CORS
from langchain_google_genai import ChatGoogleGenerativeAI
from langchain_community.sql_database import SQLDatabase
from langchain_community.agent_toolkits import SQLDatabaseToolkit
from langchain.chains import LLMChain
from langchain.prompts import PromptTemplate
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
if not GOOGLE_API_KEY or GOOGLE_API_KEY == 'votre_cle_api_gemini_ici':
    logger.error("ERREUR CRITIQUE: GOOGLE_API_KEY non définie ou invalide")
    logger.error("Veuillez définir une vraie clé API Google Gemini dans votre fichier .env")
    sys.exit("ERREUR: GOOGLE_API_KEY valide requise pour démarrer le service")

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
    
    # Test de connexion et récupération des tables
    tables = db.get_usable_table_names()
    logger.info(f"📊 Tables trouvées: {len(tables)} - {tables}")
    
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

# Configuration du toolkit SQL
try:
    toolkit = SQLDatabaseToolkit(db=db, llm=llm)
    logger.info("🔧 Toolkit SQL initialisé")
    
except Exception as e:
    logger.error(f"❌ Erreur d'initialisation du toolkit: {e}")
    sys.exit("ERREUR: Impossible d'initialiser le toolkit SQL")

# Template pour les requêtes SQL
SQL_QUERY_TEMPLATE = PromptTemplate(
    input_variables=["question", "schema"],
    template="""Tu es un assistant expert en SQL pour une base de données de gestion scolaire.

BASE DE DONNÉES:
{schema}

QUESTION: {question}

Génère UNE SEULE requête SQL valide pour répondre à cette question.
Règles importantes:
1. Utilise uniquement les tables mentionnées dans le schéma
2. Pour les "meilleur/meilleure élève", fais un JOIN entre users, grades, et classes
3. Utilise AVG() pour les moyennes et ORDER BY DESC pour trouver le meilleur
4. La réponse doit être UNIQUEMENT la requête SQL, sans explication
5. Utilise les noms de tables exacts comme dans le schéma

Requête SQL:"""
)

# Template pour les réponses conversationnelles
RESPONSE_TEMPLATE = PromptTemplate(
    input_variables=["question", "sql_result"],
    template="""Question: {question}

Résultat SQL: {sql_result}

En tant qu'assistant conversationnel pour un système de gestion scolaire, réponds de manière naturelle et amicale à cette question.
Si c'est un bonjour, réponds simplement "Bonjour ! Comment puis-je vous aider ?"
Si on demande le meilleur élève, formate la réponse comme: "Le meilleur élève de [classe] est [nom] avec une moyenne de [moyenne]/20"
Sois précis et donne des réponses basées sur les données réelles.

Réponse:"""
)

def execute_sql_query(question):
    """Exécute une requête SQL basée sur la question"""
    try:
        # Générer la requête SQL
        schema_info = db.get_table_info()
        prompt = SQL_QUERY_TEMPLATE.format(
            question=question,
            schema=schema_info
        )
        
        sql_query = llm.invoke(prompt).content.strip()
        
        # Nettoyer la requête
        if sql_query.startswith('```sql'):
            sql_query = sql_query[6:]
        if sql_query.endswith('```'):
            sql_query = sql_query[:-3]
        sql_query = sql_query.strip()
        
        logger.info(f"🔍 Requête SQL générée: {sql_query}")
        
        # Exécuter la requête
        result = db.run(sql_query)
        logger.info(f"📊 Résultat SQL: {result}")
        
        return sql_query, result
        
    except Exception as e:
        logger.error(f"❌ Erreur SQL: {e}")
        raise e

def generate_conversational_response(question, sql_result):
    """Génère une réponse conversationnelle basée sur le résultat"""
    try:
        prompt = RESPONSE_TEMPLATE.format(
            question=question,
            sql_result=sql_result
        )
        
        response = llm.invoke(prompt).content.strip()
        return response
        
    except Exception as e:
        logger.error(f"❌ Erreur génération réponse: {e}")
        return f"J'ai trouvé ces informations: {sql_result}"

@app.route('/health', methods=['GET'])
def health_check():
    """Vérification de l'état du service"""
    return jsonify({
        'status': 'healthy',
        'database': 'connected',
        'llm': 'initialized',
        'agent': 'ready',
        'mode': 'direct_sql_agent'
    })

@app.route('/chat', methods=['POST'])
def chat():
    """Endpoint principal pour le chat avec agent SQL direct"""
    try:
        data = request.get_json()
        
        if not data or 'message' not in data:
            return jsonify({'error': 'Message requis'}), 400
        
        user_message = data['message'].strip()
        
        if not user_message:
            return jsonify({'error': 'Message vide'}), 400
        
        logger.info(f"📨 Message reçu: {user_message}")
        
        # Vérifier si c'est une salutation simple
        if any(word in user_message.lower() for word in ['bonjour', 'salut', 'hello', 'hi', 'bonsoir']):
            return jsonify({
                'response': 'Bonjour ! Comment puis-je vous aider avec votre système de gestion scolaire ?',
                'status': 'success'
            })
        
        # Exécuter la requête SQL et générer la réponse
        try:
            sql_query, sql_result = execute_sql_query(user_message)
            conversational_response = generate_conversational_response(user_message, sql_result)
            
            logger.info(f"✅ Réponse générée: {conversational_response[:100]}...")
            
            return jsonify({
                'response': conversational_response,
                'status': 'success',
                'sql_query': sql_query,
                'sql_result': sql_result
            })
            
        except Exception as sql_error:
            logger.error(f"❌ Erreur SQL: {sql_error}")
            
            # En cas d'erreur SQL, répondre avec le LLM directement
            try:
                fallback_prompt = f"""Question: {user_message}

Je suis un assistant IA pour un système de gestion scolaire. Je ne peux pas accéder à la base de données actuellement.
Réponds de manière utile si possible, ou explique que j'ai besoin d'accéder aux données.

Réponse:"""
                
                fallback_response = llm.invoke(fallback_prompt).content.strip()
                return jsonify({
                    'response': fallback_response,
                    'status': 'fallback'
                })
                
            except Exception as fallback_error:
                return jsonify({
                    'error': f'Erreur lors du traitement: {str(sql_error)}',
                    'status': 'error'
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
                table_info = db.get_table_info(table)
                
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
    logger.info("🚀 Démarrage du service IA avec Agent SQL Direct")
    logger.info(f"🔗 URL: http://localhost:5000")
    logger.info("💡 Le service utilise un véritable agent SQL avec Gemini")
    
    app.run(
        host='0.0.0.0',
        port=5000,
        debug=os.getenv('FLASK_DEBUG', 'False').lower() == 'true'
    )

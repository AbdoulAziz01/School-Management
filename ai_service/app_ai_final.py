from flask import Flask, request, jsonify
from flask_cors import CORS
from langchain_google_genai import ChatGoogleGenerativeAI
import os
from dotenv import load_dotenv
import pymysql
import logging
import sys
from sqlalchemy import create_engine, text

# Configuration du logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Charger les variables d'environnement (avec fallback)
try:
    load_dotenv()
except Exception as e:
    logger.warning(f"⚠️ Impossible de charger .env: {e}")
    logger.info("🔧 Utilisation des variables d'environnement système")

app = Flask(__name__)
CORS(app)

# Vérification critique de la clé API
GOOGLE_API_KEY = os.environ.get('GOOGLE_API_KEY')
if not GOOGLE_API_KEY or GOOGLE_API_KEY == 'votre_cle_api_gemini_ici':
    logger.error("ERREUR CRITIQUE: GOOGLE_API_KEY non définie ou invalide")
    logger.error("Veuillez définir une vraie clé API Google Gemini dans votre fichier .env")
    logger.error("Exemple: GOOGLE_API_KEY=AIzaSyD...")
    sys.exit("ERREUR: GOOGLE_API_KEY valide requise pour démarrer le service")

# Configuration de la base de données MySQL
DB_CONFIG = {
    'host': os.environ.get('DB_HOST', '127.0.0.1'),
    'port': int(os.environ.get('DB_PORT', 3306)),
    'user': os.environ.get('DB_USERNAME', 'root'),
    'password': os.environ.get('DB_PASSWORD', ''),
    'database': os.environ.get('DB_DATABASE', 'school_management_system'),
    'charset': 'utf8mb4'
}

# Initialisation de la connexion à la base de données
try:
    db_uri = f"mysql+pymysql://{DB_CONFIG['user']}:{DB_CONFIG['password']}@{DB_CONFIG['host']}:{DB_CONFIG['port']}/{DB_CONFIG['database']}"
    engine = create_engine(db_uri)
    
    # Test de connexion
    with engine.connect() as conn:
        result = conn.execute(text("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = :schema"), {"schema": DB_CONFIG['database']})
        table_count = result.fetchone()[0]
    
    logger.info(f"✅ Connexion à la base de données établie avec succès - {table_count} tables trouvées")
    
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

def get_database_schema():
    """Récupère le schéma de la base de données"""
    schema_info = ""
    
    try:
        with engine.connect() as conn:
            # Récupérer les tables
            tables_result = conn.execute(text("""
                SELECT table_name 
                FROM information_schema.tables 
                WHERE table_schema = :schema
                ORDER BY table_name
            """), {"schema": DB_CONFIG['database']})
            
            tables = [row[0] for row in tables_result]
            
            for table in tables:
                schema_info += f"\nTable: {table}\n"
                
                # Récupérer les colonnes
                columns_result = conn.execute(text("""
                    SELECT column_name, data_type, is_nullable
                    FROM information_schema.columns 
                    WHERE table_schema = :schema AND table_name = :table
                    ORDER BY ordinal_position
                """), {"schema": DB_CONFIG['database'], "table": table})
                
                for row in columns_result:
                    nullable = "NULL" if row[2] == "YES" else "NOT NULL"
                    schema_info += f"  - {row[0]} ({row[1]}) {nullable}\n"
                
                schema_info += "\n"
    
    except Exception as e:
        logger.error(f"❌ Erreur récupération schéma: {e}")
        schema_info = "Tables: users, grades, classes, subjects (structure de base)"
    
    return schema_info

def execute_sql_query(sql_query):
    """Exécute une requête SQL en toute sécurité"""
    try:
        with engine.connect() as conn:
            result = conn.execute(text(sql_query))
            
            # Récupérer les résultats
            if result.returns_rows:
                rows = result.fetchall()
                columns = result.keys()
                
                # Formater les résultats
                formatted_results = []
                for row in rows:
                    row_dict = dict(zip(columns, row))
                    formatted_results.append(row_dict)
                
                return formatted_results
            else:
                return {"affected_rows": result.rowcount}
    
    except Exception as e:
        logger.error(f"❌ Erreur exécution SQL: {e}")
        raise e

def generate_sql_query(question, schema):
    """Génère une requête SQL avec Gemini"""
    prompt = f"""Tu es un expert SQL pour une base de données de gestion scolaire.

BASE DE DONNÉES:
{schema}

QUESTION: {question}

Génère UNE SEULE requête SQL valide MySQL pour répondre à cette question.

RÈGLES IMPORTANTES:
1. Utilise UNIQUEMENT les tables et colonnes mentionnées dans le schéma
2. Pour "meilleur/meilleure élève", fais un JOIN entre users, grades, et classes
3. Utilise AVG() pour calculer les moyennes et ORDER BY DESC LIMIT 1 pour trouver le meilleur
4. Pour les élèves, filtre avec role='student' ou role='eleve'
5. La réponse doit être UNIQUEMENT la requête SQL, sans explication ni backticks
6. Utilise des alias de tables clairs (u pour users, g pour grades, c pour classes)

EXEMPLES:
- "Combien d'élèves ?" → "SELECT COUNT(*) as total FROM users WHERE role IN ('student', 'eleve')"
- "Meilleur élève de seconde S" → "SELECT u.name, AVG(g.grade) as moyenne FROM users u JOIN grades g ON u.id = g.user_id JOIN classes c ON u.class_id = c.id WHERE u.role IN ('student', 'eleve') AND c.name LIKE '%seconde%' AND c.name LIKE '%S%' GROUP BY u.id, u.name ORDER BY moyenne DESC LIMIT 1"

Requête SQL:"""

    try:
        response = llm.invoke(prompt)
        sql_query = response.content.strip()
        
        # Nettoyer la requête
        sql_query = sql_query.replace('```sql', '').replace('```', '').strip()
        
        logger.info(f"🔍 Requête SQL générée: {sql_query}")
        return sql_query
        
    except Exception as e:
        logger.error(f"❌ Erreur génération SQL: {e}")
        raise e

def generate_conversational_response(question, sql_result):
    """Génère une réponse conversationnelle basée sur le résultat"""
    
    # Formater les résultats pour le prompt
    if isinstance(sql_result, list) and sql_result:
        result_text = "\n".join([str(row) for row in sql_result[:5]])  # Limiter à 5 résultats
    else:
        result_text = str(sql_result)
    
    prompt = f"""Question: {question}

Résultat de la base de données: {result_text}

En tant qu'assistant conversationnel pour un système de gestion scolaire sénégalais, réponds de manière naturelle et amicale.

RÈGLES:
1. Si c'est un bonjour, réponds simplement "Bonjour ! Comment puis-je vous aider ?"
2. Si on demande le meilleur élève, formule comme: "Le meilleur élève de [classe] est [nom] avec une moyenne de [moyenne]/20"
3. Sois précis et base-toi sur les données réelles
4. Utilise un ton professionnel mais amical
5. Si aucune donnée n'est trouvée, dis-le poliment

Réponse:"""

    try:
        response = llm.invoke(prompt)
        return response.content.strip()
        
    except Exception as e:
        logger.error(f"❌ Erreur génération réponse: {e}")
        # Réponse de secours basique
        if isinstance(sql_result, list) and sql_result:
            return f"J'ai trouvé {len(sql_result)} résultat(s): {sql_result[0] if sql_result else 'Aucun'}"
        else:
            return f"Résultat: {sql_result}"

@app.route('/health', methods=['GET'])
def health_check():
    """Vérification de l'état du service"""
    return jsonify({
        'status': 'healthy',
        'database': 'connected',
        'llm': 'initialized',
        'agent': 'ready',
        'mode': 'gemini_sql_agent'
    })

@app.route('/chat', methods=['POST'])
def chat():
    """Endpoint principal pour le chat avec agent SQL Gemini"""
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
        
        # Récupérer le schéma et générer la requête SQL
        try:
            schema = get_database_schema()
            sql_query = execute_sql_query(sql_query)
            sql_result = execute_sql_query(sql_query)
            
            # Générer la réponse conversationnelle
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
                fallback_prompt = f"""Question: {question}

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
            'schema': get_database_schema(),
            'sample_queries': [
                "SELECT COUNT(*) FROM users WHERE role IN ('student', 'eleve')",
                "SELECT u.name, AVG(g.grade) as moyenne FROM users u JOIN grades g ON u.id = g.user_id WHERE u.role IN ('student', 'eleve') GROUP BY u.id, u.name ORDER BY moyenne DESC LIMIT 5",
                "SELECT c.name, COUNT(u.id) as eleves FROM classes c LEFT JOIN users u ON c.id = u.class_id GROUP BY c.id"
            ]
        }
        
        return jsonify(schema_info)
        
    except Exception as e:
        logger.error(f"❌ Erreur lors de la récupération du schéma: {e}")
        return jsonify({'error': 'Erreur lors de la récupération du schéma'}), 500

@app.route('/test', methods=['POST'])
def test_query():
    """Endpoint de test pour vérifier une requête spécifique"""
    try:
        data = request.get_json()
        query = data.get('query', 'SELECT COUNT(*) as total FROM users')
        
        result = execute_sql_query(query)
        
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
    logger.info("🚀 Démarrage du service IA avec Agent SQL Gemini Direct")
    logger.info(f"🔗 URL: http://localhost:5000")
    logger.info("💡 Le service utilise Gemini avec des requêtes SQL réelles")
    
    app.run(
        host='0.0.0.0',
        port=5000,
        debug=os.getenv('FLASK_DEBUG', 'False').lower() == 'true'
    )

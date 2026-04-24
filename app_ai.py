import os
from flask import Flask, request, jsonify
from flask_cors import CORS
from langchain_google_genai import ChatGoogleGenerativeAI
from langchain_community.utilities import SQLDatabase
from langchain_community.agent_toolkits import create_sql_agent

app = Flask(__name__)
CORS(app)

# --- CONFIGURATION ---

# 1. Remplace par ta clé API générée sur Google AI Studio
os.environ["GOOGLE_API_KEY"] = "AIzaSyA5o4MouPpUf09o3NsagoH0nUs34msf-Ek"

# 2. Connexion à MySQL (Vérifie bien le nom de ta base de données)
# Format : mysql+pymysql://utilisateur:motdepasse@localhost/nom_de_la_base
try:
    db = SQLDatabase.from_uri("mysql+pymysql://root:@localhost/school_management_system")
    print("✅ Connexion à la base de données réussie.")
except Exception as e:
    print(f"❌ Erreur de connexion BDD : {e}")

# 3. Initialisation du modèle (Utilisation du nom complet pour éviter l'erreur 404)
llm = ChatGoogleGenerativeAI(model="models/gemini-1.5-flash", temperature=0.2)

# 4. Création de l'Agent SQL "Intelligent"
agent_executor = create_sql_agent(
    llm, 
    db=db, 
    agent_type="tool-calling", 
    verbose=True, 
    handle_parsing_errors=True,
    allow_dangerous_requests=True # Nécessaire pour les versions récentes
)

# --- ROUTES ---

@app.route('/ask', methods=['POST'])
def ask():
    try:
        data = request.json
        user_query = data.get('question', '')

        if not user_query:
            return jsonify({"answer": "Pose-moi une question !"})

        # On donne une instruction système pour que l'IA sache quoi faire
        prompt = (
            f"Tu es l'assistant de l'école. Réponds de manière naturelle. "
            f"Si on te demande des infos sur les élèves, notes ou profs, cherche dans la base. "
            f"Question : {user_query}"
        )

        # L'agent décide s'il utilise le SQL ou s'il répond directement
        response = agent_executor.invoke({"input": prompt})
        
        return jsonify({"answer": response["output"]})

    except Exception as e:
        print(f"⚠️ Erreur lors de l'appel : {str(e)}")
        # On renvoie l'erreur détaillée pour débugger plus facilement
        return jsonify({"answer": f"Erreur de l'IA : {str(e)}"}), 500

if __name__ == '__main__':
    print("\n🤖 SERVEUR IA DÉMARRÉ")
    print("🔗 URL : http://127.0.0.1:5000")
    print("💡 Astuce : Garde ce terminal ouvert pour voir les requêtes SQL en temps réel.\n")
    app.run(host='0.0.0.0', port=5000, debug=False)
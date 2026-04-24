from flask import Flask, request, jsonify
from flask_cors import CORS
import os
import pymysql

app = Flask(__name__)
CORS(app)

# 1. Ta clé Gemini
os.environ["GOOGLE_API_KEY"] = "AIzaSyAoJZnymnettwt1avNumXgMy7-lzXicpuE"

# 2. Test simple avec Gemini direct
from langchain_google_genai import ChatGoogleGenerativeAI

try:
    llm = ChatGoogleGenerativeAI(model="gemini-pro", temperature=0)
    print("Modèle Gemini chargé avec succès")
except Exception as e:
    print(f"Erreur modèle: {e}")
    llm = None

# 3. Connexion BDD directe pour les requêtes simples
def get_db_connection():
    try:
        conn = pymysql.connect(
            host='localhost',
            user='root',
            password='',
            database='school_management_system'
        )
        return conn
    except Exception as e:
        print(f"Erreur BDD: {e}")
        return None

@app.route('/ask', methods=['POST'])
def ask():
    data = request.json
    question = data.get('question')
    print(f"Question reçue: {question}")
    
    try:
        if llm:
            # Utiliser Gemini pour répondre aux questions générales
            prompt = f"""Tu es un assistant pour une école. Réponds à cette question: {question}
            
            Base de données disponible: users, classes, subjects, grades, attendances, schedules, teachers, students
            Sois utile et concis."""
            
            response = llm.invoke(prompt)
            return jsonify({"answer": response.content})
        else:
            return jsonify({"answer": "Service IA temporairement indisponible. Veuillez réessayer plus tard."})
            
    except Exception as e:
        print(f"Erreur: {type(e).__name__}: {str(e)}")
        return jsonify({"answer": f"Erreur: {str(e)}"}), 500

if __name__ == '__main__':
    print("Serveur IA simplifié démarré sur http://127.0.0.1:5000")
    app.run(port=5000)

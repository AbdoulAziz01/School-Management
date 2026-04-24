from flask import Flask, request, jsonify
from flask_cors import CORS
import os
import requests
import pymysql

app = Flask(__name__)
CORS(app)

# 1. Clé OpenAI (remplace par ta vraie clé)
OPENAI_API_KEY = "sk-proj-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"  # Mets ta clé ici

# 2. Connexion BDD pour contexte
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

def get_school_context():
    try:
        conn = get_db_connection()
        if not conn:
            return "Impossible de se connecter à la base de données"
            
        with conn.cursor() as cursor:
            # Récupérer les statistiques actuelles
            cursor.execute("SELECT COUNT(*) FROM users WHERE role = 'eleve'")
            students = cursor.fetchone()[0]
            
            cursor.execute("SELECT COUNT(*) FROM users WHERE role = 'professeur'")
            teachers = cursor.fetchone()[0]
            
            cursor.execute("SELECT COUNT(*) FROM classes")
            classes = cursor.fetchone()[0]
            
            cursor.execute("SELECT name FROM subjects LIMIT 10")
            subjects = cursor.fetchall()
            subjects_list = [s[0] for s in subjects]
            
            cursor.execute("SELECT name FROM classes LIMIT 10")
            classes_list = [c[0] for c in cursor.fetchall()]
            
        conn.close()
        
        return f"""
CONTEXTE DE L'ÉCOLE:
- Nombre total d'élèves: {students}
- Nombre de professeurs: {teachers}
- Nombre de classes: {classes}
- Matières enseignées: {', '.join(subjects_list)}
- Classes disponibles: {', '.join(classes_list)}
- Base de données: school_management_system

Tu es un assistant intelligent pour cette école. Utilise ces informations pour répondre précisément aux questions.
"""
        
    except Exception as e:
        return f"Erreur de récupération du contexte: {str(e)}"

# 3. Appel à OpenAI GPT
def call_openai(question, context):
    try:
        url = "https://api.openai.com/v1/chat/completions"
        
        headers = {
            'Authorization': f'Bearer {OPENAI_API_KEY}',
            'Content-Type': 'application/json',
        }
        
        data = {
            "model": "gpt-3.5-turbo",
            "messages": [
                {
                    "role": "system",
                    "content": f"""Tu es un assistant expert pour un système de gestion scolaire. 

{context}

Réponds de manière:
- Précise et professionnelle
- Basée sur les informations disponibles
- Utile pour les élèves, professeurs et administrateurs
- En français si la question est en français

Si tu ne connais pas une réponse, dis-le honnêtement."""
                },
                {
                    "role": "user", 
                    "content": question
                }
            ],
            "max_tokens": 500,
            "temperature": 0.7
        }
        
        response = requests.post(url, headers=headers, json=data, timeout=30)
        result = response.json()
        
        if 'choices' in result and result['choices']:
            return result['choices'][0]['message']['content']
        else:
            return f"Erreur API OpenAI: {result}"
            
    except Exception as e:
        return f"Erreur de communication avec OpenAI: {str(e)}"

# 4. Requêtes SQL directes pour questions précises
def execute_sql_query(query):
    try:
        conn = get_db_connection()
        if not conn:
            return "Erreur de connexion BDD"
            
        with conn.cursor() as cursor:
            cursor.execute(query)
            result = cursor.fetchall()
            
        conn.close()
        return result
        
    except Exception as e:
        return f"Erreur SQL: {str(e)}"

@app.route('/ask', methods=['POST'])
def ask():
    try:
        data = request.json
        question = data.get('question', '')
        
        print(f"Question reçue: {question}")
        
        # Récupérer le contexte de l'école
        context = get_school_context()
        
        # Appeler OpenAI avec le contexte
        response = call_openai(question, context)
        
        print(f"Réponse: {response[:100]}...")
        
        return jsonify({"answer": response})
        
    except Exception as e:
        print(f"Erreur: {e}")
        return jsonify({"answer": f"Désolé, une erreur est survenue: {str(e)}"}), 500

if __name__ == '__main__':
    print("🤖 Assistant IA intelligent avec OpenAI démarré !")
    print("📍 http://127.0.0.1:5000")
    print("💬 Prêt à répondre comme un vrai LLM !")
    app.run(port=5000)

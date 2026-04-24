from flask import Flask, request, jsonify
from flask_cors import CORS
import os
import requests
import pymysql

app = Flask(__name__)
CORS(app)

# 1. Clé Gemini directe
GEMINI_API_KEY = "AIzaSyAoJZnymnettwt1avNumXgMy7-lzXicpuE"

# 2. Fonction pour appeler Gemini API directement
def call_gemini(prompt):
    try:
        url = f"https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key={GEMINI_API_KEY}"
        
        headers = {
            'Content-Type': 'application/json',
        }
        
        data = {
            "contents": [{
                "parts": [{
                    "text": prompt
                }]
            }]
        }
        
        response = requests.post(url, headers=headers, json=data)
        result = response.json()
        
        if 'candidates' in result and result['candidates']:
            return result['candidates'][0]['content']['parts'][0]['text']
        else:
            return f"Erreur API: {result}"
            
    except Exception as e:
        return f"Erreur: {str(e)}"

# 3. Connexion BDD pour infos
def get_school_info():
    try:
        conn = pymysql.connect(
            host='localhost',
            user='root',
            password='',
            database='school_management_system'
        )
        
        with conn.cursor() as cursor:
            # Nombre d'élèves
            cursor.execute("SELECT COUNT(*) FROM users WHERE role = 'eleve'")
            students_count = cursor.fetchone()[0]
            
            # Nombre de professeurs
            cursor.execute("SELECT COUNT(*) FROM users WHERE role = 'professeur'")
            teachers_count = cursor.fetchone()[0]
            
            # Nombre de classes
            cursor.execute("SELECT COUNT(*) FROM classes")
            classes_count = cursor.fetchone()[0]
            
        conn.close()
        
        return f"""
        École - Informations actuelles:
        - Élèves: {students_count}
        - Professeurs: {teachers_count}  
        - Classes: {classes_count}
        """
        
    except Exception as e:
        return f"Erreur BDD: {str(e)}"

@app.route('/ask', methods=['POST'])
def ask():
    data = request.json
    question = data.get('question')
    print(f"Question reçue: {question}")
    
    try:
        # Ajouter les infos de l'école au prompt
        school_info = get_school_info()
        
        prompt = f"""Tu es un assistant pour une école. Voici les informations actuelles:
        {school_info}
        
        Question de l'utilisateur: {question}
        
        Réponds de manière utile et professionnelle en utilisant les informations disponibles."""
        
        response = call_gemini(prompt)
        
        return jsonify({"answer": response})
        
    except Exception as e:
        print(f"Erreur: {type(e).__name__}: {str(e)}")
        return jsonify({"answer": f"Erreur: {str(e)}"}), 500

if __name__ == '__main__':
    print("Serveur IA direct démarré sur http://127.0.0.1:5000")
    app.run(port=5000)

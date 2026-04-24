from flask import Flask, request, jsonify
from flask_cors import CORS
import pymysql
import json

app = Flask(__name__)
CORS(app)

# Connexion BDD
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

# Réponses pré-définies avec vraies données
def get_school_stats():
    try:
        conn = get_db_connection()
        if not conn:
            return "Erreur de connexion à la base de données"
            
        with conn.cursor() as cursor:
            # Statistiques
            cursor.execute("SELECT COUNT(*) FROM users WHERE role = 'eleve'")
            students = cursor.fetchone()[0]
            
            cursor.execute("SELECT COUNT(*) FROM users WHERE role = 'professeur'")
            teachers = cursor.fetchone()[0]
            
            cursor.execute("SELECT COUNT(*) FROM classes")
            classes = cursor.fetchone()[0]
            
            cursor.execute("SELECT COUNT(*) FROM subjects")
            subjects = cursor.fetchone()[0]
            
        conn.close()
        
        return f"""📊 **Statistiques de l'école** :
        
👥 **Élèves** : {students}
👨‍🏫 **Professeurs** : {teachers}
🏫 **Classes** : {classes}
📚 **Matières** : {subjects}

💡 *Données en temps réel de la base de données*"""
        
    except Exception as e:
        return f"Erreur: {str(e)}"

def get_students_list():
    try:
        conn = get_db_connection()
        if not conn:
            return "Erreur de connexion"
            
        with conn.cursor() as cursor:
            cursor.execute("SELECT name, email FROM users WHERE role = 'eleve' LIMIT 10")
            students = cursor.fetchall()
            
        conn.close()
        
        if students:
            result = "📝 **Liste des élèves (10 premiers)** :\n\n"
            for i, (name, email) in enumerate(students, 1):
                result += f"{i}. **{name}** - {email}\n"
            return result
        else:
            return "Aucun élève trouvé"
            
    except Exception as e:
        return f"Erreur: {str(e)}"

def get_teachers_list():
    try:
        conn = get_db_connection()
        if not conn:
            return "Erreur de connexion"
            
        with conn.cursor() as cursor:
            cursor.execute("SELECT name, email FROM users WHERE role = 'professeur' LIMIT 10")
            teachers = cursor.fetchall()
            
        conn.close()
        
        if teachers:
            result = "👨‍🏫 **Liste des professeurs (10 premiers)** :\n\n"
            for i, (name, email) in enumerate(teachers, 1):
                result += f"{i}. **{name}** - {email}\n"
            return result
        else:
            return "Aucun professeur trouvé"
            
    except Exception as e:
        return f"Erreur: {str(e)}"

# Intelligence artificielle simple
def smart_response(question):
    question_lower = question.lower()
    
    # Salutations
    if any(word in question_lower for word in ['bonjour', 'salut', 'hello', 'hi']):
        return """👋 **Bonjour !** 

Je suis l'assistant de votre système de gestion scolaire.

Je peux vous aider avec :
📊 Statistiques de l'école
👥 Listes d'élèves et professeurs  
📚 Informations sur les classes et matières

Posez-moi une question !"""

    # Statistiques
    elif any(word in question_lower for word in ['statistique', 'combien', 'nombre', 'élèves', 'professeurs', 'classes']):
        return get_school_stats()
    
    # Liste élèves
    elif any(word in question_lower for word in ['élève', 'eleve', 'étudiant', 'etudiant']):
        return get_students_list()
    
    # Liste professeurs
    elif any(word in question_lower for word in ['professeur', 'prof', 'enseignant']):
        return get_teachers_list()
    
    # Aide
    elif any(word in question_lower for word in ['aide', 'help', 'comment', 'fonctionne']):
        return """🤖 **Comment je peux vous aider** :

📊 **Statistiques** : demandez "combien d'élèves ?" ou "statistiques"
👥 **Élèves** : demandez "liste des élèves" ou "montre les élèves"
👨‍🏫 **Professeurs** : demandez "liste des professeurs" ou "montre les profs"
🏫 **Classes** : demandez "combien de classes ?"
📚 **Matières** : demandez "combien de matières ?"

Je travaille avec vos vraies données !"""

    else:
        return f"""🤔 **Je n'ai pas compris** : "{question}"

Essayez de demander :
- "Combien d'élèves ?"
- "Liste des professeurs" 
- "Statistiques de l'école"
- "Aide"

Ou dites "aide" pour voir toutes les commandes disponibles !"""

@app.route('/ask', methods=['POST'])
def ask():
    try:
        data = request.json
        question = data.get('question', '')
        
        print(f"Question reçue: {question}")
        
        # Traitement intelligent
        response = smart_response(question)
        
        print(f"Réponse: {response[:100]}...")
        
        return jsonify({"answer": response})
        
    except Exception as e:
        print(f"Erreur: {e}")
        return jsonify({"answer": f"Désolé, une erreur est survenue: {str(e)}"}), 500

if __name__ == '__main__':
    print("🚀 Assistant scolaire intelligent démarré !")
    print("📍 http://127.0.0.1:5000")
    print("💬 Prêt à répondre aux questions sur votre école !")
    app.run(port=5000)

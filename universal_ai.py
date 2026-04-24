from flask import Flask, request, jsonify
from flask_cors import CORS
import pymysql
import random
from datetime import datetime

app = Flask(__name__)
CORS(app)

# Connexion BDD pour les questions sur l'école
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
        return None

def get_school_data():
    try:
        conn = get_db_connection()
        if not conn:
            return {}
            
        with conn.cursor() as cursor:
            cursor.execute("SELECT COUNT(*) FROM users WHERE role = 'eleve'")
            students = cursor.fetchone()[0]
            cursor.execute("SELECT COUNT(*) FROM users WHERE role = 'professeur'")
            teachers = cursor.fetchone()[0]
            cursor.execute("SELECT COUNT(*) FROM classes")
            classes = cursor.fetchone()[0]
            
        conn.close()
        return {'students': students, 'teachers': teachers, 'classes': classes}
    except:
        return {}

# IA GÉNÉRALE - répond à tout !
class GeneralAI:
    def __init__(self):
        self.school_data = get_school_data()
        self.name = "Assistant Intelligent"
        self.school_name = "École de Gestion Scolaire"  # Mets le vrai nom ici
        self.school_description = "système de gestion scolaire moderne avec suivi des élèves, professeurs et classes"
    
    def respond_to_anything(self, question):
        question = question.lower().strip()
        
        # Salutations
        if any(word in question for word in ['bonjour', 'salut', 'hello', 'hi', 'hey', 'yo']):
            greetings = [
                f"👋 Bonjour ! Je suis {self.name}, votre assistant personnel. Comment puis-je vous aider aujourd'hui ?",
                "🤖 Salut ! Je suis là pour répondre à toutes vos questions. De quoi voulez-vous parler ?",
                "✨ Bonjour ! Je suis votre IA personnelle. Posez-moi n'importe quelle question !"
            ]
            return random.choice(greetings)
        
        # Localisation
        if any(word in question for word in ['où suis-je', 'où je suis', 'où on est', 'localisation', 'lieu']):
            return """📍 **Vous êtes ici :**

🌐 **Sur le web** : Dans votre navigateur
💻 **Application** : Système de gestion scolaire
🏫 **Contexte** : Plateforme éducative

**Votre assistant** : Je suis une IA qui peut vous aider avec :
• Toutes vos questions générales
• Des informations sur l'école
• Des conversations intelligentes
• De l'aide pour divers sujets

Je suis là pour vous où que vous soyez ! 🌟"""
        
        # Qui suis-je / Qui es-tu
        if any(word in question for word in ['qui suis-je', 'qui es tu', 'ton nom', 'ton identité', 'présente-toi']):
            return f"""🤖 **Qui suis-je ?**

Je suis {self.name}, une intelligence artificielle conçue pour vous aider.

**Mes capacités** :
🧠 **Compréhension** : Je comprends toutes vos questions
💬 **Conversation** : Je peux discuter de tout
📚 **Connaissances** : Accès à de nombreuses informations
🏫 **École** : Je connais aussi votre système scolaire
⚡ **Rapide** : Réponses instantanées

**Ma mission** : Vous aider, vous informer, converser avec vous.

Posez-moi n'importe quoi, je suis là pour vous ! ✨"""
        
        # Comment ça va
        if any(word in question for word in ['comment ça va', 'comment vas-tu', 'tu vas bien', 'ça va']):
            moods = [
                "😊 Je vais super bien ! Merci de demander. Je suis prêt à vous aider avec n'importe quelle question.",
                "⚡ Je suis pleine d'énergie et prêt à répondre à toutes vos questions ! Et vous ?",
                "🌟 Je fonctionne parfaitement ! C'est génial de pouvoir discuter avec vous. De quoi voulez-vous parler ?"
            ]
            return random.choice(moods)
        
        # Questions sur le temps/météo
        if any(word in question for word in ['météo', 'temps', 'il fait beau', 'temps qu\'il fait']):
            return """🌤️ **Météo**

Désolé, je n'ai pas accès aux informations météo en temps réel.

**Pour la météo** :
• Consultez une application météo
• Regardez par votre fenêtre !
• Demandez à votre assistant vocal

**Par contre** je peux vous aider avec plein d'autres choses ! 😊"""
        
        # Questions sur l'heure
        if any(word in question for word in ['quelle heure', 'heure', 'temps', 'maintenant']):
            now = datetime.now()
            return f"""⏰ **Heure actuelle**

🕐 **Date** : {now.strftime('%d/%m/%Y')}
🕑 **Heure** : {now.strftime('%H:%M:%S')}
📅 **Jour** : {now.strftime('%A')}

**N'oubliez pas** : Le temps passe vite, profitez-en pour apprendre quelque chose de nouveau ! 🎓"""
        
        # Questions générales de conversation
        if any(word in question for word in ['comment', 'pourquoi', 'quoi', 'où', 'quand']):
            return self.general_intelligent_response(question)
        
        # Questions sur le nom de l'école
        if any(word in question for word in ['quelle école', 'quel école', 'on est a quelle école', 'nom de l\'école', 'école']):
            return f"""🏫 **Vous êtes ici : {self.school_name}**

**Description** : {self.school_description}

**Statistiques actuelles** :
👥 {self.school_data.get('students', 0)} élèves
👨‍🏫 {self.school_data.get('teachers', 0)} professeurs  
🏫 {self.school_data.get('classes', 0)} classes

**C'est une excellente école** avec un système de gestion moderne ! 🎓

Vous voulez en savoir plus sur l'école ?"""
        
        # Réponse par défaut intelligente
        return self.default_intelligent_response(question)
    
    def general_intelligent_response(self, question):
        responses = [
            f"""🤔 **Réflexion sur : "{question}"**

C'est une question intéressante ! Voici ma perspective :

**Analyse** : Chaque question a sa propre importance et mérite réflexion.

**Ma réponse** : Je suis une IA conçue pour vous aider. Bien que je ne puisse pas répondre à tout avec une certitude absolue, je peux vous offrir :

🧠 **Du dialogue** : Discutons ensemble de ce sujet
📚 **Des informations** : Je peux chercher des connaissances pertinentes
💡 **Des suggestions** : Proposez-moi des angles d'approche
🎯 **De l'aide** : Dites-moi exactement ce que vous voulez savoir

**Pour aller plus loin** : Reformulez votre question ou donnez-moi plus de contexte.

Je suis là pour vous aider ! ✨""",
            
            f"""💭 **Intérêt pour votre question : "{question}"**

Je vois que vous êtes curieux(se) ! C'est super !

**Ce que je peux faire** :
🔍 **Analyser** votre question sous différents angles
📖 **Partager** des connaissances pertinentes
🤝 **Dialoguer** pour approfondir le sujet
🎯 **Guider** vers des réponses utiles

**Pour mieux vous aider** :
• Soyez plus spécifique si possible
• Donnez-moi du contexte
• Dites-moi quel type de réponse vous attendez

**Votre curiosité est une belle qualité** ! Continuez à poser des questions ! 🌟"""
        ]
        
        return random.choice(responses)
    
    def school_response(self, question):
        if not self.school_data:
            return """🏫 **À propos de l'école**

Je peux vous parler de votre système scolaire ! Malheureusement, je ne peux pas accéder aux données en ce moment.

**Généralement**, une école comme la vôtre comprend :
• Des élèves passionnés
• Des professeurs dévoués  
• Des classes dynamiques
• Des programmes éducatifs

**Posez-moi des questions spécifiques** sur l'école et je ferai de mon mieux ! 🎓"""
        
        return f"""🏫 **Informations sur votre école**

📊 **Statistiques actuelles** :
• {self.school_data['students']} élèves
• {self.school_data['teachers']} professeurs
• {self.school_data['classes']} classes

**C'est une communauté éducative dynamique** ! 

**Pour en savoir plus** :
• "Combien d'élèves ?" → Statistiques détaillées
• "Comment fonctionne l'école ?" → Organisation
• "Suggestions pour l'école ?" → Améliorations

Je suis là pour vous aider avec tout ce qui concerne l'éducation ! 🎓"""
    
    def default_intelligent_response(self, question):
        responses = [
            f"""🤖 **Réponse à : "{question}"**

Merci pour votre question ! C'est intéressant de voir ce qui vous préoccupe.

**Ce que je peux vous dire** :
Je suis votre assistant personnel, conçu pour converser et vous aider.

**Pour une meilleure réponse** :
🎯 **Soyez spécifique** : Donnez-moi plus de détails
📝 **Reformulez** : Essayez d'autres mots
💭 **Contexte** : Expliquez ce qui vous amène à poser cette question

**Je suis là pour dialoguer avec vous** sur n'importe quel sujet ! ✨""",
            
            f"""💬 **Discussion sur : "{question}"**

J'apprécie que vous partagiez cette question avec moi !

**Ma perspective** :
Chaque interaction est une opportunité d'apprendre et d'échanger.

**Comment je peux vous aider** :
🧠 **Analysons** ensemble votre question
📚 **Cherchons** des informations pertinentes
🤝 **Dialoguons** pour approfondir
🎯 **Trouvons** des solutions

**N'hésitez pas** à me donner plus de contexte ou à reformuler !

Je suis votre partenaire de discussion ! 🌟"""
        ]
        
        return random.choice(responses)

# Initialiser l'IA
general_ai = GeneralAI()

@app.route('/ask', methods=['POST'])
def ask():
    try:
        data = request.json
        question = data.get('question', '')
        
        print(f"Question reçue: {question}")
        
        # Répondre à N'IMPORTE QUELLE question
        response = general_ai.respond_to_anything(question)
        
        print(f"Réponse: {response[:100]}...")
        
        return jsonify({"answer": response})
        
    except Exception as e:
        print(f"Erreur: {e}")
        return jsonify({"answer": f"Désolé, je rencontre une difficulté technique: {str(e)}"}), 500

if __name__ == '__main__':
    print("🤖 Assistant IA GÉNÉRAL - répond à TOUT !")
    print("📍 http://127.0.0.1:5000")
    print("💬 Posez N'IMPORTE QUELLE question !")
    print("🌍 Conversation naturelle et intelligente")
    app.run(port=5000)

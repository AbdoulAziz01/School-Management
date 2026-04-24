from flask import Flask, request, jsonify
from flask_cors import CORS
import os

app = Flask(__name__)
CORS(app)

# Test simple sans IA
@app.route('/ask', methods=['POST'])
def ask():
    data = request.json
    question = data.get('question')
    
    # Réponse temporaire pour tester la connexion
    return jsonify({
        "answer": f"Test reçu : '{question}'. Le serveur Python fonctionne mais l'IA Gemini a des problèmes de modèle. Vérifiez la clé API ou essayez un autre modèle."
    })

if __name__ == '__main__':
    print("Serveur de test démarré sur http://127.0.0.1:5000")
    app.run(port=5000)

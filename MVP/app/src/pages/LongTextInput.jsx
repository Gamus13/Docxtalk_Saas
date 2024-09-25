import { useState } from 'react';
import axios from '../axios'; // Assurez-vous que ce chemin est correct

const LongTextInput = () => {
  const [text, setText] = useState('');

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (text) {
      try {
        // Envoyer la requête POST avec Axios
        const response = await axios.post('/objet-document', {
          document_type: text, // Envoyer le texte comme type de document
        });

        // Traitement de la réponse, par exemple afficher un message de succès
        console.log('Réponse du serveur:', response.data);
        setText(''); // Réinitialiser le champ après soumission
      } catch (error) {
        console.error('Erreur lors de l\'envoi des données:', error);
      }
    }
  };

  return (
    <div className="w-full max-w-md mx-auto">
      <form onSubmit={handleSubmit} className="flex flex-col space-y-4">
        <label className="text-lg font-medium">Entrez le type de document :</label>
        <textarea
          value={text}
          onChange={(e) => setText(e.target.value)}
          placeholder="Écrivez le type de document ici..."
          className="p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          rows="6"
        ></textarea>
        <button
          type="submit"
          className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-500"
        >
          Envoyer
        </button>
      </form>
    </div>
  );
};

export default LongTextInput;

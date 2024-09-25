import { useState, useEffect } from 'react';
import axios from '../axios'; // Assure-toi que le fichier axios est bien configuré pour pointer vers ton backend

const URLInput = () => {
  const [url, setUrl] = useState('');
  const [loading, setLoading] = useState(false);
  const [success, setSuccess] = useState(false); // État pour le message de réussite

  // Envoie des données au backend via Axios
  const sendUrlToBackend = async (url) => {
    setLoading(true); // Démarrer l'animation
    setSuccess(false); // Réinitialiser le message de succès
    try {
      const response = await axios.post('/convert-url-to-pdf', { url });
      console.log('Conversion réussie:', response.data);
      setSuccess(true); // Afficher le message de succès
    } catch (error) {
      console.error('Erreur lors de la conversion:', error);
    } finally {
      setLoading(false); // Arrêter l'animation après l'envoi
    }
  };

  // Utilisation de useEffect pour déclencher l'envoi quand l'URL change
  useEffect(() => {
    if (url) {
      const timer = setTimeout(() => {
        sendUrlToBackend(url);
        setUrl(''); // Réinitialiser le champ après l'envoi
      }, 1000); // 1 seconde de délai avant l'envoi

      return () => clearTimeout(timer); // Nettoie le timer si l'URL change rapidement
    }
  }, [url]);

  return (
    <div className="w-full max-w-md mx-auto relative">
      <label className="text-lg font-medium mb-2 block">Collez une URL :</label>
      <div className="relative flex items-center">
        <input
          type="text"
          value={url}
          onChange={(e) => setUrl(e.target.value)}
          placeholder="http://example.com"
          className="p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 w-full pr-12" // Ajout de padding à droite pour l'animation
        />

        {/* Afficher l'animation ou le message de réussite */}
        {loading ? (
          <div className="absolute right-3 top-1/2 transform -translate-y-1/2">
            <svg
              className="animate-spin h-4 w-4 text-indigo-600" // Ajuster la taille de l'animation
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
            >
              <circle
                className="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                strokeWidth="4"
              ></circle>
              <path
                className="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
              ></path>
            </svg>
          </div>
        ) : success ? (
          <div className="absolute right-3 top-1/2 transform -translate-y-1/2 text-green-600">
            Conversion réussie !
          </div>
        ) : null}
      </div>
    </div>
  );
};

export default URLInput;

import { useState } from 'react';
import axios from '../axios'; // Assurez-vous que le chemin est correct

const FileUpload = () => {
  const [uploadStatus, setUploadStatus] = useState(''); // Pour suivre le statut du téléchargement

  const handleFileChange = async (e) => {
    const file = e.target.files[0]; // Obtenez le fichier sélectionné

    if (file) {
      // Créer un formData pour envoyer le fichier en multipart/form-data
      const formData = new FormData();
      formData.append('file', file);

      try {
        // Utiliser axios pour envoyer la requête
        const response = await axios.post('/fileusers', formData, {
          headers: {
            'Content-Type': 'multipart/form-data', // Assurez-vous d'envoyer la bonne entête
          },
        });

        if (response.status === 200) {
          // Si la réponse est OK, mettre à jour l'état
          setUploadStatus('Fichier téléchargé avec succès!');
        } else {
          setUploadStatus('Échec du téléchargement du fichier.');
        }
      } catch (error) {
        console.error('Erreur lors du téléchargement du fichier:', error);
        setUploadStatus('Erreur lors du téléchargement.');
      }
    }
  };

  return (
    <div className="w-full max-w-md mx-auto">
      <form className="flex flex-col space-y-4">
        <label className="text-lg font-medium">Télécharger un fichier :</label>
        <input
          type="file"
          onChange={handleFileChange} // Appeler handleFileChange lors de l'ajout du fichier
          className="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none"
        />
      </form>

      {/* Afficher le statut du téléchargement */}
      {uploadStatus && <p className="text-sm text-center mt-4">{uploadStatus}</p>}
    </div>
  );
};

export default FileUpload;

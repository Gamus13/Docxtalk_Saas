import React, { useState } from 'react';
import axios from '../axios';

function RegisteList() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [response, setResponse] = useState(null);

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      const res = await axios.post('/register', { email, password });
      setResponse(res.data);
    } catch (error) {
      console.error('Erreur:', error);
      setResponse('Erreur lors de l\'enregistrement');
    }
  };

  return (
    <div>
      <h1>Inscription</h1>
      <form onSubmit={handleSubmit}>
        <input 
          type="email" 
          placeholder="Email" 
          value={email} 
          onChange={(e) => setEmail(e.target.value)} 
        />
        <input 
          type="password" 
          placeholder="Mot de passe" 
          value={password} 
          onChange={(e) => setPassword(e.target.value)} 
        />
        <button type="submit">S'inscrire</button>
      </form>
      {response && <p>Réponse: {response}</p>}
    </div>
  );
}

export default RegisteList;

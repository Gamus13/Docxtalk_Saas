# Étape 1 : Utiliser une image légère de Node.js
FROM node:18-alpine

# Étape 2 : Définir le répertoire de travail
WORKDIR /app

# Étape 3 : Copier le fichier package.json et installer les dépendances
COPY package.json package-lock.json* ./
RUN npm install

# Étape 4 : Copier le reste du code source
COPY . .

# Étape 5 : Exposer le port configuré (ici 3000)
EXPOSE 3000

# Étape 6 : Commande pour démarrer l'application avec Vite
CMD ["npm", "run", "dev", "--", "--host"]


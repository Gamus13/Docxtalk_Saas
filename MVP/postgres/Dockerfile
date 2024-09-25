# Utiliser l'image PostgreSQL officielle version 16
FROM postgres:16

# Installer les outils nécessaires pour compiler l'extension
RUN apt-get update && \
    apt-get install -y git build-essential postgresql-server-dev-16

# Cloner le dépôt pgvector et l'installer
RUN git clone --branch v0.7.4 https://github.com/pgvector/pgvector.git /pgvector && \
    cd /pgvector && \
    make && \
    make install

# Nettoyer les fichiers temporaires
RUN rm -rf /pgvector

# Copier le script d'initialisation pour pgvector
COPY init_pgvector.sql /docker-entrypoint-initdb.d/

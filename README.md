# learn-php-rest-api

Un petit projet pour apprendre à créer une API REST en PHP natif, en utilisant PDO pour la base de données.

## Objectifs

- [X] Connexion PDO à MySQL
- [X] Routes REST simples (sur /products)
- [X] CRUD complet
- [X] Upload de fichier (image)
- [ ] Tests unitaires

## Structure

À compléter plus tard

## À savoir avant d'utiliser l'API

Pour **modifier une ressource** avec cette API, il y a deux cas selon le type de mise à jour :

1. **Mise à jour avec fichier (image)**
   - Utiliser la méthode **POST** avec un champ `_method="PATCH"` (form-data)  
     - Tous les champs texte sont envoyés via form-data  
     - Le fichier image est pris en compte pour l’upload et la mise à jour  

2. **Mise à jour sans fichier**
   - Utiliser la méthode **PATCH** normale avec **raw → JSON**  
     - Cette méthode ne gère **pas l’upload d’image**  
     - Idéal pour modifier uniquement des champs texte ou numériques  

> ⚠️ Important : La distinction est nécessaire car PHP ne peut pas lire les fichiers envoyés dans un `raw JSON`. Pour l’upload d’image, il faut donc passer par `multipart/form-data`.

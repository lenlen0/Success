# Projet SUCCESS

**Objectif :** Développement d'une application web pour l'évaluation des collaborateurs de la société S11 par le biais de questionnaires (QCM).

Ce projet vise à créer une solution interne, le marché actuel ne répondant pas aux besoins spécifiques de S11 (fonctionnalités manquantes ou technologies non maîtrisées en interne).

---

## Fonctionnalités Clés

* **Authentification :** Gestion de deux rôles (Administrateur, Collaborateur) avec connexion sécurisée.
* **Espace Collaborateur :**
    * Rejoindre un questionnaire (via mot de passe).
    * Passer le test (QCM unique ou multiple).
    * Consulter l'historique de ses résultats (notes, corrections).
* **Espace Administrateur :**
    * Gestion des utilisateurs et des groupes.
    * Création d'une banque de questions.
    * Création et assignation des questionnaires.

---

## Stack Technique

* **Frontend :** Vue.js (responsive desktop & mobile).
* **Backend :** Solution BaaS (Backend-as-a-Service) auto-hébergée.
* **Base de Données :** SGBD (modélisation Merise).
* **Hébergement :** Ubuntu Server LTS sur Proxmox S11.

---

## Gestion de Projet

* **Méthodologie :** Agile
* **Gestion de versions (Git) :**
    * `main` : Branche de développement.
    * `release_x.x` : Branches de production.
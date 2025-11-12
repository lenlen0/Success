# 🎯 Projet SUCCESS

**Solution web d'évaluation des collaborateurs par questionnaires pour la société S11.**

Ce dépôt contient le code source de l'application **SUCCESS**, un outil interne d'évaluation des compétences professionnelles. Il a été créé pour répondre à un besoin spécifique de S11.

---

## ✨ Fonctionnalités Principales

L'application est conçue autour de deux rôles distincts avec des permissions spécifiques.

### 👨‍💼 Espace Administrateur
* **Gestion des Utilisateurs :** Créer, modifier et gérer les comptes collaborateurs.
* **Gestion des Groupes :** Créer des groupes de collaborateurs (par ex: "Équipe Marketing").
* **Banque de Questions :** Créer et cataloguer les questions (QCM choix unique / multiple).
* **Gestion des Questionnaires :** Assembler des questions pour créer un test et l'assigner à des utilisateurs ou des groupes.
* **Suivi :** Accéder aux statistiques et résultats.

### 👥 Espace Collaborateur
* **Authentification :** Se connecter à son espace personnel.
* **Accès aux Tests :** Rejoindre un questionnaire assigné à l'aide d'un mot de passe.
* **Passation :** Répondre aux questions dans l'interface de test.
* **Historique :** Consulter les questionnaires déjà réalisés, voir sa note et la correction.

---

## 💻 Stack Technique & Architecture

* **Frontend :** [**QuasarJS**](https://quasar.dev/) (un framework basé sur Vue.js).
* **Backend :** **PHP Natif** (pour l'API et la logique serveur).
* **Mobile :** [**Apache Cordova**](https://cordova.apache.org/) (utilisé pour l'intégration en tant qu'application web/mobile hybride).

---

## 🛠️ Installation et Démarrage (Windows)

Suivez ces étapes pour configurer et lancer le projet sur votre machine.

### 1. Prérequis Système

Avant de cloner, assurez-vous d'avoir installé :

1.  [**Node.js (LTS)**](https://nodejs.org/en)
2.  [**Git for Windows**](https://git-scm.com/downloads)

*(Après l'installation, ouvrez un nouveau terminal (PowerShell ou "Git Bash") pour que les commandes `node`, `npm` et `git` soient reconnues.)*

### 2. Installation du Projet

1.  **Cloner le dépôt** (ouvrez votre terminal et placez-vous dans votre dossier de projets) :
    ```bash
    git clone [https://git.s11.fr/24gajecki/Projet-Success](https://git.s11.fr/24gajecki/Projet-Success)
    ```

2.  **Aller dans le dossier du projet** :
    ```bash
    cd Projet-Success
    ```

3.  **Installer les dépendances** (télécharge les modules nécessaires au projet) :
    ```bash
    npm install
    ```

### 3. Lancer le Projet

1.  **Démarrer le serveur de développement** :
    ```bash
    npm run dev
    ```

2.  **Alternative (via Quasar CLI)** :
    ```bash
    quasar dev
    ```
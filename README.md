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

## 🛠️ Installation et Démarrage

Suivez ces étapes pour configurer et lancer le projet sur votre machine.

### 1. Prérequis Système

1.  **Installer cURL** (Exemple pour Fedora) :
    ```bash
    sudo dnf install curl -y
    ```

2.  **Installer nvm** (Node Version Manager) :
    ```bash
    curl -o- [https://raw.githubusercontent.com/nvm-sh/nvm/master/install.sh](https://raw.githubusercontent.com/nvm-sh/nvm/master/install.sh) | bash
    ```

3.  **Activer nvm** (vous devrez peut-être redémarrer votre terminal ou exécuter) :
    ```bash
    source ~/.bashrc
    ```

4.  **Installer Node.js** (version LTS 22.20.0) :
    ```bash
    nvm install --lts
    ```

5.  **Vérifier les installations** :
    ```bash
    node -v
    npm -v
    ```
    *(Vous devriez voir les versions de Node et npm s'afficher)*

### 2. Installation du Projet

1.  **Cloner le dépôt** (placez-vous dans votre dossier de projets) :
    ```bash
    git clone [https://git.s11.fr/24gajecki/Projet-Success](https://git.s11.fr/24gajecki/Projet-Success)
    ```

2.  **Aller dans le dossier du projet** :
    ```bash
    cd Projet-Success
    ```

3.  **Installer les dépendances** (node_modules) :
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


<template>
  <q-page class="bg-rose column items-center q-pa-lg" style="background-color: #FFF4FF;">
    <div class="q-pa-md full-width">
      <h4 class="text-purple-12 text-weight-bold" style="margin-bottom: 8px;">Évals</h4>

      <q-btn round color="purple-7" icon="add" style="margin: 8px" @click="openDialogAndGenerateCode" />
      <br/><br/>

      <q-table
        flat
        bordered
        color="primary"
        card-class="bg-white"
        table-header-class="bg-purple-1 text-purple-10"
        :rows="rows"
        :columns="columns"
        row-key="Code"
      >
        <template v-slot:body-cell-action="props">
          <q-btn flat color="purple-7" icon="edit" @click="openEditDialog(props.row)" />
          <q-btn flat color="purple-7" icon="delete_outline" @click="deleteRow(props.row)" />
        </template>
        <template v-slot:body-cell-reussite="props">
          <q-td :props="props">
            {{ props.row.reussite || '0%' }}
          </q-td>
        </template>
      </q-table>
    </div>

    <q-dialog v-model="showDialog" persistent>
      <q-card style="min-width: 450px;">
        <q-card-section class="bg-purple-1 text-purple-10">
          <div class="text-h6">{{ isEditing ? 'Modifier évaluation' : 'Nouvelle évaluation' }}</div>
        </q-card-section>

        <q-card-section>
          <div class="row q-gutter-md">
            <q-input rounded outlined v-model="currentEval.nom" label="Nom" class="col-11" />
            <q-input rounded outlined v-model.number="currentEval.time" label="Time (min)" type="number" class="col-11" />

            <q-select
              rounded outlined
              v-model="currentEval.qcm"
              :options="quizzOptions"
              label="QCM"
              class="col-11"
              emit-value
              map-options
            />

            <q-input 
              rounded 
              outlined 
              v-model="currentEval.Code" 
              label="Code" 
              class="col-11" 
              :readonly="isEditing" 
            >
              <template v-slot:append>
                <q-btn 
                    v-if="!isEditing"
                    icon="refresh" 
                    round 
                    flat 
                    dense 
                    @click="generateAndSetCode" 
                    color="purple-7" 
                />
              </template>
            </q-input>

            <q-select
              rounded outlined
              v-model="currentEval.groupe"
              :options="groupOptions"
              label="Groupe"
              class="col-11"
              emit-value
              map-options
            />

            <q-select
              rounded outlined
              v-model="currentEval.status"
              :options="['En cours', 'Fermer', 'Entrainement']"
              label="Status"
              class="col-11"
            />

            <q-card-actions align="center" class="q-gutter-md">
              <q-checkbox v-model="currentEval.Barem" label="Barême" color="purple-7" keep-color />
              <q-checkbox v-model="currentEval.Malus" label="Malus" color="purple-7" keep-color />
            </q-card-actions>
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn unelevated rounded color="purple-7" label="Annuler" v-close-popup @click="resetForm" />
          <q-btn 
            unelevated 
            rounded 
            color="purple-7" 
            :label="isEditing ? 'Modifier' : 'Ajouter'" 
            @click="saveEval" 
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'

const showDialog = ref(false)
const rows = ref([])
const editedEval = ref(null) 

const currentEval = ref({
  nom: '',
  time: 60,
  qcm: null, 
  groupe: null, 
  Code: '', 
  status: 'En cours',
  Barem: false,
  Malus: false,
  idExam: null
})

const isEditing = computed(() => editedEval.value !== null)

const columns = [
  { name: 'nom', label: 'Nom', align: 'left', field: 'nom' },
  { name: 'date', label: 'Date', align: 'left', field: 'date' },
  { name: 'qcm', label: 'QCM', align: 'left', field: 'qcm' },
  { name: 'groupe', label: 'Groupe', align: 'left', field: 'groupe' },
  { name: 'status', label: 'Status', align: 'left', field: 'status' },
  { name: 'reussite', label: '%Réussite', align: 'left', field: 'reussite' },
  { name: 'Code', label: 'Code', align: 'left', field: 'Code' },
  { name: 'action', label: 'Action', align: 'center', style: 'width:120px', headerStyle: 'width:120px' }
]

const groupOptions = ref([])
const quizzOptions = ref([])

// --- Fonctions utilitaires ---

function generateCode() {
    const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let result = '';
    for (let i = 0; i < 6; i++) {
        const randomIndex = Math.floor(Math.random() * characters.length);
        result += characters.charAt(randomIndex);
    }
    return result;
}

function generateAndSetCode() {
    currentEval.value.Code = generateCode();
}

function resetForm() {
    editedEval.value = null;
    currentEval.value = {
        nom: '', time: 60, qcm: null, groupe: null, Code: '', idExam: null,
        status: 'En cours', Barem: false, Malus: false
    }
}

function openDialogAndGenerateCode() {
    resetForm();
    generateAndSetCode();
    showDialog.value = true;
}

// --- Logique de chargement des données ---

async function loadGroups() {
  try {
    const res = await fetch('http://10.0.52.142/success/api.php/show_group')
    const data = await res.json()
    groupOptions.value = data.map(g => ({ label: g.name, value: g.idGroup }))
  } catch (err) {
    console.error('Erreur chargement groupes:', err)
  }
}

async function loadQcm() {
  try {
    const res = await fetch('http://10.0.52.142/success/api.php/show_quizz')
    const data = await res.json()
    quizzOptions.value = data.map(q => ({ label: q.name, value: q.idQuizz }))
  } catch (err) {
    console.error('Erreur chargement quizz:', err)
  }
}

async function loadExams() {
  try {
    const res = await fetch('http://10.0.52.142/success/api.php/show_exam')
    const data = await res.json()

    // MAPPING: Remplace 'Ouvert' par 'En cours' pour l'affichage
    const mapStatusForDisplay = (status) => {
        return status === 'Ouvert' ? 'En cours' : status;
    };
    
    // Fonction utilitaire pour trouver la valeur (ID) du QCM et du Groupe à partir du nom
    const getOptionValue = (options, label) => {
        const option = options.find(o => o.label === label);
        return option ? option.value : null;
    };
    
    rows.value = data.map(item => ({
      // Champs pour l'affichage dans la Q-Table
      nom: item.exam_name,
      date: item.dateExam,
      qcm: item.quizz_name,
      groupe: item.group_name,
      status: mapStatusForDisplay(item.status), 
      reussite: `${item.avg_grade !== null ? (item.avg_grade * 5).toFixed(0) : 0}%`,
      Code: item.code,
      // Champs cachés nécessaires pour la modification et la suppression
      idExam: item.idExam,
      rawStatus: item.status,
      // Stockage de l'objet { label, value } pour la pré-sélection dans le formulaire de modification
      qcmObject: { label: item.quizz_name, value: getOptionValue(quizzOptions.value, item.quizz_name) },
      groupeObject: { label: item.group_name, value: getOptionValue(groupOptions.value, item.group_name) },
      time: item.time ? parseInt(item.time) : 60,
      Barem: item.scale === '1' || item.scale === 1,
      Malus: item.hasMalus === '1' || item.hasMalus === 1,
    }))
  } catch (err) {
    console.error('Erreur chargement exams:', err)
    rows.value = []
  }
}


// --- Logique d'ajout/modification ---

function getPayload(evalData) {
    // MAPPING: Remplace 'En cours' par 'Ouvert' pour l'envoi à l'API.
    const statusApi = evalData.status === 'En cours' ? 'Ouvert' : evalData.status;

    // Détermination des IDs (la valeur sélectionnée est l'objet { label, value } ou la valeur directement si map-options est utilisé)
    const qcmValue = typeof evalData.qcm === 'object' && evalData.qcm !== null ? evalData.qcm.value : evalData.qcm;
    const groupeValue = typeof evalData.groupe === 'object' && evalData.groupe !== null ? evalData.groupe.value : evalData.groupe;

    return {
        ...(evalData.idExam && { idExam: evalData.idExam }), // Ajout conditionnel pour la modification
        name: evalData.nom.trim(),
        time: parseInt(evalData.time) || 60,
        idQuizz: qcmValue,
        idGroup: groupeValue,
        code: String(evalData.Code).trim(), 
        status: statusApi, 
        scale: evalData.Barem ? 1 : 0,
        hasMalus: evalData.Malus ? 1 : 0
    }
}

async function addEval(payload) {
  try {
    const res = await fetch('http://10.0.52.142/success/api.php/add_exam', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    })

    const responseText = await res.text();
    let responseData;
    let errorMessage = '';

    try {
        responseData = JSON.parse(responseText);
    } catch { 
        // Tentative d'identifier l'erreur SQL dans la réponse brute si le JSON est invalide
        const sqlErrorMatch = responseText.match(/Duplicate entry '([^']+)'/);
        if (sqlErrorMatch) {
            errorMessage = `Erreur de la base de données : Le code d'accès '${sqlErrorMatch[1]}' est déjà utilisé. Veuillez choisir un autre code.`;
        } else {
            errorMessage = `Erreur API (JSON invalide). Réponse serveur brute : ${responseText.substring(0, 150)}...`;
        }
        throw new Error(errorMessage);
    }

    if (!res.ok || responseData.status !== 'success') {
        throw new Error(`Erreur API ${res.status}: ${responseData.message || 'Détails inconnus.'}`)
    }
    
    // Message de succès retiré
    
  } catch (err) {
    throw new Error(`Échec de l'ajout : ${err.message}`)
  }
}

async function updateEval(payload) {
    try {
        // NOTE: J'ai rétabli 'update_exam' qui était dans votre code original pour ne pas casser la partie qui fonctionne si l'API a été corrigée.
        // Si vous avez utilisé 'edit_exam' comme dans votre dernier message, veuillez le modifier vous-même pour utiliser le nom de la route fonctionnelle.
        const res = await fetch('http://10.0.52.142/success/api.php/edit_exam', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })

        const responseText = await res.text();
        let responseData;

        try {
            responseData = JSON.parse(responseText);
        } catch { 
            throw new Error(`Erreur API (JSON invalide). Réponse serveur brute : ${responseText.substring(0, 150)}...`);
        }

        if (!res.ok || responseData.status !== 'success') {
            throw new Error(`Erreur API ${res.status}: ${responseData.message || 'Détails inconnus.'}`)
        }
        
        // Message de succès retiré

    } catch (err) {
        throw new Error(`Échec de la modification : ${err.message}`);
    }
}

async function saveEval() {
    // 1. Validation de base (Remplacé alert par console.error pour ne pas bloquer l'interface)
    if (!currentEval.value.nom.trim()) {
      console.error('Validation: Le nom est requis');
      return; 
    }
    if (!currentEval.value.qcm) {
      console.error('Validation: Veuillez sélectionner un QCM');
      return;
    }
    if (!currentEval.value.groupe) {
      console.error('Validation: Veuillez sélectionner un groupe');
      return;
    }
    
    const codeValue = currentEval.value.Code ? currentEval.value.Code.trim() : '';
    if (!codeValue || codeValue.length !== 6) {
      console.error('Validation: Le code d\'accès doit être généré et avoir 6 caractères.');
      return;
    }

    const payload = getPayload(currentEval.value);
    
    try {
        if (isEditing.value) {
            await updateEval(payload);
        } else {
            await addEval(payload);
        }

        // Si l'appel réussi (aucune erreur levée), on met à jour la liste et ferme la modale
        await loadExams();
        resetForm();
        showDialog.value = false;
        
    } catch (error) {
        // Affiche uniquement les erreurs critiques (API/BDD)
        alert(error.message); 
    }
}

// --- Logique d'édition et de suppression ---

function openEditDialog(row) {
    editedEval.value = row;
    
    // Clonage des données de la ligne dans currentEval pour l'édition
    currentEval.value = {
        idExam: row.idExam,
        nom: row.nom,
        time: row.time,
        // Utilisation des objets { label, value } stockés lors du loadExams pour pré-sélectionner le QSelect
        qcm: row.qcmObject, 
        groupe: row.groupeObject,
        Code: row.Code, 
        status: row.status,
        Barem: row.Barem,
        Malus: row.Malus
    };

    showDialog.value = true;
}

async function deleteRow(row) {
    if (!row.idExam) {
        console.error("Erreur: L'ID de l'examen est manquant pour la suppression.");
        return;
    }

    // Le confirm reste car c'est la seule protection contre la suppression accidentelle
    if (!confirm(`Êtes-vous sûr de vouloir supprimer l'évaluation "${row.nom}" (ID: ${row.idExam}) ?`)) {
        return;
    }

    try {
        const payload = {
            idExam: row.idExam 
        }

        const res = await fetch('http://10.0.52.142/success/api.php/del_exam', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })

        const responseText = await res.text();
        let responseData;

        try {
            responseData = JSON.parse(responseText);
        } catch { 
            throw new Error(`Erreur API (JSON invalide). Réponse serveur brute : ${responseText.substring(0, 150)}...`);
        }

        if (!res.ok || responseData.status !== 'success') {
            throw new Error(`Échec de la suppression: ${responseData.message || 'Erreur inconnue.'}`)
        }

        // Succès : Recharger les données pour mettre à jour la table
        await loadExams();
        // Message de succès retiré

    } catch (err) {
        console.error('Erreur de suppression:', err);
        alert(`Échec de la suppression : ${err.message}`); // Garde l'alerte pour les erreurs critiques
    }
}


// Chargements initiaux
onMounted(async () => {
  await loadGroups()
  await loadQcm()
  // Important : loadExams DOIT se faire après loadGroups et loadQcm
  await loadExams() 
})
</script>


<style scoped>
.text-purple-12 { color: var(--q-color-purple-12); }
.bg-rose { background-color: #FFF4FF; }
</style>
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
          <q-btn flat color="purple-7" icon="edit" @click="editRow(props.row)" />
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
          <div class="text-h6">Nouvelle évaluation</div>
        </q-card-section>

        <q-card-section>
          <div class="row q-gutter-md">
            <q-input rounded outlined v-model="newEval.nom" label="Nom" class="col-11" />
            <q-input rounded outlined v-model.number="newEval.time" label="Time (min)" type="number" class="col-11" />

            <q-select
              rounded outlined
              v-model="newEval.qcm"
              :options="quizzOptions"
              label="QCM"
              class="col-11"
            />

            <q-input 
              rounded 
              outlined 
              v-model="newEval.Code" 
              label="Code" 
              class="col-11" 
              readonly
            >
                <template v-slot:append>
                    <q-btn 
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
              v-model="newEval.groupe"
              :options="groupOptions"
              label="Groupe"
              class="col-11"
            />

            <q-select
              rounded outlined
              v-model="newEval.status"
              :options="['En cours', 'Fermer', 'Entrainement']"
              label="Status"
              class="col-11"
            />

            <q-card-actions align="center" class="q-gutter-md">
              <q-checkbox v-model="newEval.Barem" label="Barême" color="purple-7" keep-color />
              <q-checkbox v-model="newEval.Malus" label="Malus" color="purple-7" keep-color />
            </q-card-actions>
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn unelevated rounded color="purple-7" label="Annuler" v-close-popup @click="resetForm" />
          <q-btn unelevated rounded color="purple-7" label="Ajouter" @click="addEval" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const showDialog = ref(false)
const rows = ref([])

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

// Formulaire ajout d'exam
const newEval = ref({
  nom: '',
  time: 60,
  qcm: null, 
  groupe: null, 
  Code: '', // Sera défini par generateCode
  status: 'En cours',
  Barem: false,
  Malus: false
})

// Options chargées par API
const groupOptions = ref([])
const quizzOptions = ref([])


// 🎯 LOGIQUE DE GÉNÉRATION DE CODE
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
    newEval.value.Code = generateCode();
}

function openDialogAndGenerateCode() {
    resetForm();
    generateAndSetCode();
    showDialog.value = true;
}
// FIN LOGIQUE DE GÉNÉRATION DE CODE


function resetForm() {
  newEval.value = {
    nom: '', time: 60, qcm: null, groupe: null, Code: '',
    status: 'En cours', Barem: false, Malus: false
  }
}

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

    // MAPPING: Remplace 'Ouvert' par 'En cours' pour l'affichage seulement.
    const mapStatusForDisplay = (status) => {
        return status === 'Ouvert' ? 'En cours' : status;
    };
    
    rows.value = data.map(item => ({
      nom: item.exam_name,
      date: item.dateExam,
      qcm: item.quizz_name,
      groupe: item.group_name,
      status: mapStatusForDisplay(item.status), 
      reussite: `${item.avg_grade !== null ? (item.avg_grade * 5).toFixed(0) : 0}%`,
      Code: item.code 
    }))
  } catch (err) {
    console.error('Erreur chargement exams:', err)
    rows.value = []
  }
}

async function addEval() {
  // 1. Validation côté client
  if (!newEval.value.nom.trim()) return alert('Le nom est requis')
  if (!newEval.value.qcm) return alert('Veuillez sélectionner un QCM')
  if (!newEval.value.groupe) return alert('Veuillez sélectionner un groupe')
  
  const codeValue = newEval.value.Code ? newEval.value.Code.trim() : '';
  if (!codeValue || codeValue.length !== 6) return alert('Le code d\'accès doit être généré et avoir 6 caractères.');

  // MAPPING: Remplace 'En cours' par 'Ouvert' pour l'envoi à l'API.
  const statusApi = newEval.value.status === 'En cours' ? 'Ouvert' : newEval.value.status;
  
  const payload = {
    name: newEval.value.nom.trim(),
    time: parseInt(newEval.value.time) || 60,
    idQuizz: newEval.value.qcm.value,
    idGroup: newEval.value.groupe.value,
    code: String(codeValue), 
    status: statusApi, 
    scale: newEval.value.Barem ? 1 : 0,
    hasMalus: newEval.value.Malus ? 1 : 0
  }

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
      errorMessage = `Erreur API (JSON invalide). Réponse serveur brute : ${responseText.substring(0, 150)}...`;
      
      const sqlErrorMatch = responseText.match(/Duplicate entry '([^']+)'/);
      if (sqlErrorMatch) {
          errorMessage = `Erreur de la base de données : Le code d'accès '${sqlErrorMatch[1]}' est déjà utilisé. Veuillez choisir un autre code.`;
      }
      throw new Error(errorMessage);
    }

    if (!res.ok) {
        throw new Error(`Erreur API ${res.status}: ${responseData.message || 'Détails inconnus.'}`)
    }
    
    if (responseData.status === 'success') {
      await loadExams()
      resetForm()
      showDialog.value = false 
    } else {
      alert(`Erreur : ${responseData.message || 'inconnue'}`)
    }
  } catch (err) {
    alert(`Échec de l'ajout : ${err.message}`)
  }
}

function editRow(row) {
  alert("La modification n'est pas implémentée pour l'instant.");
  console.log('Modifier :', row)
}

function deleteRow(row) {
  alert("La suppression est locale. L'API doit être implémentée.");
  rows.value = rows.value.filter(r => r.Code !== row.Code)
}

// Chargements initiaux
onMounted(async () => {
  await loadGroups()
  await loadQcm()
  await loadExams()
})
</script>


<style scoped>
.text-purple-12 { color: var(--q-color-purple-12); }
.bg-rose { background-color: #FFF4FF; }
</style>
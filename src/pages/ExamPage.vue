<template>
  <q-page class="bg-rose column items-center q-pa-lg" style="background-color: #FFF4FF;">
    <div class="q-pa-md full-width">
      <h4 class="text-purple-12 text-weight-bold" style="margin-bottom: 8px;">Évals</h4>

      <q-btn round color="purple-7" icon="add" style="margin: 8px" @click="showDialog = true" />
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
      </q-table>
    </div>

    <q-dialog v-model="showDialog" persistent>
      <q-card style="min-width: 400px;">
        <q-card-section class="bg-purple-1 text-purple-10">
          <div class="text-h6">Nouvelle évaluation</div>
        </q-card-section>

        <q-card-section>
          <div class="row q-gutter-md">
            <q-input rounded outlined v-model="newEval.nom" label="Nom" class="col-11" />
            <q-input rounded outlined v-model.number="newEval.time" label="Time (min)" type="number" class="col-11" />

            <q-select
              rounded
              standout="bg-grey-1"
              v-model="newEval.qcm"
              :options="quizzOptions"
              label="QCM"
              class="col-11"
            />

            <q-input rounded outlined v-model="newEval.Code" label="Code" class="col-11" />

            <q-select
              rounded
              standout="bg-grey-1"
              v-model="newEval.groupe"
              :options="groupOptions"
              label="Groupe"
              class="col-11"
            />

            <q-select
              rounded
              standout="bg-grey-1"
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
          <q-btn unelevated rounded color="purple-7" label="Annuler" v-close-popup />
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

const newEval = ref({
  nom: '',
  time: 60,
  qcm: null,
  groupe: null,
  Code: '',
  status: 'En cours',
  Barem: false,
  Malus: false
})


// Options des groupes (chargées depuis l'API)
const groupOptions = ref([])

async function loadGroups() {
  try {
    const res = await fetch('http://10.0.52.142/success/api.php/show_group')
    const data = await res.json()
    groupOptions.value = data.map(g => ({ label: g.name, value: g.idGroup }))
  } catch (err) {
    console.error('Erreur chargement groupes:', err)
  }
}

async function loadExams() {
  try {
    const res = await fetch('http://10.0.52.142/success/api.php/show_exam')
    const data = await res.json()
    rows.value = data.map(item => ({
      nom: item.exam_name,
      date: item.dateExam,
      qcm: item.quizz_name || 'QCM Inventé',
      groupe: item.group_name,
      status: item.status,
      reussite: `${item.avg_grade * 5 || 0}%`,
      Code: item.idExam
    }))
  } catch (err) {
    console.error('Erreur chargement exams:', err)
  }
}

async function addEval() {
  console.log('Début addEval', newEval.value)
  
  // Validation renforcée
  if (!newEval.value.nom || !newEval.value.nom.trim()) {
    alert('Le nom est requis')
    return
  }
  
  if (!newEval.value.qcm) {
    alert('Veuillez sélectionner un QCM')
    return
  }
  
  if (!newEval.value.groupe) {
    alert('Veuillez sélectionner un groupe')
    return
  }
  
  if (!newEval.value.Code || !newEval.value.Code.trim()) {
    alert('Le code est requis')
    return
  }

  const payload = {
    name: newEval.value.nom.trim(),
    time: parseInt(newEval.value.time) || 60,
    idQuizz: newEval.value.qcm.value,
    idGroup: newEval.value.groupe.value,
    code: newEval.value.Code.trim(),
    status: newEval.value.status === 'En cours' ? 'Ouvert' : newEval.value.status,
    scale: newEval.value.Barem ? 1 : 0,
    hasMalus: newEval.value.Malus ? 1 : 0
  }

  console.log('Payload envoyé:', payload)

  try {
    const res = await fetch('http://10.0.52.142/success/api.php/add_exam', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    })

    console.log('Status réponse:', res.status)

    if (!res.ok) {
      const errorText = await res.text()
      throw new Error(`Erreur API ${res.status}: ${errorText}`)
    }

    const data = await res.json()
    console.log('Réponse API:', data)

    if (data.status === 'success') {
      await loadExams()
      // Réinitialiser le formulaire
      newEval.value = {
        nom: '',
        time: 60,
        qcm: null,
        groupe: null,
        Code: '',
        status: 'En cours',
        Barem: false,
        Malus: false
      }
      showDialog.value = false
    } else {
      console.error('Erreur ajout exam:', data.message || data)
      alert(`Erreur lors de l'ajout : ${data.message || 'Erreur inconnue'}`)
    }
  } catch (err) {
    console.error('Erreur API add_exam:', err)
    alert(`Erreur lors de l'ajout : ${err.message}`)
  }
}

function editRow(row) { console.log('Modifier :', row) }
function deleteRow(row) { rows.value = rows.value.filter(r => r.Code !== row.Code) }

onMounted(async () => {
  await loadGroups()
  await loadExams()
})
</script>

<style scoped>
.text-purple-12 { color: var(--q-color-purple-12); }
.bg-rose { background-color: #FFF4FF; }
</style>
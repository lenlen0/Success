<template>
  <q-page class="bg-rose column items-center q-pa-lg" style="background-color: #FFF4FF;">
    <div class="q-pa-md full-width">
      <!-- Titre -->
      <h4
        class="text-purple-12 text-weight-bold"
        style="margin-bottom: 8px;"
      >Éval</h4>

      <!-- Bouton pour ouvrir le dialog -->
      <q-btn round color="purple-7" icon="add" style="margin: 8px" @click="showDialog = true" />
      <br/><br/>

      <!-- Tableau Quasar -->
      <q-table
        flat
        bordered
        color="primary"
        card-class="bg-white"
        table-header-class="bg-purple-1 text-purple-10"
        :rows="rows"
        :columns="columns"
        row-key="nom"
      >
        <!-- Slot pour customiser la colonne "action" -->
        <template v-slot:body-cell-action="props">
          <q-btn flat color="purple-7" icon="edit" aligne="center" @click="editRow(props.row)" />
          <q-btn flat color="purple-7" icon="delete_outline" aligne="center" @click="editRow(props.row)" />
        </template>
      </q-table>
    </div>

    <!-- Dialog pour ajouter une nouvelle évaluation -->
    <q-dialog v-model="showDialog" persistent>
      <q-card style="min-width: 400px;">
        <q-card-section class="bg-purple-1 text-purple-10">
          <div class="text-h6">Nouvelle évaluation</div>
        </q-card-section>

        <q-card-section>
          <div class="row q-gutter-md">
            <q-input rounded outlined :label="'Nom'" class="col-11" />
            <q-input rounded outlined :label="'Time'" class="col-11" />
            <q-input rounded outlined :label="'QCM'" class="col-11" />
            <q-input rounded outlined :label="'Code'" class="col-11" />
            <q-select
              rounded
              standout="bg-grey-1"
              v-model="newEval.groupe"
              :options="['A', 'B', 'C']"
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
              <q-checkbox
              v-model="newEval.Barem"
              label="Barême"
              color="purple-7"
              keep-color
            />
              <q-checkbox
              v-model="newEval.Malus"
              label="Malus"
              color="purple-7"
              keep-color
            />
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

const columns = [
  { name: 'nom', label: 'Nom', align: 'left', field: 'nom' },
  { name: 'date', label: 'Date', align: 'left', field: 'date' },
  { name: 'qcm', label: 'QCM', align: 'left', field: 'qcm' },
  { name: 'groupe', label: 'Groupe', align: 'left', field: 'groupe' },
  { name: 'status', label: 'Status', align: 'left', field: 'status' },
  { name: 'reussite', label: '%Réussite', align: 'left', field: 'reussite' },
  { name: 'Code', label: 'Code', align: 'left', field: 'Code' },
  {
    name: 'action',
    label: 'Action',
    align: 'center',
    style: 'width: 120px; max-width: 120px;',
    headerStyle: 'width: 120px; max-width: 120px;'
  }
]

const rows = ref([])

const newEval = ref({
  nom: '',
  date: '',
  qcm: '',
  groupe: '',
  Code: '',
  Barem: '',
  Malus: ''
})

function addEval() {
  if (newEval.value.nom) {
    rows.value.push({ ...newEval.value, reussite: `${newEval.value.reussite || 0}%` })
    Object.keys(newEval.value).forEach(k => newEval.value[k] = '')
    showDialog.value = false
  }
}

function editRow(row) {
  console.log('Modifier :', row)
}

onMounted(async () => {
  try {
    const response = await fetch('http://10.0.52.142/success/api.php/show_exam')
    if (!response.ok) throw new Error('Erreur HTTP ' + response.status)

    const data = await response.json()

    // 🧩 Adaptation selon la structure exacte de ton JSON
    rows.value = data.map(item => ({
      nom: item.exam_name,
      date: item.dateExam,
      qcm: item.quizz_name,
      groupe: item.group_name,
      status: item.status,
      reussite: `${item.avg_grade * 5}%`, // Exemple : conversion note → pourcentage (optionnel)
      Code: item.idExam
    }))
  } catch (err) {
    console.error('Impossible de charger les données depuis la VM :', err)
  }
})
</script>


<style scoped>
.text-purple-12 {
  color: var(--q-color-purple-12);
}

.bg-rose {
  background-color: #FFF4FF;
}
</style>

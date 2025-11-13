<template>
  <q-page class="bg-rose column items-center q-pa-lg" style="background-color: #FFF4FF;">
    <div class="q-pa-md full-width">
      <!-- Titre -->
      <h4
        class="text-purple-12 text-weight-bold"
        style="margin-bottom: 8px;"
      >Mes Evaluations</h4>

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
          <q-btn flat color="purple-7" icon="visibility" align="center" @click="editRow(props.row)" />
          <q-btn flat color="purple-7" icon="replay" align="center" @click="editRow(props.row)" />
        </template>
      </q-table>
    </div>

    <!-- Dialog pour ajouter une nouvelle évaluation -->
    <q-dialog v-model="showDialog" persistent>
      <q-card style="min-width: 400px;">
        <q-card-section class="bg-purple-1 text-purple-10">
          <div class="text-h6">Ajouté évaluation</div>
        </q-card-section>

        <q-card-section>
          <div class="row q-gutter-md">
            <q-input rounded outlined :label="'Code'" class="col-11" />
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

// Variables réactives
const showDialog = ref(false)
const rows = ref([])

// Colonnes du tableau
const columns = [
  { name: 'nom', label: 'Nom', align: 'left', field: 'nom' },
  { name: 'date', label: 'Date', align: 'left', field: 'date' },
  { name: 'status', label: 'Status', align: 'left', field: 'status' },
  { name: 'Note', label: 'Note', align: 'left', field: 'Note' },
  { name: 'Code', label: 'Code', align: 'left', field: 'Code' },
  {
    name: 'action',
    label: 'Action',
    align: 'center',
    style: 'width: 120px; max-width: 120px;',
    headerStyle: 'width: 120px; max-width: 120px;'
  }
]

const newEval = ref({
  Code: ''
})

// Ajouter une évaluation (temporaire côté client)
function addEval() {
  if (newEval.value.Code) {
    rows.value.push({ ...newEval.value })
    Object.keys(newEval.value).forEach(k => newEval.value[k] = '')
    showDialog.value = false
  }
}

// Éditer une ligne (console log temporaire)
function editRow(row) {
  console.log('Modifier :', row)
}

// ID de l'utilisateur courant
const currentUserId = '2'

// Récupération des données depuis l’API pour cet utilisateur
onMounted(async () => {
  try {
    // 🔹 Utilisation directe de l'URL avec l'id utilisateur
    const response = await fetch(`http://10.0.52.142/success/api.php/show_exam/${currentUserId}`)
    if (!response.ok) throw new Error('Erreur HTTP ' + response.status)

    const data = await response.json()

    // Mapping pour afficher correctement dans le tableau
    rows.value = data.map(item => ({
      nom: item.exam_name,
      date: item.dateExam,
      status: item.status,
      Note: item.avg_grade,
      Code: item.code,
      groupe: item.group_name
    }))

    console.log('Évaluations pour l’utilisateur', currentUserId, rows.value)
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

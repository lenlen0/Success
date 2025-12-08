<template>
  <q-page class="bg-rose column items-center q-pa-lg" style="background-color: #FFF4FF;">
    <div class="q-pa-md full-width">
      <!-- Titre -->
      <h4
        class="text-purple-12 text-weight-bold"
        style="margin-bottom: 8px;"
      >Mes Evaluations</h4>

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
          <q-btn flat color="purple-7" icon="visibility" align="center" @click="detailsRow(props.row)" />
          <q-btn flat color="purple-7" icon="replay" align="center" @click="retryExam(props.row)" />
        </template>
      </q-table>

      <!-- Permet d'afficher le détails d'un Examen passé -->
      <q-dialog v-model="showDetailsDialog" persistent>
        <q-card style="min-width: 800px;">
          <q-card-section class="bg-purple-1 text-purple-10">
            <div class="text-h6">Details de l'examen</div>
          </q-card-section>

          <q-card-section>
                <h2 class="text-h5" v-for="(question, index) in questions" :key="index">
                  {{ question.name }}
                </h2>
          </q-card-section>

          <q-card-actions>
            <q-btn unelevated rounded color="purple-7" label="Annuler" v-close-popup />
          </q-card-actions>
        </q-card>
      </q-dialog>
    </div>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'

// Variables réactives
const rows = ref([])
const questions = ref([])
const showDetailsDialog = ref(false)

// Colonnes du tableau
const columns = [
  { name: 'exam_name', label: 'Nom', align: 'left', field: 'exam_name' },
  { name: 'date_exam', label: 'Date', align: 'left', field: 'date_exam' },
  { name: 'status', label: 'Status', align: 'left', field: 'status' },
  { name: 'avg_grade', label: 'Note', align: 'left', field: 'avg_grade' },
  { name: 'code', label: 'Code', align: 'left', field: 'code' },
  {
    name: 'action',
    label: 'Action',
    align: 'center',
    style: 'width: 120px; max-width: 120px;',
    headerStyle: 'width: 120px; max-width: 120px;'
  }
]

// Éditer une ligne (console log temporaire)
function detailsRow(row) {
  getQuestions(row.idQuizz);
  showDetailsDialog.value = true;
}

function retryExam(row) {
  if (row.status === "Entrainement") {
    alert("Réaliser le renvoi vers l'examen.")
  } else {
    alert("Examen déja passer, impossible de le repasser.")
  }
}

async function loadExamU(id_s11) {
  try {
    const response = await fetch(`http://10.0.52.142/success/api.php/show_passed_exam/${id_s11}`)
    if (!response.ok) throw new Error('Erreur HTTP ' + response.status)

    const data = await response.json()

    // 🧩 Adapter les données à tes colonnes
    rows.value = data.map(item => ({
      idExam: item.idExam,
      exam_name: item.exam_name,
      date_exam: item.date_exam,
      status: item.status,
      avg_grade: item.avg_grade,
      code: item.code,
      idQuizz: item.idQuizz
    }))
  } catch (err) {
    console.error('Impossible de charger les exams :', err)
  }
}

async function getQuestions(idQuizz) {
  try {
    const response = await fetch(`http://10.0.52.142/success/api.php/show_question/${idQuizz}`)
    if (!response.ok) throw new Error('Erreur HTTP ' + response.status)

    const data = await response.json()

    // 🧩 Adapter les données à tes colonnes
    questions.value = data.map(item => ({
      idQuestion: item.idQuestion,
      name: item.name
    }))

    console.log(questions)
  } catch (err) {
    console.error('Impossible de charger les questions :', err)
  }
}

// Récupération des données depuis l’API pour cet utilisateur
onMounted(async () => {
  await loadExamU(3)
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

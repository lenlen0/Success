<template>
  <q-page class="flex flex-center">
    <div class="q-pa-md" style="width: 100%; max-width: 1000px;">

      <h1 class="text-h4  text-purple-12">Questionnaires</h1>

      <q-btn round color="purple-7" icon="add" style="margin: 8px" @click="openAddDialog" />
      <q-btn round color="purple-7" icon="smart_toy" />
      <br/><br/>

      <div class="table-responsive-wrapper">
        <q-table
          color="primary"
          card-class="bg-white"
          table-header-class="bg-purple-1 text-purple-10"
          flat bordered title=""
          :rows="rows"
          :columns="columns"
          row-key="NOM"
        >
          <template v-slot:body-cell-Action="props">
            <q-td :props="props" class="flex items-center justify-center">
              <div class="row items-center q-gutter-sm no-wrap">
                <q-btn flat color="purple-7" dense icon="delete" aria-label="Supprimer" @click="deleteQuizz(props.row)"/>
                <q-btn flat color="purple-7" dense icon="edit" aria-label="Éditer" @click="openEditDialog(props.row)"/>
              </div>
            </q-td>
          </template>
        </q-table>
      </div>
    </div>

    <q-dialog v-model="showDialog" persistent>
      <q-card style="min-width: 400px;">
        <q-card-section class="bg-purple-1 text-purple-10">
          <div class="text-h6">{{ editedQuizz ? 'Éditer le Questionnaire' : 'Nouveau Questionnaire' }}</div>
        </q-card-section>
        <q-card-section>
          <q-input
              v-model="newQuizzName"
              rounded
              outlined
              :label="'Nom'"
              class="col"
              style="width:100%; margin-bottom: 16px;"
            />

          <q-input
              v-model.number="newQuizzTime"
              type="number"
              rounded
              outlined
              :label="'Temps (en minutes)'"
              class="col"
              style="width:100%;"
            />
        </q-card-section>
        <q-card-actions align="right">
          <q-btn unelevated rounded color="purple-7" label="Annuler" v-close-popup />
          <q-btn unelevated rounded color="purple-7" :label="editedQuizz ? 'Modifier' : 'Ajouter'" @click="handleSubmit" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import { ref, onMounted } from 'vue'
import axios from 'axios'


const columns = [
  { name: 'NOM', align: 'center', label: 'NOM', field: 'nom', sortable: true }, // <-- MODIFIÉ
  { name: 'Nombre_De_Questions', label: 'Nombre De Questions', field: 'nombre_de_question', align: 'center'}, // <-- MODIFIÉ
  { name: 'temps', label: 'Temps (min)', field: 'temps' , align: 'center' },
  { name: 'Action', label: 'Action', field: 'Action' , align: 'center' }
]

export default {
  setup () {
    const rows = ref([])
    const showDialog = ref(false)

    const newQuizzName = ref('')
    const newQuizzTime = ref(null)

    const editedQuizz = ref(null)

    async function fetchQuizzes() {
      try {
        const response = await axios.get('https://10.0.52.115/success/api.php/quizz')
        rows.value = response.data
      } catch (error) {
        console.error("Erreur lors de la récupération des questionnaires:", error)
      }
    }

    onMounted(() => {
      fetchQuizzes()
    })

    function openAddDialog() {
      editedQuizz.value = null
      newQuizzName.value = ''
      newQuizzTime.value = null
      showDialog.value = true
    }

    function openEditDialog(row) {
      editedQuizz.value = row
      newQuizzName.value = row.nom
      newQuizzTime.value = row.temps
      showDialog.value = true
    }

    function handleSubmit() {
      if (newQuizzName.value && newQuizzTime.value > 0) {
        if (editedQuizz.value) {
          editedQuizz.value.nom = newQuizzName.value
          editedQuizz.value.temps = newQuizzTime.value
        } else {
          // Logique d'ajout
          rows.value.push({
            id: Date.now(),
            nom: newQuizzName.value,
            nombre_de_question: 0,
            temps: newQuizzTime.value
          })
        }
        showDialog.value = false
      } else {
        console.error("Veuillez remplir tous les champs correctement.")
      }
    }

    function deleteQuizz(rowToDelete) {
      const index = rows.value.indexOf(rowToDelete)
      if (index > -1) {
        rows.value.splice(index, 1)
      }
    }

    return {
      columns,
      rows,
      showDialog,
      newQuizzName,
      newQuizzTime,
      editedQuizz,
      openAddDialog,
      handleSubmit,
      deleteQuizz,
      openEditDialog
    }
  }
}
</script>

<style>
body{
  background-color: #FFF4FF;
}

@media (max-width: 600px) {
  .table-responsive-wrapper {
    width: 100%;
    overflow-x: auto;
  }

  .table-responsive-wrapper .q-table {
    min-width: 500px;
  }
}
</style>

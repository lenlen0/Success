<template>
  <q-page class="bg-rose column items-center q-pa-lg" style="background-color: #FFF4FF;">
    <div class="q-pa-md full-width">
      <!-- Titre -->
      <h4 class="text-purple-12 text-weight-bold" style="margin-bottom: 8px;">Questions</h4>

      <!-- Bouton pour ouvrir le dialog -->
      <q-btn round color="purple-7" icon="add" style="margin: 8px" @click="showDialog = true" />
      <br /><br />

      <!-- Tableau Quasar -->
      <q-table
        flat
        bordered
        color="primary"
        card-class="bg-white"
        table-header-class="bg-purple-1 text-purple-10"
        :rows="rows"
        :columns="columns"
        row-key="Id"
      >
        <!-- Slot pour customiser la colonne "action" -->
        <template v-slot:body-cell-action="props">
          <q-btn flat color="purple-7" icon="edit" align="center" @click="editRow(props.row)" />
          <q-btn flat color="purple-7" icon="delete_outline" align="center" @click="deleteRow(props.row)" />
        </template>
      </q-table>
    </div>

    <!-- Dialog pour ajouter un nouvel utilisateur -->
    <q-dialog v-model="showDialog" persistent>
      <q-card style="min-width: 400px;">
        <q-card-section class="bg-purple-1 text-purple-10">
          <div class="text-h6">Ajouter une question</div>
        </q-card-section>

        <q-card-section>
          <div class="row q-gutter-md">
            <q-input v-model="newQuestion.Question" rounded outlined label="Question" class="col-11" />
            <q-input v-model="newQuestion.Reponses[0]" rounded outlined label="Reponses 1" class="col-11" />
            <q-input v-model="newQuestion.Reponses[1]" rounded outlined label="Reponses 2" class="col-11" />
            <q-input v-model="newQuestion.Reponses[2]" rounded outlined label="Reponses 3" class="col-11" />
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn unelevated rounded color="purple-7" label="Annuler" v-close-popup />
          <q-btn
          unelevated
          rounded
          color="purple-7"
          label="Ajouter"
          @click="addQuestion(newQuestion.Reponses, newQuestion.Question)"
        />

        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const showDialog = ref(false)
const rows = ref([])

// Colonnes du tableau
const columns = [
  { name: 'Id', label: 'ID', align: 'left', field: 'Id' },
  { name: 'Nom', label: 'Nom', align: 'left', field: 'Nom' },
  { name: 'Reponses', label: 'Reponses', align: 'left', field: 'Reponses' },
  {
    name: 'action',
    label: 'Actions',
    align: 'center',
    style: 'width: 120px; max-width: 120px;',
    headerStyle: 'width: 120px; max-width: 120px;'
  }
]

async function loadQuestions() {
  try {
    const response = await fetch('http://10.0.52.142/success/api.php/show_question/1')
    if (!response.ok) throw new Error('Erreur HTTP ' + response.status)

    const data = await response.json()

    // 🧩 Adapter les données à tes colonnes
    rows.value = data.map(item => ({
      Id: item.idQuestion,
      Nom: item.name,
      Reponses: item.reponses,
      Question: item.question
    }))
  } catch (err) {
    console.error('Impossible de charger les utilisateurs :', err)
  }
}

// Nouvel utilisateur
const newQuestion = ref({
  Nom: '',
  Reponses: ['', '', '', ''],
  Question: ''
})

async function addQuestion(reponses, question) {
  try {
    const response = await fetch("http://10.0.52.142/success/api.php/add_question/1", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({
        reponses: reponses,
        question: question
      })
    });

    if (!response.ok) {
      throw new Error("Erreur API " + response.status);
    }

    const data = await response.json();
    console.log("Question ajoutée :", data);

    if (data.status === "success") {
      showDialog.value = false
      await loadQuestions()
    }

    return data;

  } catch (err) {
    console.error("Erreur", err);
  }
}



function editRow(row) {
  console.log('Modifier :', row)
}

function deleteRow(row) {
  rows.value = rows.value.filter(r => r.Id !== row.Id)
}

// Chargement depuis ton API
onMounted(loadQuestions)
</script>

<style scoped>
.text-purple-12 {
  color: var(--q-color-purple-12);
}
.bg-rose {
  background-color: #FFF4FF;
}
</style>

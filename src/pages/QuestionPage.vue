<template>
  <q-page class="bg-rose column items-center q-pa-lg" style="background-color: #FFF4FF;">
    <div class="q-pa-md full-width">
      <!-- Titre -->
      <h4 class="text-purple-12 text-weight-bold" style="margin-bottom: 8px;">Questions</h4>

      <!-- Bouton pour ouvrir le dialog -->
      <q-btn round color="purple-7" icon="add" style="margin: 8px" @click="openAddDialog" />
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

    <!-- Dialog pour ajouter une question -->
    <q-dialog v-model="showDialog" persistent>
      <q-card style="min-width: 400px;">
        <q-card-section class="bg-purple-1 text-purple-10">
          <div class="text-h6">Ajouter une question</div>
        </q-card-section>

        <q-card-section>
          <div class="row q-gutter-md">
            <q-input v-model="newQuestion.Name" rounded outlined label="Question" class="col-11" />
          </div>
        </q-card-section>
        <q-card-actions align="right">
          <q-btn unelevated rounded color="purple-7" label="Annuler" v-close-popup />
          <q-btn
          unelevated
          rounded
          color="purple-7"
          label="Ajouter"
          @click="addQuestion(newQuestion.Name, newQuestion.idQuizz)"
        />

        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Dialog pour modifier une question -->
    <q-dialog v-model="showEditDialog" persistent>
      <q-card style="min-width: 400px;">
        <q-card-section class="bg-purple-1 text-purple-10">
          <div class="text-h6">Modifier une question</div>
        </q-card-section>

        <q-card-section>
          <div class="row q-gutter-md">
            <!-- editingQuestion.Id -->
            <q-input
              v-model="editingQuestion.Name"
              rounded
              outlined
              label="Question"
              class="col-11"
            />
            <!-- Liste dynamique de réponses -->
              <div v-for="(ans, index) in answers" :key="index" class="row">

                <q-input
                  v-model="ans.name"
                  rounded
                  outlined
                  :label="'Réponse ' + (index + 1)"
                  class="col-11"
                />

                <q-toggle
                  v-model="ans.isCorrect"
                  :label="'Bonne réponse ?'"
                  color="purple-7"
                />

                <q-btn
                  dense
                  flat
                  color="red"
                  icon="delete_outline"
                  v-if="answers.length > 1"
                  @click="removeAnswer(index)"
                />
              </div>
              <q-btn
                unelevated
                rounded
                color="purple-7"
                icon="add"
                label="Ajouter une réponse"
                @click="addAnswer"
              />
          </div>

        </q-card-section>

        <q-card-actions align="right">
          <q-btn unelevated rounded color="purple-7" label="Annuler" v-close-popup />
          <q-btn
            unelevated
            rounded
            color="purple-7"
            label="Modifier"
            @click="editHub(editingQuestion.Id, editingQuestion.Name)"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const showDialog = ref(false)
const showEditDialog = ref(false)
const rows = ref([])
const editingQuestion = ref({
  Id: null,
  Name: ''
})

const answers = ref([
  {
    name: '',
    isCorrect: false
  }
])

const addAnswer = () => {
  answers.value.push({
    name: '',
    isCorrect: false
  })
}

const removeAnswer = (index) => {
  answers.value.splice(index, 1)
}

const quizzOptions = ref([])

// Récupérer l'ID du quiz depuis l'URL
const idQuizz = ref(null)

// Colonnes du tableau
const columns = [
  { name: 'Numero', label: 'Numéro', align: 'left', field: 'Numero' },
  { name: 'Nom', label: 'Nom', align: 'left', field: 'Nom' },
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
    // Récupérer l'ID depuis l'URL ou utiliser une valeur par défaut
    const quizzId = idQuizz.value
    const response = await fetch(`http://10.0.52.142/success/api.php/show_question/${quizzId}`)
    if (!response.ok) throw new Error('Erreur HTTP ' + response.status)

    const data = await response.json()

    // Adapter les données aux colonnes du tableau
    rows.value = data.map((item) => ({
      Id: item.idQuestion,
      Numero: item.idQuestion,
      Nom: item.name,
    }))
  } catch (error) {
    console.error('Erreur lors du chargement des questions:', error)
    rows.value = []
  }
}

async function loadAnswers(idQuestion) {
  answers.value = []

  try {
    const response = await fetch(`http://10.0.52.142/success/api.php/show_answer/${idQuestion}`)
    if (!response.ok) throw new Error('Erreur HTTP ' + response.status)

    const data = await response.json()

    answers.value = data.map(a => ({
      name: a.name,
      isCorrect: a.isCorrect == 1
    }))

  } catch (error) {
    console.error('Erreur lors du chargement des réponses:', error)
    answers.value = []
  }
}


async function loadQuizz() {
  try {
    const response = await fetch('http://10.0.52.142/success/api.php/show_quizz')
    if (!response.ok) throw new Error('Erreur HTTP ' + response.status)

    const data = await response.json()
    quizzOptions.value = data.map(q => ({ label: q.name, value: q.idQuizz }))
  } catch (error) {
    console.error('Erreur lors du chargement des questionnaires:', error)
    quizzOptions.value = []
  }
}

// Nouvelle question
const newQuestion = ref({
  Name: '',
  idQuizz: null
})

function openAddDialog() {
  newQuestion.value.Name = ''
  newQuestion.value.idQuizz = null
  showDialog.value = true
}

async function addQuestion(name) {
  const response = await fetch("http://10.0.52.142/success/api.php/add_question", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify({
      name: name,
      idQuizz: idQuizz.value
    })
  });

  if (!response.ok) {
    throw new Error("Erreur API " + response.status);
  }

  const data = await response.json();

  if (data.status === "success") {
    showDialog.value = false
    await loadQuestions()
  }

  return data;
}

async function editHub(id, name) {
  await editQuestion(id, name)

  await deleteAnswersByIdQuestion(id)

  await addAnswers(id)

  showEditDialog.value = false
  await loadQuestions()
}


async function editQuestion(idQuestion, name) {
  try {
    const response = await fetch("http://10.0.52.142/success/api.php/edit_question", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({
        idQuestion: idQuestion,
        name: name
      })
    });

    if (!response.ok) {
      throw new Error("Erreur API " + response.status);
    }

    const data = await response.json();
    console.log("Question modifié :", data);

  } catch (err) {
    console.error("Erreur", err);
  }
}


async function deleteAnswersByIdQuestion(idQuestion) {
  try {
    const response = await fetch("http://10.0.52.142/success/api.php/del_answer_by_idquestion", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({
        idQuestion: idQuestion
      })
    });

    if (!response.ok) {
      throw new Error("Erreur API " + response.status);
    }

    const data = await response.json();
    console.log("Anciennes réponses supprimée :", data);

  } catch (err) {
    console.error("Erreur", err);
  }
}

async function addAnswers(idQuestion) {
  try {
    for (const ans of answers.value) {
      await fetch("http://10.0.52.142/success/api.php/add_answer", {
        method: "POST",
        headers: {
          "Content-Type": "application/json"
        },
        body: JSON.stringify({
          name: ans.name,
          isCorrect: ans.isCorrect ? 1 : 0,
          idQuestion: idQuestion
        })
      })
    }

  } catch (err) {
    console.error("Erreur", err);
  }
}

function editRow(row) {
  answers.value = [{
    name: '',
    isCorrect: false
  }]

  editingQuestion.value = {
    Id: row.Id,
    Name: row.Nom
  }

  loadAnswers(row.Id)
  showEditDialog.value = true
}


async function deleteRow(row) {
  const response = await fetch('http://10.0.52.142/success/api.php/del_question', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ idQuestion: row.Id })
  });
  if (!response.ok) throw new Error('Erreur HTTP ' + response.status);

  const data = await response.json();

  if (data.status === 'success') {
    await loadQuestions();
  }
}

// Chargement depuis l'API
onMounted(async () => {
  // Récupérer l'ID depuis les paramètres de l'URL
  idQuizz.value = route.query.idQuizz || null

  await loadQuizz()

  // Si un ID est présent dans l'URL, pré-remplir le formulaire
  if (idQuizz.value) {
    newQuestion.value.idQuizz = parseInt(idQuizz.value)
  }

  await loadQuestions()
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
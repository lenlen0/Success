<template>
  <q-page class="bg-rose column items-center q-pa-lg" style="background-color: #FFF4FF;">
    <div class="q-pa-md full-width">
      <!-- Titre -->
      <h4 class="text-purple-12 text-weight-bold" style="margin-bottom: 8px;">Questions</h4>

      <!-- Bouton pour ouvrir le dialog -->
      <q-btn round color="purple-7" icon="add" style="margin: 8px" @click="openAddDialog" />
      <q-btn round color="purple-7" icon="smart_toy" @click="openIADialog" />
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

    <!-- Afficher le contenu pour créer une question par IA -->
    <q-dialog v-model="showIADialog" persistent>
      <q-card style="min-width: 400px;">
        <q-card-section class="bg-purple-1 text-purple-10">
          <div class="text-h6">Générer avec l'IA</div>
        </q-card-section>

        <q-card-section>
          <div class="row q-gutter-md">
            <q-input v-model="newIAGeneration.nb_question" rounded outlined label="Nombres de questions" class="col-11" />
            <q-input v-model="newIAGeneration.theme" rounded outlined label="Theme" class="col-11" />
            <q-select
              v-model="newIAGeneration.difficulty"
              rounded
              outlined
              :options="[
                { label: 'Facile', value: 'Facile' },
                { label: 'Intermédiaire', value: 'Intermédiaire' },
                { label: 'Difficile', value: 'Difficile' }
              ]"
              label="Difficulté"
              class="col-11"
              emit-value
              map-options
            />
          </div>
        </q-card-section>
        <q-card-actions align="right">
          <q-btn unelevated rounded color="purple-7" label="Annuler" v-close-popup />
          <q-btn
          @click="IAHub"
          unelevated
          rounded
          color="purple-7"
          label="Ajouter"
        />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Afficher les réponses générer par l'IA-->
    <q-dialog v-model="showIAResult" persistent>
      <q-card style="min-width: 1000px;">
        <q-card-section class="bg-purple-1 text-purple-10">
          <div class="text-h6">Liste des questions Générées</div>
        </q-card-section>
          <div class="row q-gutter-lg flex flex-center">
            <q-table
              class="q-mt-xl"
              flat
              bordered
              color="primary"
              card-class="bg-white"
              table-header-class="bg-purple-1 text-purple-10"
              :rows="temp_rows"
              :columns="temp_columns"
              row-key="Id"
            >
            <template v-slot:body-cell-action="props">
              <q-btn flat color="purple-7" icon="delete_outline" @click="deleteTempRow(props.row)"/>
            </template>
          </q-table>
          </div>
        <q-card-section>

        </q-card-section>
        <q-card-actions align="right">
          <q-btn unelevated rounded color="purple-7" label="Annuler" v-close-popup />
          <q-btn
          @click="addQuestionByIA"
          unelevated
          rounded
          color="purple-7"
          label="Accepter et ajouter"
        />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Afficher la pop-up de demande de nombres de réponses.-->
    <q-dialog v-model="showAskIAResponse" persistent>
      <q-card style="min-width: 400px;">
        <q-card-section class="bg-purple-1 text-purple-10">
          <div class="text-h6">Générer des réponses</div>
        </q-card-section>
          <div class="row q-gutter-md">
            <q-input v-model="genIAResponse.nb_answer" rounded outlined label="Nombres de réponses" class="col-11" />
          </div>
        <q-card-section>

        </q-card-section>
        <q-card-actions align="right">
          <q-btn unelevated rounded color="purple-7" label="Annuler" v-close-popup />
          <q-btn
          @click="askGeminiForAnswers(genIAResponse.nb_answer)"
          unelevated
          rounded
          color="purple-7"
          label="Ajouter"
        />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Afficher les réponses générer par IA-->
    <q-dialog v-model="showResponseIAResult" persistent>
      <q-card style="min-width: 1000px;">
        <q-card-section class="bg-purple-1 text-purple-10">
          <div class="text-h6">Liste des réponses Générées</div>
        </q-card-section>
          <div class="row q-gutter-lg flex flex-center">
            <q-table
              class="q-mt-xl"
              flat
              bordered
              color="primary"
              card-class="bg-white"
              table-header-class="bg-purple-1 text-purple-10"
              :rows="responses_rows"
              :columns="responses_columns"
              row-key="Id"
            >
            <template v-slot:body-cell-action="props">
              <q-btn flat color="purple-7" icon="delete_outline" @click="deleteTempRow(props.row)"/>
            </template>
          </q-table>
          </div>
        <q-card-section>

        </q-card-section>
        <q-card-actions align="right">
          <q-btn unelevated rounded color="purple-7" label="Annuler" v-close-popup />
          <q-btn
          @click="addResponseByIA"
          unelevated
          rounded
          color="purple-7"
          label="Accepter et ajouter"
        />
        </q-card-actions>
      </q-card>
    </q-dialog>

  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'

const API_Gemini = "AIzaSyAOKd8h6HBLBP2UZRi14XDD2BfSqKz96rk";

const route = useRoute()
const showDialog = ref(false)
const showEditDialog = ref(false)
const showIADialog = ref(false)
const showIAResult = ref(false)
const showAskIAResponse = ref(false)
const showResponseIAResult = ref(false)
const rows = ref([])
const temp_rows = ref([])
const responses_rows = ref([])
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

const newIAGeneration = ref({
  nb_question: '',
  theme: '',
  difficulty: null,
  idQuizz: null
})

const genIAResponse = ref({
  nb_answer: ''
})

const IAHub = async () => {
  if (!newIAGeneration.value) {
    console.error('newIAGeneration est indéfini');
    return;
  }
  newIAGeneration.value.idQuizz = idQuizz.value;
  await sendToGemini(
    newIAGeneration.value.nb_question,
    newIAGeneration.value.theme,
    newIAGeneration.value.difficulty,
    newIAGeneration.value.idQuizz
  );
};

async function addResponseByIA() {
  const cleanData = responses_rows.value.map(row => ({
    name: row.name,
    isCorrect: row.isCorrect,
    idQuestion: row.idQuestion
  }));

  try {
    for (const q of cleanData) {
      const response = await fetch("http://10.0.52.142/success/api.php/add_answer", {
        method: "POST",
        headers: {
          "Content-Type": "application/json"
        },
        body: JSON.stringify(q)
      });

      if (!response.ok) {
        throw new Error("Erreur API " + response.status);
      }

      const data = await response.json();

      if (data.status !== "success") {
        console.error("Erreur ajout d'une réponse :", data);
      }
    }

    showResponseIAResult.value = false;
    await loadQuestions();

  } catch (error) {
    console.error("Erreur lors de l'ajout des réponses par IA :", error);
    console.log("Données envoyées :", cleanData);
  }
}

async function askGeminiForAnswers(nb_answer) {
  const questions = rows.value.map(q => ({
    idQuestion: q.Numero,
    name: q.Nom
  }));

  const prompt = `Génère moi ${nb_answer} réponses par question. Une seule doit être correcte.

Voici les questions avec leurs ID réels :

${questions.map(q => `- ID ${q.idQuestion} : ${q.name}`).join("\n")}

Réponds uniquement en JSON strictement valide.
Pour chaque question, renvoie ${nb_answer} objets au format :

{
  "name": "texte de la réponse",
  "isCorrect": 0 ou 1,
  "idQuestion": ID_DE_LA_QUESTION
}

IMPORTANT :
- "idQuestion" doit être EXACTEMENT l'ID donné ci-dessus.
- NE PAS réindexer les ID.
- NE PAS créer de numérotation automatique.
`;

  const body = {
    contents: [
      { parts: [{ text: prompt }] }
    ]
  };

  const response = await fetch(
    `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=${API_Gemini}`,
    {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(body)
    }
  );

  const data = await response.json();

  let raw = data?.candidates?.[0]?.content?.parts?.[0]?.text || "";

  raw = raw.replace(/```json/g, "").replace(/```/g, "").trim();

  try {
    const parsed = JSON.parse(raw);
    responses_rows.value = parsed.map((item) => ({
    name: item.name,
    isCorrect: item.isCorrect,
    idQuestion: item.idQuestion
    }))
    showAskIAResponse.value = false
    showResponseIAResult.value = true
    console.log(responses_rows)
  } catch (err) {
    console.error("Réponse Gemini NON JSON :\n", raw, err);
  }
}

const removeAnswer = (index) => {
  answers.value.splice(index, 1)
}

function deleteTempRow(row) {
  const index = temp_rows.value.findIndex(
    r => r.IdTemporaire === row.IdTemporaire
  );

  if (index !== -1) {
    temp_rows.value.splice(index, 1);
  }
  console.log(temp_rows)
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

// Colonnes des questions temporaires
const temp_columns = [
  { name: 'IdTemporaire', label: 'Id Temp', align: 'left', field: 'IdTemporaire' },
  { name: 'Question', label: 'Question', align: 'left', field: 'Question' },
  {
    name: 'action',
    label: 'Actions',
    align: 'center'
  }
]

const responses_columns = [
  { name: 'name', label: 'Réponses', align: 'left', field: 'name' },
  { name: 'isCorrect', label: 'Est correcte', align: 'left', field: 'isCorrect' },
  { name: 'idQuestion', label: 'Id Question', align: 'left', field: 'idQuestion' },
  {
    name: 'action',
    label: 'Actions',
    align: 'center'
  }
]

async function sendToGemini(nb_question, theme, difficulty, id) {
  const prompt = `
Réalise moi ${nb_question} questions sur le thème suivant : ${theme}. Toutes les questions devront avoir le même niveau de difficulté
qui est le suivant : ${difficulty}. Les questions doivent pouvoir être répondu autres que par un oui ou un non. Pour chaques réponses le format devra impérativement être le suivant :
Réponds uniquement en JSON strictement valide.

Format obligatoire :
{
  "idTemp": "idT",
  "name": "question",
  "idQuizz": "id"
}

Remplace "question" par une question.
Remplace "idQuizz" par un texte correspondant à : ${id}.
Remplace "idT" par un id différent pour chaques questions ayant impérativement la forme suivante : q1 pour la première des questions et ainsi de suite.

Répète autant que necessaire le format JSON obligatoirement en adéquation avec le nombres de questions demandées.
`

  const body = {
    contents: [
      {
        parts: [{ text: prompt }]
      }
    ]
  }

  const response = await fetch(
    `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=${API_Gemini}`,
    {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(body)
    }
  )

  const data = await response.json()

  let rawText = data?.candidates?.[0]?.content?.parts?.[0]?.text || ''

    rawText = rawText
    .replace(/```json/g, "")
    .replace(/```/g, "")
    .trim();

  try {
    const parsed = JSON.parse(rawText)
    temp_rows.value = parsed.map((item) => ({
    IdTemporaire: item.idTemp,
    idQuizz: item.idQuizz,
    Question: item.name
    }))
    showIADialog.value = false
    showIAResult.value = true
    console.log(temp_rows)
  } catch {
    console.error('Erreur : Réponse non JSON\n', rawText)
  }
}

async function addQuestionByIA() {
  const cleanData = temp_rows.value.map(row => ({
    name: row.Question,
    idQuizz: row.idQuizz
  }));

  try {
    for (const q of cleanData) {
      const response = await fetch("http://10.0.52.142/success/api.php/add_question", {
        method: "POST",
        headers: {
          "Content-Type": "application/json"
        },
        body: JSON.stringify(q)
      });

      if (!response.ok) {
        throw new Error("Erreur API " + response.status);
      }

      const data = await response.json();

      if (data.status !== "success") {
        console.error("Erreur ajout d'une question :", data);
      }
    }

    showIAResult.value = false;
    await loadQuestions();
    showAskIAResponse.value = true;

  } catch (error) {
    console.error("Erreur lors de l'ajout des questions par IA :", error);
    console.log("Données envoyées :", cleanData);
  }
}


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

function openIADialog() {
  newIAGeneration.value.nb_question = ''
  newIAGeneration.value.nb_answer = ''
  newIAGeneration.value.theme = ''
  newIAGeneration.value.difficulty = null
  showIADialog.value = true
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
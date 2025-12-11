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
            <div v-for="(question) in questions" :key="question.idQuestion" class="q-mb-xl">
              <h2 class="text-h6" style="color: #4a148c;">{{ question.name }}</h2>

              <!-- Si aucune réponse faite -->
              <div v-if="answers[question.idQuestion]?.noAnswerGiven">
                <div class="text-negative text-weight-bold q-mb-sm">Vous n'avez pas répondu à cette question.</div>
              </div>

              <!-- Liste des réponses -->
              <div v-for="answer in answers[question.idQuestion]" :key="answer.idAnswer" class="q-mb-sm">
                <q-card
                  class="my-card text-white text-subtitle2"
                  :style="answer.isCorrect === 1 ? 'background-color: #05c46b;' : 'background-color: #ff5e57;'"
                >
                  <q-card-section class="row items-center no-wrap" style="gap: 12px;">

                    <!-- Icône X pour réponse choisie -->
                    <div v-if="answer.selectedByUser">
                      <q-icon name="close" size="22px" color="white" />
                    </div>

                    <!-- Texte de la réponse -->
                    <div class="col" style="white-space: normal;">
                      {{ answer.name }}
                    </div>

                  </q-card-section>
                </q-card>
              </div>
            </div>
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
import { ref } from 'vue'
import { onMounted } from 'vue'
import { useRouter } from 'vue-router';

const router = useRouter();
const rows = ref([])
const questions = ref([])
const answers = ref({})
const showDetailsDialog = ref(false)

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

async function loadExamU(id_s11) {
  try {
    const response = await fetch(`http://10.0.52.142/success/api.php/show_passed_exam/${id_s11}`)

    if (!response.ok) throw new Error('Erreur HTTP ' + response.status)

    const data = await response.json()
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
    questions.value = data.map(item => ({
      idQuestion: item.idQuestion,
      name: item.name
    }))

    answers.value = {}

    // load answers for each question in parallel (faster)
    await Promise.all(questions.value.map(q => getAnswers(q.idQuestion)))

  } catch (err) {
    console.error('Impossible de charger les questions :', err)
  }
}

async function getAnswers(idQuestion) {
  try {
    const response = await fetch(`http://10.0.52.142/success/api.php/show_answer/${idQuestion}`)

    if (!response.ok) throw new Error('Erreur HTTP ' + response.status)

    const data = await response.json()
    answers.value[idQuestion] = data.map(item => ({
      idAnswer: item.idAnswer,
      name: item.name,
      isCorrect: item.isCorrect,
      selectedByUser: false,
      missedCorrect: false
    }))
  } catch (err) {
    console.error('Impossible de charger les réponses :', err)
  }
}

async function getAnswersFromUser(id_s11, idExam) {
  try {
    const response = await fetch("http://10.0.52.142/success/api.php/get_answers_from_user", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        id_s11: id_s11,
        idExam: idExam
      })
    })

    if (!response.ok) throw new Error("Erreur API " + response.status)

    const data = await response.json()

    let flatArray = []       // cas où on a juste une liste d'idAnswer choisis
    let perQuestionMap = {}  // cas où on a une valeur par question (indexable)

    if (Array.isArray(data) && data.length > 0 && ('answer' in data[0])) {
      const raw = data[0].answer

      if (typeof raw === 'string') {
        try {
          const parsed = JSON.parse(raw)
          if (Array.isArray(parsed)) {
            flatArray = parsed
          }
        } catch (err) {
          console.warn('getAnswersFromUser: answer est une string non JSON :', raw, err)
        }
      } else if (Array.isArray(raw)) {
        flatArray = raw.slice()
      } else {
        console.warn('getAnswersFromUser: answer existe mais n\'est pas un tableau ni string', raw)
      }
    }

    //API envoie un tableau d'ids (data === [598,562,...])
    if (Array.isArray(data) && data.length > 0 && typeof data[0] === 'number') {
      flatArray = data.slice()
    }

    //API envoie un tableau d'objet
    if (Array.isArray(data) && data.length > 0 && data[0] && typeof data[0] === 'object' && ('idQuestion' in data[0])) {
      for (const item of data) {
        perQuestionMap[item.idQuestion] = item.answer ?? null
        if (item.answer !== null && item.answer !== undefined) {
          flatArray.push(item.answer)
        }
      }
    }

    // 4) si aucune des heuristiques ci-dessus n'a trouvé, on cherche récursivement des idAnswer dans l'objet
    if (flatArray.length === 0 && Object.keys(perQuestionMap).length === 0) {
      // recursion pour trouver des arrays d'entiers dans l'objet `data`
      const collectInts = (obj) => {
        if (!obj) return []
        if (Array.isArray(obj)) {
          const ints = obj.filter(el => typeof el === 'number')
          if (ints.length) return ints
          // sinon tenter à l'intérieur
          for (const el of obj) {
            const sub = collectInts(el)
            if (sub.length) return sub
          }
          return []
        } else if (typeof obj === 'object') {
          for (const k of Object.keys(obj)) {
            const sub = collectInts(obj[k])
            if (sub.length) return sub
          }
          return []
        }
        return []
      }
      const found = collectInts(data)
      if (found.length) flatArray = found
    }

    // nettoyage : flatArray peut contenir nulls -> on les garde dans flatRaw (pour debug),
    // mais pour le lookup on enlève les nulls
    const flatRaw = Array.isArray(flatArray) ? flatArray : []
    const flatFiltered = flatRaw.filter(x => x !== null && x !== undefined)

    const selectedSet = new Set(flatFiltered)

    return { selectedSet, flatRaw, perQuestionMap }

  } catch (err) {
    console.error('Erreur getAnswersFromUser :', err)
    return { selectedSet: new Set(), flatRaw: [], perQuestionMap: {} }
  }
}


function correlateUserAnswers(resultObj) {
  const { selectedSet, perQuestionMap } = resultObj || {}
  // reset
  for (const qId of Object.keys(answers.value)) {
    for (const ans of answers.value[qId]) {
      ans.selectedByUser = false
      ans.noAnswerGiven = false
    }
  }

  // Si on a un perQuestionMap (idQuestion -> idAnswer null)
  if (perQuestionMap && Object.keys(perQuestionMap).length > 0) {
    for (const qId of Object.keys(answers.value)) {
      const val = perQuestionMap[qId]
      if (val === null) {
        answers.value[qId].noAnswerGiven = true
      } else if (val === undefined) {
        let anySelected = false
        for (const ans of answers.value[qId]) {
          if (selectedSet.has(ans.idAnswer)) {
            ans.selectedByUser = true
            anySelected = true
          }
        }
        answers.value[qId].noAnswerGiven = !anySelected
      } else {
        // val est un idAnswer choisi pour cette question
        for (const ans of answers.value[qId]) {
          if (ans.idAnswer === val) {
            ans.selectedByUser = true
          }
        }
        answers.value[qId].noAnswerGiven = false
      }
    }
    return
  }

  for (const qId of Object.keys(answers.value)) {
    let anySelected = false
    for (const ans of answers.value[qId]) {
      if (selectedSet.has(ans.idAnswer)) {
        ans.selectedByUser = true
        anySelected = true
      } else {
        ans.selectedByUser = false
      }
    }
    answers.value[qId].noAnswerGiven = !anySelected
  }
}

async function detailsRow(row) {
  try {
    await getQuestions(row.idQuizz)
    const resultObj = await getAnswersFromUser(3, row.idExam)
    correlateUserAnswers(resultObj)
    showDetailsDialog.value = true
  } catch (err) {
    console.error('Erreur lors de l\'ouverture des détails :', err)
  }
}

function retryExam(row) {
  if (row.status === "Entrainement") {
    router.push({
      path: '/PexamE',
      query: {
        idExam: row.idExam,
        idQuizz: row.idQuizz
      }
    });
  } else {
    alert("Examen déja passer, impossible de le repasser.")
  }
}

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

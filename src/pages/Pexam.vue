<template>
  <q-page padding>
    <div class="row justify-center">
      <div class="col-12 col-md-8">

        <h4 class="text-h4 text-purple-12">Questionnaire</h4>

        <div v-if="questionsList.length > 0" class="text-subtitle1 q-mb-md text-grey-7">
          Question {{ currentIndex + 1 }} / {{ questionsList.length }}
        </div>

        <div v-if="loading" class="row justify-center q-my-xl">
          <q-spinner-dots color="primary" size="3em" />
        </div>

        <q-banner v-else-if="errorMessage" class="bg-negative text-white q-my-md rounded-borders">
          <template v-slot:avatar>
            <q-icon name="warning" />
          </template>
          {{ errorMessage }}
        </q-banner>

        <q-card v-else bordered class="my-card shadow-3">
          <q-card-section class="bg-purple-1 text-purple-10">
            <div class="text-h6 text-weight-bold">
              {{ currentQuestionName }}
            </div>

            <div class="text-caption q-mt-sm">
              <q-badge :color="hasMalus === 1 ? 'red' : 'grey-5'" class="q-mr-xs">
                Malus (-1) : {{ hasMalus === 1 ? 'ACTIF' : 'INACTIF' }}
              </q-badge>
              <q-badge :color="scale === 1 ? 'blue' : 'grey-5'">
                Note sur 20 : {{ scale === 1 ? 'OUI' : 'NON' }}
              </q-badge>
            </div>
          </q-card-section>

          <q-separator />

          <q-card-section>
            <div class="q-gutter-sm">
              <q-list separator>
                <q-item
                  v-for="(row, index) in rows"
                  :key="row.idAnswer || index"
                  clickable
                  v-ripple
                  @click="toggleAnswer(row.idAnswer)"
                >
                  <q-item-section avatar>
                    <q-radio
                      :model-value="selectedAnswer"
                      :val="row.idAnswer"
                      color="purple-7"
                      class="no-pointer-events"
                    />
                  </q-item-section>
                  <q-item-section>
                    <q-item-label>{{ row.name }}</q-item-label>
                  </q-item-section>
                </q-item>
              </q-list>
            </div>

            <div class="row justify-between q-mt-lg">
              <q-btn
                v-if="currentIndex < questionsList.length - 1"
                label="Passer la question"
                color="grey-7"
                flat
                icon="skip_next"
                @click="skipQuestion"
              />
              <div v-else></div>

              <div class="row items-center">
                <q-btn
                  v-if="!isLastQuestion"
                  label="Valider la réponse"
                  type="button"
                  color="purple-7"
                  icon-right="send"
                  unelevated
                  @click="validateAnswer"
                />

                <q-btn
                  v-else
                  label="Terminer le questionnaire"
                  type="button"
                  color="negative"
                  class="q-ml-sm"
                  unelevated
                  @click="finishExam"
                />
              </div>
            </div>
          </q-card-section>
        </q-card>

      </div>
    </div>
  </q-page>
</template>

<script setup>
defineOptions({ name: 'PageExam' })

import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { verifyUR } from '../composables/verificationU'
import { getInfoUser } from 'src/composables/infouser'

const { verifyUserRole } = verifyUR()
const { infoUser } = getInfoUser()
import { Cookies } from 'quasar'

const route = useRoute()
const router = useRouter()

// Données
const rows = ref([])
const questionsList = ref([])
const currentIndex = ref(0)
const idQuestion = ref(null)

// Paramètres URL
const idquizz = ref(route.query.idQuizz ? parseInt(route.query.idQuizz) : 10)
const idExam = ref(route.query.idExam ? parseInt(route.query.idExam) : 95)

// État
const currentQuestionName = ref('')
const selectedAnswer = ref(null)
const selectedAnswers = ref([])
const loading = ref(true)
const errorMessage = ref(null)

// Config Examen
const hasMalus = ref(0)
const scale = ref(0)

const isLastQuestion = computed(() => {
  return questionsList.value.length > 0 && currentIndex.value === questionsList.value.length - 1
})

onMounted(async () => {
  verifyUserRole("admin", "logged_user", "/");
  loading.value = true
  errorMessage.value = null

  await getExamDetails(idExam.value)
  await getQuestionsFromQuiz(idquizz.value)

  if (questionsList.value.length > 0) {
    selectedAnswers.value = new Array(questionsList.value.length).fill(null)
    loadCurrentQuestionData()
  }

  loading.value = false
})

// Utilitaire de conversion
function forceTo1or0(val) {
  if (val === null || val === undefined) return 0;
  const cleanVal = String(val).trim().toLowerCase();
  if (['1', 'true', 'on', 'yes', 'oui'].includes(cleanVal)) {
    return 1;
  }
  return 0;
}

// 1. Config Examen
async function getExamDetails(examId) {
  try {
    const token_user = Cookies.get('token_user')
    const url = `http://10.0.52.187:1337/api/exams?filters[id]=${examId}&populate=*`

    const response = await fetch(url, {
      headers: {
        "Content-Type": "application/json",
        "Authorization": `Bearer ${token_user}`
      }
    })

    if (!response.ok) throw new Error("Erreur API Exam")

    const json = await response.json()

    if (!Array.isArray(json.data) || json.data.length === 0) return

    const exam = json.data[0]

    hasMalus.value = forceTo1or0(exam.hasMalus)
    scale.value = forceTo1or0(exam.scale)

  } catch (err) {
    console.error("❌ Erreur getExamDetails :", err)
  }
}


// 2. Questions
async function getQuestionsFromQuiz(quizId) {
  try {
    const token_user = Cookies.get('token_user')
    const url = `http://10.0.52.187:1337/api/questions?filters[idQuizz][id]=${quizId}&populate=*`

    const response = await fetch(url, {
      headers: {
        "Content-Type": "application/json",
        "Authorization": `Bearer ${token_user}`
      }
    })

    if (!response.ok) throw new Error(`Erreur API (${response.status})`)

    const json = await response.json()

    if (!Array.isArray(json.data) || json.data.length === 0) {
      errorMessage.value = "Ce questionnaire est vide."
      return
    }

    questionsList.value = json.data.map(q => ({
      id: q.id,
      name: q.name
    }))

  } catch (err) {
    console.error("❌ Erreur getQuestionsFromQuiz :", err)
    errorMessage.value = "Erreur chargement questions."
  }
}


// 3. Charger Question
async function loadCurrentQuestionData() {
  const currentQ = questionsList.value[currentIndex.value]

  idQuestion.value = currentQ.id
  currentQuestionName.value = currentQ.name

  selectedAnswer.value = selectedAnswers.value[currentIndex.value] || null
  rows.value = []

  await loadAnswers(idQuestion.value)
}

// 4. Charger Réponses
async function loadAnswers(questionId) {
  try {
    const token_user = Cookies.get('token_user')
    const url = `http://10.0.52.187:1337/api/answers?filters[idQuestion][id]=${questionId}&populate=*`

    const response = await fetch(url, {
      headers: {
        "Content-Type": "application/json",
        "Authorization": `Bearer ${token_user}`
      }
    })

    if (!response.ok) throw new Error("Erreur API Answers")

    const json = await response.json()

    if (!Array.isArray(json.data)) {
      rows.value = []
      return
    }

    rows.value = json.data.map(a => ({
      idAnswer: a.id,
      name: a.name
    }))

  } catch (err) {
    console.error("❌ Erreur loadAnswers :", err)
    rows.value = []
  }
}


// Gestion Clic
function toggleAnswer(ansId) {
  if (selectedAnswer.value === ansId) {
    selectedAnswer.value = null;
  } else {
    selectedAnswer.value = ansId;
  }
  selectedAnswers.value[currentIndex.value] = selectedAnswer.value;
}

function skipQuestion() {
  selectedAnswers.value[currentIndex.value] = null;
  if (currentIndex.value < questionsList.value.length - 1) {
    currentIndex.value++;
    loading.value = true;
    loadCurrentQuestionData().then(() => {
      loading.value = false;
    });
  }
}

// Vérification
async function getAnswerStatus(questionId, answerId) {
  try {
    const token_user = Cookies.get('token_user')
    const url = `http://10.0.52.187:1337/api/answers?filters[idQuestion][id]=${questionId}&populate=*`

    const response = await fetch(url, {
      headers: {
        "Content-Type": "application/json",
        "Authorization": `Bearer ${token_user}`
      }
    })

    if (!response.ok) throw new Error("Erreur API AnswerStatus")

    const json = await response.json()

    if (!Array.isArray(json.data)) return 0

    const answer = json.data.find(a => String(a.id) === String(answerId))
    if (!answer) return 0

    return forceTo1or0(answer.isCorrect) === 1 ? 1 : -1

  } catch (err) {
    console.error(`❌ Erreur getAnswerStatus Q${questionId}:`, err)
    return 0
  }
}

function cleanAnswersArray() {
  const total = questionsList.value.length;
  while (selectedAnswers.value.length < total) {
    selectedAnswers.value.push(null);
  }
  for (let i = 0; i < total; i++) {
    if (selectedAnswers.value[i] === undefined) {
      selectedAnswers.value[i] = null;
    }
  }
}

// --- CALCUL DE LA NOTE (AVEC LOGS DÉTAILLÉS) ---
async function calculateGrade(userAnswers) {
  let rawScore = 0;
  let totalQuestions = questionsList.value.length;

  console.group("📝 DÉTAIL DES RÉSULTATS PAR QUESTION");

  for (let i = 0; i < totalQuestions; i++) {
    const question = questionsList.value[i];
    const questionId = question.id ?? question.idQuestion;
    const userAnswer = userAnswers[i];

    if (userAnswer === null) {
      console.log(`Question ${i+1} : Pas de réponse -> 0 point`);
      rawScore += 0;
    } else {
      const status = await getAnswerStatus(questionId, userAnswer);

      if (status === 1) {
        console.log(`Question ${i+1} : Bonne réponse -> +1 point`);
        rawScore += 1;
      } else if (status === -1) {
        if (hasMalus.value === 1) {
          console.log(`Question ${i+1} : Mauvaise réponse (Malus ACTIF) -> -1 point`);
          rawScore += -1;
        } else {
          console.log(`Question ${i+1} : Mauvaise réponse (Sans Malus) -> 0 point`);
          rawScore += 0;
        }
      }
    }
  }

  let finalGrade = rawScore;

  if (scale.value === 1 && totalQuestions > 0) {
    const oldScore = finalGrade;
    finalGrade = (rawScore / totalQuestions) * 20;
    console.log(`⚖️ Scale activé : Note brute (${oldScore}/${totalQuestions}) ramenée sur 20 -> ${finalGrade}/20`);
  } else {
    console.log(`⚖️ Scale désactivé : Note brute conservée -> ${finalGrade}`);
  }

  console.groupEnd();
  return finalGrade;
}

// Envoi
async function submitExam(answer, grade) {
  try {
    const token_user = Cookies.get('token_user')
    const currentUser = await infoUser()

    const url = "http://10.0.52.187:1337/api/takeexams";
    const response = await fetch(url,
    {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "Authorization": `Bearer ${token_user}`
      },
      body: JSON.stringify({
        data: {
          id_s11: currentUser.id,
          idExam: idExam.value,
          answer: answer,
          grade: grade
        }
      })
    });

    if (!response.ok) throw new Error("Erreur HTTP " + response.status);
    const data = await response.json();
    return data;

  } catch (err) {
    console.error("Erreur submitExam", err);
    throw err;
  }
}

function validateAnswer() {
  selectedAnswers.value[currentIndex.value] = selectedAnswer.value;
  if (currentIndex.value < questionsList.value.length - 1) {
    currentIndex.value++;
    loading.value = true;
    loadCurrentQuestionData().then(() => {
      loading.value = false;
    });
  }
}

function finishExam() {
  selectedAnswers.value[currentIndex.value] = selectedAnswer.value;
  cleanAnswersArray();

  ;(async () => {
    try {
      loading.value = true;
      const calculatedGrade = await calculateGrade(selectedAnswers.value);
      await submitExam(selectedAnswers.value, calculatedGrade);

      loading.value = false;

      setTimeout(() => {
        router.push('/AccueilU');
      }, 500);

    } catch (err) {
      loading.value = false;
      console.error("Erreur finale :", err);
      errorMessage.value = "Erreur lors de l'envoi des résultats.";
    }
  })()
}
</script>

<style scoped>
.no-pointer-events {
  pointer-events: none;
}
</style>

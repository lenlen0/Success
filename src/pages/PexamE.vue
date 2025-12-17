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

const { verifyUserRole } = verifyUR()

const route = useRoute()
const router = useRouter()

// Données
const rows = ref([])
const questionsList = ref([])
const currentIndex = ref(0)
const idQuestion = ref(null)

// Récup les données dans l'url
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
    const url = `http://10.0.52.142/success/api.php/show_exam/${examId}`;
    const response = await fetch(url);
    if (response.ok) {
      const data = await response.json();

      let examData = null;
      if (Array.isArray(data) && data.length > 0) examData = data[0];
      else if (data.records && Array.isArray(data.records)) examData = data.records[0];
      else examData = data;

      if (examData) {
        const keys = Object.keys(examData);
        const keyMalus = keys.find(k => k.toLowerCase().includes('malus')) || 'hasMalus';
        const keyScale = keys.find(k => k.toLowerCase().includes('scale')) || 'scale';

        hasMalus.value = forceTo1or0(examData[keyMalus]);
        scale.value = forceTo1or0(examData[keyScale]);
      }
    }
  } catch (err) {
    console.error("Erreur getExamDetails :", err);
  }
}

// 2. Questions
async function getQuestionsFromQuiz(quizId) {
  try {
    const url = `http://10.0.52.142/success/api.php/show_question/${quizId}`;
    const response = await fetch(url);
    if (!response.ok) throw new Error(`Erreur API (${response.status})`);

    const data = await response.json();
    const list = Array.isArray(data) ? data : (data.records || []);

    if (list.length > 0) {
      questionsList.value = list;
    } else {
      errorMessage.value = "Ce questionnaire est vide.";
    }
  } catch (err) {
    console.error("Erreur getQuestionsFromQuiz :", err);
    errorMessage.value = "Erreur chargement questions.";
  }
}

// 3. Charger Question
async function loadCurrentQuestionData() {
  const currentQ = questionsList.value[currentIndex.value];
  idQuestion.value = currentQ.id || currentQ.idQuestion;
  currentQuestionName.value = currentQ.name;

  selectedAnswer.value = selectedAnswers.value[currentIndex.value] || null;
  rows.value = [];
  await loadAnswers(idQuestion.value);
}

// 4. Charger Réponses
async function loadAnswers(questionId) {
  try {
    const url = `http://10.0.52.142/success/api.php/show_answer/${questionId}`;
    const response = await fetch(url);
    if (response.ok) {
      const data = await response.json();
      const answersList = Array.isArray(data) ? data : (data.records || []);

      rows.value = answersList.map(item => ({
        name: item.name ?? item.label ?? item.text ?? '',
        idAnswer: item.id ?? item.idAnswer ?? item.ID ?? item.answer_id ?? null
      }));
    }
  } catch (err) {
    console.error("Erreur loadAnswers :", err);
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
    const url = `http://10.0.52.142/success/api.php/show_answer/${questionId}`;
    const response = await fetch(url);
    if (response.ok) {
      const data = await response.json();
      const answersList = Array.isArray(data) ? data : (data.records || []);

      const answer = answersList.find(item =>
        String(item.id ?? item.idAnswer) === String(answerId)
      );

      if (answer) {
        const isCorrect = answer.isCorrect ?? answer.correct;
        if (forceTo1or0(isCorrect) === 1) return 1;
        return -1;
      }
    }
  } catch (err) {
    console.error(`Erreur getAnswerStatus Q${questionId}:`, err);
  }
  return 0;
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
      console.log(`Question ${i+1} : ⚪ Pas de réponse -> 0 point`);
      rawScore += 0;
    } else {
      const status = await getAnswerStatus(questionId, userAnswer);

      if (status === 1) {
        console.log(`Question ${i+1} : ✅ Bonne réponse -> +1 point`);
        rawScore += 1;
      } else if (status === -1) {
        if (hasMalus.value === 1) {
          console.log(`Question ${i+1} : ❌ Mauvaise réponse (Malus ACTIF) -> -1 point`);
          rawScore += -1;
        } else {
          console.log(`Question ${i+1} : ❌ Mauvaise réponse (Sans Malus) -> 0 point`);
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

// Update
async function updateExam(answer, grade) {
  try {
    const url = "http://10.0.52.142/success/api.php/update_take_exam";
    const response = await fetch(url, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        id_s11: 3,
        idExam: idExam.value,
        answer: answer,
        grade: grade
      })
    });

    if (!response.ok) throw new Error("Erreur HTTP " + response.status);
    const data = await response.json();
    return data;

  } catch (err) {
    console.error("Erreur :", err);
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
      await updateExam(selectedAnswers.value, calculatedGrade);

      loading.value = false;

      setTimeout(() => {
        router.push('/ExamU');
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

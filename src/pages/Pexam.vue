<template>
  <q-page padding>
    <div class="row justify-center">
      <div class="col-12 col-md-8">

        <h4 class="text-h4 text-purple-12">Questionnaire</h4>

        <!-- Bandeau Mode Entraînement -->
        <q-banner v-if="mode === 'training'" class="bg-orange-2 text-orange-10 q-mb-md rounded-borders shadow-1">
          <template v-slot:avatar>
            <q-icon name="school" color="orange-10" />
          </template>
          <div class="text-weight-bold">Mode Entraînement</div>
          <div class="text-caption">Vos réponses et votre note ne seront pas enregistrées officiellement.</div>
        </q-banner>

        <div v-if="questionsList.length > 0" class="q-mb-md">

          <div class="row justify-between items-center q-mb-xs">
            <span class="text-subtitle1 text-grey-7">Question {{ currentIndex + 1 }} / {{ questionsList.length }}</span>
            <span class="text-subtitle2 text-purple-7 text-weight-bold">{{ Math.round(((currentIndex + 1) / questionsList.length) * 100) }}%</span>
          </div>
          <q-linear-progress 
            :value="(currentIndex + 1) / questionsList.length" 
            color="purple-7" 
            track-color="purple-1" 
            size="10px" 
            rounded 
            class="q-mt-sm shadow-1"
          />
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
import { Cookies } from 'quasar'


const { verifyUserRole } = verifyUR()

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
const mode = ref(route.query.mode || 'normal')


// État
const currentQuestionName = ref('')
const selectedAnswer = ref(null)
const selectedAnswers = ref([])
const loading = ref(true)
const errorMessage = ref(null)

// Strapi Config
const BASE_STRAPI_URL = 'http://10.0.52.187:1337'
const userToken = Cookies.get('token_user')
const currentUserId = ref(null)


// Config Examen
const hasMalus = ref(0)
const scale = ref(0)

const isLastQuestion = computed(() => {
  return questionsList.value.length > 0 && currentIndex.value === questionsList.value.length - 1
})

onMounted(async () => {
  console.log("Pexam mounted with query:", route.query);
  verifyUserRole("admin", "logged_user", "/");
  loading.value = true
  errorMessage.value = null

  await loadUserData()
  
  if (currentUserId.value) {
    await getExamDetails(idExam.value)
    await getQuestionsFromQuiz(idquizz.value)

    if (questionsList.value.length > 0) {
      selectedAnswers.value = new Array(questionsList.value.length).fill(null)
      loadCurrentQuestionData()
    }
  } else {
    errorMessage.value = "Utilisateur non identifié."
  }

  loading.value = false
})

async function loadUserData() {
    if (!userToken) return;
    try {
        const res = await fetch(`${BASE_STRAPI_URL}/api/users/me`, {
            headers: { Authorization: `Bearer ${userToken}` }
        })
        if (res.ok) {
            const data = await res.json();
            currentUserId.value = data.id;
        }
    } catch (err) {
        console.error('Erreur chargement profil Strapi:', err);
    }
}


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
    // Utilisation de filters pour plus de robustesse si l'ID direct fait 404
    const url = `${BASE_STRAPI_URL}/api/exams?filters[id][$eq]=${examId}`;
    const response = await fetch(url, {
      headers: { Authorization: `Bearer ${userToken}` }
    });
    if (response.ok) {
      const data = await response.json();
      const examData = data.data?.[0]?.attributes || data.data?.[0];

      if (examData) {
        // Support pour 'role' ou 'hasMalus'
        hasMalus.value = forceTo1or0(examData.hasMalus);
        scale.value = forceTo1or0(examData.scale);
      }
    }
  } catch (err) {
    console.error("Erreur getExamDetails :", err);
  }
}



// 2. Questions
async function getQuestionsFromQuiz(quizId) {
  try {
    // D'après les logs, la relation s'appelle 'idQuizz'
    const url = `${BASE_STRAPI_URL}/api/questions?filters[idQuizz][id][$eq]=${quizId}`;
    const response = await fetch(url, {
      headers: { Authorization: `Bearer ${userToken}` }
    });
    if (!response.ok) throw new Error(`Erreur API (${response.status})`);

    const data = await response.json();
    const list = data.data || [];

    if (list.length > 0) {
      questionsList.value = list.map(q => {
          const attr = q.attributes || q;
          return {
            id: q.id,
            name: attr.name
          };
      });
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
    // On suppose que la relation s'appelle 'idQuestion' (similaire à idQuizz)
    const url = `${BASE_STRAPI_URL}/api/answers?filters[idQuestion][id][$eq]=${questionId}`;
    const response = await fetch(url, {
      headers: { Authorization: `Bearer ${userToken}` }
    });
    if (response.ok) {
      const data = await response.json();
      const answersList = data.data || [];

      rows.value = answersList.map(item => {
        const attr = item.attributes || item;
        return {
            name: attr.name,
            idAnswer: item.id
        };
      });
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
  if (!answerId) return 0;
  try {
    const url = `${BASE_STRAPI_URL}/api/answers?filters[id][$eq]=${answerId}`;
    const response = await fetch(url, {
      headers: { Authorization: `Bearer ${userToken}` }
    });
    if (response.ok) {
      const data = await response.json();
      const answer = data.data?.[0];

      if (answer) {
        const attr = answer.attributes || answer;
        const isCorrect = attr.isCorrect;
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
    const url = `${BASE_STRAPI_URL}/api/takeexams`;
    const payload = {
      data: {
          id_s11: currentUserId.value,
          idExam: idExam.value,
          answer: JSON.stringify(answer),
          grade: grade.toString()
      }
    };


    console.log("Submitting payload:", payload);

    const response = await fetch(url, {
      method: "POST",
      headers: { 
          "Content-Type": "application/json",
          Authorization: `Bearer ${userToken}`
      },
      body: JSON.stringify(payload)
    });

    if (!response.ok) {
      const errorData = await response.json();
      console.error("Strapi Error Details:", JSON.stringify(errorData, null, 2));
      throw new Error(`Erreur HTTP ${response.status}: ${errorData.error?.message || 'Inconnue'}`);
    }

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
      
      if (mode.value === 'training') {
        console.log("Mode Entraînement : Calcul de la note terminé, pas d'envoi Strapi.");
        loading.value = false;
        router.push('/AccueilU');
        return;
      } else {
        await submitExam(selectedAnswers.value, calculatedGrade);
        loading.value = false;
      }

      setTimeout(() => {
        router.push('/AccueilU');
      }, 1500);

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

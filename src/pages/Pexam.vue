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
          </q-card-section>

          <q-separator />

          <q-card-section>
            <div class="q-gutter-sm">
              <q-list separator>
                <q-item v-for="(row, index) in rows" :key="row.idAnswer || index" tag="label" v-ripple>
                  <q-item-section avatar>
                    <q-radio
                      v-model="selectedAnswer"
                      :val="row.idAnswer"
                      :name="`answer-${idQuestion}`"
                      color="purple-7"
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
                @click="nextQuestion"
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


            <div class="q-mt-md">

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

const rows = ref([])
const questionsList = ref([])
const currentIndex = ref(0)
const idQuestion = ref(null)
const idquizz = 10
const currentQuestionName = ref('')
const selectedAnswer = ref(null)
const selectedAnswers = ref([])
const loading = ref(true)
const errorMessage = ref(null)

const isLastQuestion = computed(() => {
  return questionsList.value.length > 0 && currentIndex.value === questionsList.value.length - 1
})



onMounted(async () => {
  loading.value = true
  errorMessage.value = null

  await getQuestionsFromQuiz(idquizz)

  if (questionsList.value.length > 0) {
    loadCurrentQuestionData()
  }

  loading.value = false
})

// --- FONCTION 1 : Récupérer TOUTES les questions ---
async function getQuestionsFromQuiz(quizId) {
  try {
    const url = `http://10.0.52.142/success/api.php/show_question/${quizId}`;
    const response = await fetch(url);

    if (!response.ok) throw new Error(`Erreur API (${response.status})`);

    const data = await response.json();
    const list = Array.isArray(data) ? data : (data.records || []);

    if (list.length > 0) {
      questionsList.value = list;
      console.log(`${list.length} questions chargées.`);
    } else {
      errorMessage.value = "Ce questionnaire est vide.";
    }

  } catch (err) {
    console.error("Erreur getQuestionsFromQuiz :", err);
    errorMessage.value = "Erreur lors du chargement des questions.";
  }
}

// --- FONCTION : Charger la question actuelle selon l'index ---
async function loadCurrentQuestionData() {
  const currentQ = questionsList.value[currentIndex.value];

  idQuestion.value = currentQ.id || currentQ.idQuestion;
  currentQuestionName.value = currentQ.name;
  selectedAnswer.value = null;
  rows.value = [];
  await loadAnswers(idQuestion.value);
}

// --- FONCTION 2 : Charger les réponses ---
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

      console.debug('Loaded answers for question', questionId, rows.value);
    }
  } catch (err) {
    console.error("Erreur loadAnswers :", err);
  }
}

// --- NOUVELLE FONCTION : Passer à la suivante ---
function recordCurrentAnswer(providedAnswer) {
  // Utiliser l'index de la question pour stocker la réponse dans le tableau
  const qIndex = currentIndex.value
  const ansId = (providedAnswer === undefined)
    ? (selectedAnswer.value == null ? null : selectedAnswer.value)
    : (providedAnswer == null ? null : providedAnswer)

  // S'assurer que le tableau a la bonne taille
  while (selectedAnswers.value.length <= qIndex) selectedAnswers.value.push(null)

  selectedAnswers.value[qIndex] = ansId

  console.debug('Sélectionnées (array) :', selectedAnswers.value)
}

function nextQuestion() {
  recordCurrentAnswer()

  if (currentIndex.value < questionsList.value.length - 1) {
    currentIndex.value++;
    loading.value = true;
    loadCurrentQuestionData().then(() => {
      loading.value = false;
    });
  }
}


// Récupérer le statut d'une réponse donnée (isCorrect: 1 = +1, 0 = -1, null = 0)
async function getAnswerStatus(questionId, answerId) {
  try {
    const url = `http://10.0.52.142/success/api.php/show_answer/${questionId}`;
    const response = await fetch(url);

    if (response.ok) {
      const data = await response.json();
      const answersList = Array.isArray(data) ? data : (data.records || []);
      // Trouver la réponse avec l'ID donnée
      const answer = answersList.find(item =>
        String(item.id ?? item.idAnswer) === String(answerId)
      );
      if (answer) {
        const isCorrect = answer.isCorrect ?? answer.correct;
        // Retourner +1 si correct (1), -1 si incorrect (0), 0 si null
        if (isCorrect === 1 || isCorrect === true) return 1;
        if (isCorrect === 0 || isCorrect === false) return -1;
      }
    }
  } catch (err) {
    console.error(`Erreur getAnswerStatus pour question ${questionId}:`, err);
  }
  return 0; // 0 si réponse non trouvée ou null
}

// Calculer le grade automatiquement: +1 si correct, -1 si incorrect, 0 si pas de réponse
async function calculateGrade(userAnswers) {
  let totalScore = 0;
  let totalQuestions = questionsList.value.length;

  for (let i = 0; i < totalQuestions; i++) {
    const question = questionsList.value[i];
    const questionId = question.id ?? question.idQuestion;
    const userAnswer = userAnswers[i]; // La réponse de l'utilisateur à la question i

    // Si pas de réponse (null ou undefined), score = 0
    if (userAnswer === null || userAnswer === undefined) {
      console.debug(`Question ${i + 1}: Pas de réponse, score = 0`);
      totalScore += 0;
    } else {
      // Récupérer le statut de la réponse fournie
      const score = await getAnswerStatus(questionId, userAnswer);
      console.debug(`Question ${i + 1}: Réponse ${userAnswer}, score = ${score}`);
      totalScore += score;
    }
  }

  console.debug(`Grade total calculé: ${totalScore} (somme des scores par question)`);
  return totalScore;
}

async function submitExam(answer, grade) {
  try {
    const response = await fetch("http://10.0.52.142/success/api.php/user_result_exam", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({
        id_s11: 3,
        idExam: 95,
        answer: answer,
        grade: grade
      })
    });

    if (!response.ok) {
      throw new Error("Erreur API " + response.status);
    }

    const data = await response.json();
    console.log("reponse envoyer:", data);

    return data;

  } catch (err) {
    console.error("Erreur", err);
  }
}

function validateAnswer() {
  recordCurrentAnswer()
  if (currentIndex.value < questionsList.value.length - 1) {
    nextQuestion()
  } else {
    console.log('Dernière question — utilisez "Terminer le questionnaire" pour finir.')
  }
}

function finishExam() {
  recordCurrentAnswer()

  ;(async () => {
    try {
      loading.value = true

      // Calculer le grade automatiquement en comparant avec les réponses correctes
      const calculatedGrade = await calculateGrade(selectedAnswers.value)
      console.debug('Grade calculé avant envoi:', calculatedGrade)

      const result = await submitExam(selectedAnswers.value, calculatedGrade)
      loading.value = false
      console.log('Envoi réussi :', result)
      errorMessage.value = null
    } catch (err) {
      loading.value = false
      console.error("Erreur lors de l'envoi des résultats :", err)
      errorMessage.value = "Erreur lors de l'envoi des résultats."
    }
  })()
}

</script>

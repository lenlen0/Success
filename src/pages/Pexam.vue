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
                  <q-item v-for="(row, index) in rows" :key="index" tag="label" v-ripple>
                    <q-item-section avatar>
                      <q-radio v-model="selectedAnswer" :val="row.name" color="purple-7" />
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
                <div v-else></div> <q-btn
                  :label="isLastQuestion ? 'Terminer le questionnaire' : 'Valider ma réponse'"
                  type="submit"
                  color="purple-7"
                  icon-right="send"
                  unelevated
                  @click="submitAnswer"
                />
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
        name: item.name
      }));
    }
  } catch (err) {
    console.error("Erreur loadAnswers :", err);
  }
}

// --- NOUVELLE FONCTION : Passer à la suivante ---
function nextQuestion() {
  if (currentIndex.value < questionsList.value.length - 1) {
    currentIndex.value++;
    loading.value = true;
    loadCurrentQuestionData().then(() => {
      loading.value = false;
    });
  }
}

function submitAnswer() {
  if (!selectedAnswer.value) {
    console.warn("Veuillez sélectionner une réponse");
    return;
  }
  console.log(`Réponse envoyée pour la question ${idQuestion.value} :`, selectedAnswer.value);

}
</script>

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
        row-key="id"
        :loading="loading"
      >
        <!-- Slot pour customiser la colonne "action" -->
        <template v-slot:body-cell-action="props">
          <q-td :props="props" class="text-center">
            <q-btn v-if="props.row.status !== 'Entrainement'" flat round color="purple-7" icon="visibility" @click="detailsRow(props.row)">
              <q-tooltip>Voir les détails</q-tooltip>
            </q-btn>
            <q-btn flat round color="purple-7" icon="replay" @click="retryExam(props.row)">
              <q-tooltip>Refaire l'examen (Entrainement)</q-tooltip>
            </q-btn>
          </q-td>
        </template>
      </q-table>

      <!-- Détails d'un Examen passé -->
      <q-dialog v-model="showDetailsDialog" persistent>
        <q-card style="min-width: 800px; max-width: 90vw;">
          <q-card-section class="bg-purple-1 text-purple-10 row items-center">
            <div class="text-h6">Détails de l'examen : {{ selectedExamName }}</div>
            <q-space />
            <q-btn icon="close" flat round dense v-close-popup />
          </q-card-section>

          <q-card-section style="max-height: 70vh" class="scroll">
            <div v-if="loadingDetails" class="column items-center q-pa-lg">
              <q-spinner color="purple-7" size="40px" />
              <div class="q-mt-sm">Chargement des détails...</div>
            </div>

            <div v-else v-for="(question) in questions" :key="question.id" class="q-mb-xl">
              <div class="text-h6 text-purple-10 q-mb-md">{{ question.name }}</div>

              <!-- Si aucune réponse faite -->
              <div v-if="question.noAnswerGiven" class="q-mb-md">
                <q-banner dense class="bg-red-1 text-red-9 rounded-borders">
                  Vous n'avez pas répondu à cette question.
                </q-banner>
              </div>

              <!-- Liste des réponses -->
              <div v-for="answer in question.allAnswers" :key="answer.id" class="q-mb-sm">
                <q-card
                  flat
                  bordered
                  class="text-subtitle2 rounded-borders"
                  :style="getAnswerStyle(answer)"
                >
                  <q-card-section class="row items-center no-wrap q-py-sm" style="gap: 12px;">
                    <!-- Icône pour indiquer le choix de l'utilisateur -->
                    <q-icon 
                      :name="answer.selectedByUser ? 'check_circle' : 'radio_button_unchecked'" 
                      :color="answer.selectedByUser ? 'white' : 'grey-5'"
                      size="24px" 
                    />
                    
                    <div class="col" :class="answer.selectedByUser || answer.isCorrect ? 'text-white' : 'text-grey-9'">
                      {{ answer.name }}
                    </div>

                    <q-badge v-if="answer.isCorrect" color="white" text-color="green-9" label="Bonne réponse" />
                  </q-card-section>
                </q-card>
              </div>
            </div>
          </q-card-section>

          <q-card-actions align="right" class="q-pa-md">
            <q-btn unelevated rounded color="purple-7" label="Fermer" v-close-popup />
          </q-card-actions>
        </q-card>
      </q-dialog>
    </div>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { Cookies } from 'quasar'

const BASE_STRAPI_URL = "http://10.0.52.187:1337"
const userToken = Cookies.get('token_user')

const router = useRouter()
const rows = ref([])
const questions = ref([])
const loading = ref(false)
const loadingDetails = ref(false)
const showDetailsDialog = ref(false)
const selectedExamName = ref('')

const currentUserId = ref(null)
const userName = ref('')

const columns = [
  { name: 'exam_name', label: 'Nom', align: 'left', field: 'exam_name', sortable: true },
  { name: 'date_exam', label: 'Date', align: 'left', field: 'date_exam', sortable: true },
  { name: 'status', label: 'Rôle', align: 'left', field: 'status' },
  { name: 'avg_grade', label: 'Note', align: 'left', field: 'avg_grade', sortable: true },
  { name: 'code', label: 'Code', align: 'left', field: 'code' },
  { name: 'action', label: 'Action', align: 'center' }
]

// 1. Charger Infos Utilisateur
async function loadUserData() {
    if (!userToken) return;
    try {
        const res = await fetch(`${BASE_STRAPI_URL}/api/users/me`, {
            headers: { Authorization: `Bearer ${userToken}` }
        })
        if (res.ok) {
            const data = await res.json();
            currentUserId.value = data.id;
            userName.value = data.username || '';
        }
    } catch (err) {
        console.error('Erreur profil Strapi:', err);
    }
}

// 2. Charger les examens passés
async function loadExamU() {
  if (!currentUserId.value) {
    console.warn("loadExamU: Aucun ID utilisateur trouvé.");
    return;
  }
  loading.value = true;
  try {
    // On demande de peupler idExam ET son idQuizz interne avec une syntaxe plus robuste
    const url = `${BASE_STRAPI_URL}/api/takeexams?populate[idExam][populate]=*&populate=id_s11&sort=createdAt:desc`;
    const response = await fetch(url, {
      headers: { Authorization: `Bearer ${userToken}` }
    });

    if (!response.ok) throw new Error('Erreur HTTP ' + response.status);

    const responseData = await response.json();
    const allResults = responseData.data || [];
    console.log("--- DEBUG EVALUATIONS ---");
    console.log("Mon ID (users/me):", currentUserId.value);
    console.log("Mon Pseudo (users/me):", userName.value);
    console.log("Nombre total en base:", allResults.length);

    rows.value = allResults.filter(item => {
        const attr = item.attributes || item;
        const userData = attr.id_s11?.data || attr.id_s11;
        
        if (!userData) {
            console.log(`Ligne ${item.id} rejetée: id_s11 est vide`);
            return false;
        }
        
        const uId = userData.id || userData;
        const uName = userData.attributes?.username || userData.username || (typeof userData === 'string' ? userData : '');
        
        const match = String(uId) === String(currentUserId.value) || 
                     String(uName).toLowerCase() === String(userName.value).toLowerCase();
        
        if (!match) {
            console.log(`Ligne ${item.id} rejetée: Aucun match pour`, { uId, uName });
        } else {
            console.log(`Ligne ${item.id} ACCEPTÉE !`);
        }
        
        return match;
    }).map(item => {
      console.log("Traitement item:", item);
      const attr = item.attributes || item;
      const examData = attr.idExam?.data || attr.idExam || {};
      const examAttr = examData.attributes || examData;
      
      const quizData = examAttr.idQuizz?.data || (examAttr.idQuizz && examAttr.idQuizz.id ? examAttr.idQuizz : {});
      const quizId = quizData.id || (examAttr.idQuizz && typeof examAttr.idQuizz === 'number' ? examAttr.idQuizz : null);

      return {
        id: item.id,
        idExam: examData.id,
        exam_name: examAttr.name || 'Examen sans nom',
        date_exam: new Date(attr.createdAt).toLocaleDateString('fr-FR', {
            day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'
        }),
        status: examAttr.role || examAttr.status || 'N/A',
        avg_grade: attr.grade ? `${parseFloat(attr.grade).toFixed(1)}/20` : 'N/A',
        code: examAttr.code || '-',
        idQuizz: quizId,
        userAnswers: attr.answer
      };
    });

    console.log("Lignes finales dans le tableau:", rows.value);
  } catch (err) {
    console.error('Impossible de charger les exams :', err);
  } finally {
    loading.value = false;
  }
}

// 3. Charger Détails (Questions & Réponses)
async function detailsRow(row) {
  if (!row.idQuizz) {
    alert("Impossible de trouver le quiz associé à cet examen. Vérifiez que l'examen possède un quiz dans Strapi.");
    return;
  }
  selectedExamName.value = row.exam_name;
  showDetailsDialog.value = true;
  loadingDetails.value = true;
  questions.value = [];

  try {
    let selectedAnswerIds = [];
    if (typeof row.userAnswers === 'string') {
      try { selectedAnswerIds = JSON.parse(row.userAnswers); } catch { /* ignore */ }
    } else if (Array.isArray(row.userAnswers)) {
      selectedAnswerIds = row.userAnswers;
    }

    const qUrl = `${BASE_STRAPI_URL}/api/questions?filters[idQuizz][id][$eq]=${row.idQuizz}&populate=*`;
    const qRes = await fetch(qUrl, { headers: { Authorization: `Bearer ${userToken}` } });
    const qData = await qRes.json();
    const rawQuestions = qData.data || [];

    const fullQuestions = await Promise.all(rawQuestions.map(async (q) => {
      const qAttr = q.attributes || q;
      const aUrl = `${BASE_STRAPI_URL}/api/answers?filters[idQuestion][id][$eq]=${q.id}`;
      const aRes = await fetch(aUrl, { headers: { Authorization: `Bearer ${userToken}` } });
      const aData = await aRes.json();
      const rawAnswers = aData.data || [];

      const allAnswers = rawAnswers.map(a => {
        const aAttr = a.attributes || a;
        return {
          id: a.id,
          name: aAttr.name,
          isCorrect: aAttr.isCorrect === true || aAttr.isCorrect === 1,
          selectedByUser: selectedAnswerIds.includes(a.id)
        };
      });

      return {
        id: q.id,
        name: qAttr.name,
        allAnswers,
        noAnswerGiven: !allAnswers.some(a => a.selectedByUser)
      };
    }));

    questions.value = fullQuestions;
  } catch (err) {
    console.error('Erreur détails :', err);
  } finally {
    loadingDetails.value = false;
  }
}

function getAnswerStyle(answer) {
  if (answer.selectedByUser && answer.isCorrect) return 'background-color: #2e7d32; color: white;';
  if (answer.selectedByUser && !answer.isCorrect) return 'background-color: #c62828; color: white;';
  if (!answer.selectedByUser && answer.isCorrect) return 'background-color: #81c784; color: white; opacity: 0.8;';
  return 'background-color: #f5f5f5; color: #424242;';
}

function retryExam(row) {
    router.push({
      path: '/Pexam',
      query: {
        code: row.code,
        mode: 'training',
        idQuizz: row.idQuizz,
        idExam: row.idExam
      }
    });
}

onMounted(async () => {
  await loadUserData();
  await loadExamU();
})
</script>

<style scoped>
.text-purple-12 {
  color: #ab47bc;
}
.bg-rose {
  background-color: #FFF4FF;
}
.rounded-borders {
  border-radius: 8px;
}
</style>

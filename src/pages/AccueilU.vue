<template>
  <q-page class="flex flex-center q-pa-lg" style="background-color: #FFF4FF;">

    <div class="column items-center q-gutter-xl full-width" style="max-width: 1200px;">

      <q-card class="q-pa-lg full-width" flat bordered>
        <q-card-section>
          <div class="flex-left text-center">
            <p class="q-mb-md text-h4 text-purple-12">Entrer votre code d'évaluation</p>
            <div style="max-width: 400px; margin: auto;">
              <q-input
                rounded
                outlined
                v-model="text"
                label="Exemple : P9LN01"
                class="col q-mb-md"
                @keyup.enter="verifyCode"
              />
              <q-btn
                unelevated
                rounded
                color="purple-7"
                label="Entrer"
                class="q-px-xl q-py-sm"
                @click="verifyCode"
                :loading="loading"
              />
            </div>
            <q-banner v-if="errorMessage" class="bg-negative text-white q-my-md rounded-borders">
              <template v-slot:avatar>
                <q-icon name="warning" />
              </template>
              {{ errorMessage }}
            </q-banner>
          </div>
        </q-card-section>
      </q-card>

      <!-- SECTION BADGE ET CLASSEMENT -->
      <div class="row q-col-gutter-lg full-width">
        <!-- Badge Actuel -->
        <div class="col-12 col-md-4">
          <q-card class="q-pa-md full-height" flat bordered style="background: linear-gradient(135deg, #fff 0%, #f3e5f5 100%);">
            <q-card-section class="column items-center">
              <div class="text-subtitle1 text-purple-12 text-weight-bold q-mb-md">Mon Badge Actuel</div>
              <q-avatar size="120px" :color="currentBadge.color" text-color="white" class="shadow-5">
                <q-icon :name="currentBadge.icon" size="80px" />
              </q-avatar>
               <div class="text-h5 text-weight-bold q-mt-md" :style="{ color: currentBadge.color }">{{ currentBadge.label }}</div>
               <div class="text-subtitle2 text-grey-8 q-mt-xs">{{ currentBadge.description }}</div>

            </q-card-section>
          </q-card>
        </div>

        <!-- Top 5 Leaderboard -->
        <div class="col-12 col-md-8">
          <q-card class="q-pa-md full-height" flat bordered>
            <q-card-section>
              <div class="text-h5 text-purple-12 text-weight-bold q-mb-md">🏆 Top 5 des Étudiants</div>
              <q-list separator>
                <q-item v-for="(student, index) in leaderboard" :key="index" class="q-py-sm">
                  <q-item-section avatar>
                    <q-avatar :color="index === 0 ? 'amber' : (index === 1 ? 'grey-4' : (index === 2 ? 'orange-4' : 'purple-1'))" 
                              :text-color="index < 3 ? 'black' : 'purple-10'" size="md">
                      {{ index + 1 }}
                    </q-avatar>
                  </q-item-section>
                  <q-item-section>
                    <q-item-label class="text-weight-bold">{{ student.name }}</q-item-label>
                    <q-item-label caption>Moyenne Générale</q-item-label>
                  </q-item-section>
                  <q-item-section side>
                    <q-badge :color="index === 0 ? 'amber' : 'purple-7'" class="text-subtitle2 q-pa-sm">
                      {{ student.avg }}%
                    </q-badge>
                  </q-item-section>
                </q-item>
                <div v-if="leaderboard.length === 0" class="text-center q-pa-md text-grey-6">
                  Aucune donnée disponible pour le classement.
                </div>
              </q-list>
            </q-card-section>
          </q-card>
        </div>
      </div>

      <q-card class="q-pa-lg full-width" flat bordered>
        <q-card-section>
          <div class="text-h5 text-purple-12 text-weight-bold">Statistiques de mes résultats</div>

          <div class="row q-gutter-xl q-mt-md justify-around">
            <div class="column items-center">
              <q-knob
                :model-value="averageGrade"
                show-value
                size="100px"
                :thickness="0.22"
                color="purple-7"
                track-color="purple-1"
                class="text-purple-7"
                :min="0"
                :max="100"
                readonly
              >
                {{ displayAverageGrade }}%
              </q-knob>
              <div class="text-subtitle1 text-grey-8 q-mt-sm">Moyenne Générale</div>
            </div>

            <div class="column items-center">
              <div class="text-h4 text-purple-7 text-weight-bold">{{ totalExams }}</div>
              <div class="text-subtitle1 text-grey-8">Évaluations Passées</div>
            </div>

            <div class="column items-center">
              <div class="text-h4 text-purple-7 text-weight-bold">{{ maxAvgGrade }}%</div>
              <div class="text-subtitle1 text-grey-8">Meilleure Note Obtenue</div>
            </div>
          </div>
        </q-card-section>
      </q-card>

      <q-card class="q-pa-lg full-width" flat bordered>
        <q-card-section class="q-pb-none">
          <div class="text-h5 text-purple-12 text-weight-bold">Résultats par Évaluation Passée 📈</div>
          <div class="text-subtitle2 text-grey-7">Visualisation de vos scores sur l'ensemble de vos participations (Hors entraînements).</div>
        </q-card-section>

        <q-card-section class="row q-gutter-md q-pt-md">
          <q-select
            class="col-12 col-md-5"
            outlined
            dense
            v-model="selectedExams"
            :options="examOptions"
            label="Filtrer par Évaluation"
            multiple
            emit-value
            map-options
            options-dense
            clearable
            use-chips
            @clear="selectedExams = []"
          />
        </q-card-section>

        <q-card-section>
          <div v-if="chartData.length > 0" class="q-pa-md">
            <div class="bar-chart-container q-pa-md">
              <div class="bar-chart-header text-caption text-weight-bold text-grey-8 row justify-end">
                <span>total en 100%</span>
              </div>
              <div class="bar-chart-grid">
                <div
                  v-for="(item, index) in chartData"
                  :key="index"
                  class="chart-item column items-center"
                >
                  <div class="bar-label text-caption text-purple-12">{{ item.label }}</div>

                  <div
                    class="chart-bar"
                    :style="{ height: item.percentage + '%', backgroundColor: getColor(index) }"
                  >
                    <span class="bar-value text-white text-weight-bold">{{ item.percentage }}%</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="text-center q-pa-md text-grey-6">
            Vous n'avez passé aucune évaluation réelle (ou les données ne sont pas chargées).
          </div>
        </q-card-section>
      </q-card>


    </div>
  </q-page>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { verifyUR } from '../composables/verificationU'

import { Cookies } from 'quasar';

const { verifyUserRole } = verifyUR()

const router = useRouter();

// --- 0. Configuration de base ---
const BASE_STRAPI_URL = 'http://10.0.52.187:1337'
const currentUserId = ref(null);
const userToken = Cookies.get('token_user');
const currentUserData = ref(null);


// --- 1. États pour l'entrée de code ---
const text = ref('');
const loading = ref(false);
const errorMessage = ref('');

// --- 2. États pour les Statistiques (Historique) ---
const examsData = ref([])
const selectedExams = ref([])
const examOptions = ref([])
const leaderboard = ref([])

// --- 3. Badge Logic ---
const currentBadge = computed(() => {
  const avg = averageGrade.value;
  if (avg === 100) return { label: 'Platine', color: 'blue-10', icon: 'workspace_premium', description: 'Perfection absolue ! Tu as plus de 100% de réussite' };
  if (avg >= 75) return { label: 'Or', color: 'amber-8', icon: 'emoji_events', description: 'Excellent travail ! Tu as plus de 75% de réussite' };
  if (avg >= 50) return { label: 'Argent', color: 'grey-5', icon: 'military_tech', description: 'Bonne progression. Tu as plus de 50% de réussite' };
  return { label: 'Bronze', color: 'orange-8', icon: 'stars', description: 'Continuez vos efforts! Tu as moins de 50% de réussite' };
})



// ==============================================
// LOGIQUE DE L'ENTRÉE DE CODE (Inchangée)
// ==============================================

const verifyCode = async () => {
  if (!text.value.trim()) {
    errorMessage.value = 'Veuillez entrer un code';
    return;
  }

  loading.value = true;
  errorMessage.value = '';

  try {
    const url = `${BASE_STRAPI_URL}/api/exams?filters[code][$eq]=${text.value.trim()}&populate=*`;
    console.log('Verifying code at:', url);
    const response = await fetch(url, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${userToken}`
      }
    });

    const data = await response.json();
    console.log('Verify code response:', data);

    if (response.ok && data.data && data.data.length > 0) {
      const exam = data.data[0];
      const attr = exam.attributes || exam;
      
      // D'après les logs, le champ de statut s'appelle 'role'
      const statusValue = attr.role || attr.status;
      const status = statusValue ? String(statusValue).trim().toLowerCase() : '';

      console.log('Detected status (from role/status):', status);

      if (status === 'ouvert' || status === 'entrainement') {
        const quizzData = attr.idQuizz || attr.quizz || attr.quiz;
        const idQuizz = quizzData?.id || quizzData;

        router.push({
          path: '/Pexam',
          query: {
            idExam: exam.id,
            idQuizz: idQuizz,
            status: statusValue
          }
        });
      } else {
        errorMessage.value = `Cet examen est ${statusValue || 'fermé'}, impossible de le passer.`;
      }

    } else {
      console.warn('Exam not found with code:', text.value.trim());
      // Debug: essayer de lister TOUS les examens pour voir les codes dispo
      try {
          const debugRes = await fetch(`${BASE_STRAPI_URL}/api/exams`, {
              headers: { Authorization: `Bearer ${userToken}` }
          });
          const debugData = await debugRes.json();
          console.log('Available exams in Strapi:', debugData);
      } catch (e) {
          console.error('Failed to fetch all exams for debug', e);
      }
      errorMessage.value = 'Code d\'examen invalide';
    }

  } catch (error) {
    console.error('Erreur lors de la vérification du code:', error);
    errorMessage.value = 'Une erreur est survenue lors de la vérification du code';
  } finally {
    loading.value = false;
  }
};




// ==============================================
// LOGIQUE DES STATISTIQUES (Historique)
// ==============================================

const chartData = computed(() => {
    let filteredData = examsData.value;

    if (selectedExams.value.length > 0) {
        filteredData = filteredData.filter(item =>
            selectedExams.value.includes(item.idExam)
        );
    }

    return filteredData.map(item => ({
        label: item.nom,
        percentage: parseFloat(item.reussite.replace('%', '')) || 0,
        date: item.date_exam
    })).sort((a, b) => new Date(a.date) - new Date(b.date));
})

function getColor(index) {
  const colors = [
    '#9C27B0',
    '#4DB6AC',
    '#FFB74D',
    '#64B5F6',
    '#E91E63'
  ];
  return colors[index % colors.length];
}

const averageGrade = computed(() => {
    const grades = chartData.value.map(item => item.percentage);
    if (grades.length === 0) return 0;
    const sum = grades.reduce((a, b) => a + b, 0);
    return parseFloat((sum / grades.length).toFixed(1));
})

const displayAverageGrade = computed(() => {
    return averageGrade.value.toFixed(1);
})

const totalExams = computed(() => examsData.value.length)

const maxAvgGrade = computed(() => {
  if (chartData.value.length === 0) return '0.0'
  const grades = chartData.value.map(item => item.percentage)
  return Math.max(...grades).toFixed(1)
})


// --- Fonction de chargement API (Strapi) ---

const userName = ref('');

async function loadUserData() {
    if (!userToken) return;
    try {
        const res = await fetch(`${BASE_STRAPI_URL}/api/users/me?populate=role`, {
            headers: { Authorization: `Bearer ${userToken}` }
        })
        if (res.ok) {
            const data = await res.json();
            currentUserData.value = data;
            currentUserId.value = data.id;
            userName.value = data.username || data.login || '';
            console.log('User logged in:', { id: currentUserId.value, name: userName.value });
        }
    } catch (err) {
        console.error('Erreur chargement profil Strapi:', err);
    }
}


async function loadExamsForStats() {
    if (!currentUserId.value) return;
    try {
        const url = `${BASE_STRAPI_URL}/api/takeexams?populate=*`
        const res = await fetch(url, {
            headers: { Authorization: `Bearer ${userToken}` }
        })
        const responseData = await res.json()
        console.log('Exams for stats raw data:', responseData);

        if (responseData.data && Array.isArray(responseData.data)) {
            // Filtrer par utilisateur dans le frontend pour plus de sécurité

            const userResults = responseData.data.filter(item => {
                const attr = item.attributes || item;
                const userData = attr.id_s11?.data || attr.id_s11;
                if (!userData) return false;
                
                const val = userData.id || userData;
                return String(val) === String(currentUserId.value) || 
                       String(val).toLowerCase() === String(userName.value).toLowerCase();
            });


            const cleanData = userResults.filter(item => {
                const attr = item.attributes || item;
                const examAttr = attr.idExam?.data?.attributes || attr.idExam;
                // Le champ s'appelle 'role' dans Strapi
                const status = (examAttr?.role || examAttr?.status || '').toString().toLowerCase();
                return status !== 'entrainement';
            });


            examsData.value = cleanData.map(item => {
                const attr = item.attributes || item;
                const examAttr = attr.idExam?.data?.attributes || attr.idExam;

                const grade = parseFloat(attr.grade);

                return {
                    nom: examAttr?.name || 'Examen sans nom',

                    reussite: `${(grade * 5).toFixed(1)}%`,
                    idExam: item.id,
                    date_exam: attr.createdAt
                }
            })


            examOptions.value = examsData.value.map(item => ({
                label: item.nom,
                value: item.idExam
            }));
        } else {
             examsData.value = []
        }

    } catch (err) {
        console.error('Erreur chargement historique Strapi:', err)
        examsData.value = []
    }
}

async function loadLeaderboard() {
    try {
        // Test de différents noms de collection - 'takeexams' est le bon d'après les logs
        const possibleEndpoints = ['takeexams', 'take-exams', 'results', 'participations'];

        let data = null;

        for (const endpoint of possibleEndpoints) {

            const url = `${BASE_STRAPI_URL}/api/${endpoint}?populate=*`
            const res = await fetch(url, {
                headers: { Authorization: `Bearer ${userToken}` }
            })
            if (res.ok) {
                const responseData = await res.json();
                data = responseData.data;
                console.log(`Successfully fetched leaderboard from: ${endpoint}`);
                break;
            } else {

                console.warn(`Failed to fetch from ${endpoint}: ${res.status}`);
            }
        }

        if (data && Array.isArray(data)) {
            const userScores = {};
            
            data.forEach(item => {
                const attr = item.attributes || item;
                // Chercher l'utilisateur dans id_s11
                const userData = attr.id_s11?.data || attr.id_s11;
                
                if (!userData) return;
                
                const userId = userData.id || userData;



                const userAttr = userData.attributes || userData;
                const grade = parseFloat(attr.grade);
                
                if (isNaN(grade)) return;

                if (!userScores[userId]) {
                    const name = (userAttr.firstname && userAttr.lastname) 
                        ? `${userAttr.firstname} ${userAttr.lastname}` 
                        : (userAttr.username || userAttr.username || 'Étudiant Anonyme');
                        
                    userScores[userId] = {
                        name: name,
                        total: 0,
                        count: 0
                    };
                }
                userScores[userId].total += grade;
                userScores[userId].count += 1;
            });

            const calculatedLeaderboard = Object.values(userScores).map(u => ({
                name: u.name,
                avg: (u.total / u.count * 5).toFixed(1)
            })).sort((a, b) => b.avg - a.avg).slice(0, 5);

            leaderboard.value = calculatedLeaderboard;
        }
    } catch (err) {
        console.error('Erreur chargement leaderboard:', err);
    }
}




// --- Chargement initial ---
onMounted(async () => {
    verifyUserRole("admin", "logged_user", "/");
    await loadUserData();
    if (currentUserId.value) {
        await loadExamsForStats();
        await loadLeaderboard();
    }
})

</script>

<style scoped>
/* COULEURS ET GÉNÉRAL */
.text-purple-12 { color: #8E24AA; }
.bg-rose { background-color: #FFF4FF; }

/* STYLE DU GRAPHIQUE À BARRES */
.bar-chart-container {
  width: 100%;
  max-width: 800px;
  margin: auto;
  border-radius: 8px;
}

.bar-chart-header {
    height: 10px;
    padding-right: 15px;
}

.bar-chart-grid {
  display: flex;
  align-items: flex-end;
  height: 300px;
  border-left: 1px solid #ddd;
  border-bottom: 1px solid #ddd;
  padding-left: 10px;
  gap: 20px;
}

.chart-item {
  height: 100%;
  flex: 1;
  justify-content: flex-end;
}

.chart-bar {
  width: 70%;
  border-top-left-radius: 4px;
  border-top-right-radius: 4px;
  display: flex;
  justify-content: center;
  align-items: flex-start;
  transition: height 0.8s ease-out;
  min-height: 5px;
}

.bar-value {
  margin-top: 5px;
  font-size: 0.8em;
}

.bar-label {
    margin-top: 5px;
    height: 30px;
    text-align: center;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    max-width: 100%;
}
</style>
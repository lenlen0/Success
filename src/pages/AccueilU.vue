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
          <div class="text-subtitle2 text-grey-7">Visualisation de vos scores sur l'ensemble de vos participations.</div>
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
            Vous n'avez passé aucune évaluation (ou les données ne sont pas chargées).
          </div>
        </q-card-section>
      </q-card>

    </div>
  </q-page>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

// --- 0. Configuration de base ---
const currentUserId = ref(3); // ID de l'utilisateur connecté
const BASE_API_URL = 'http://10.0.52.142/success/api.php'

// --- 1. États pour l'entrée de code ---
const text = ref('');
const loading = ref(false);
const errorMessage = ref('');

// --- 2. États pour les Statistiques (Historique) ---
const examsData = ref([]) 
const selectedExams = ref([]) 
const examOptions = ref([]) 


// ==============================================
// LOGIQUE DE L'ENTRÉE DE CODE (Inchngée)
// ==============================================

const verifyCode = async () => {
    if (!text.value.trim()) {
      errorMessage.value = 'Veuillez entrer un code';
      return;
    }
    loading.value = true;
    errorMessage.value = '';
    try {
      // Utilisation de l'endpoint global pour trouver l'examen à partir du code
      const response = await fetch(`${BASE_API_URL}/show_exam`); 
      const data = await response.json();

      if (response.ok && Array.isArray(data)) {
        const exam = data.find(e => String(e.code).trim() === text.value.trim());

        if (exam) {
          router.push({
            path: '/Pexam',
            query: {
              idExam: exam.idExam,
              idQuizz: exam.idQuizz
            }
          });
        } else {
          errorMessage.value = 'Code d\'examen invalide';
        }
      } else {
        errorMessage.value = 'Erreur lors de la récupération des examens';
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


// --- Fonction de chargement API (Stats) ---

async function loadExamsForStats() {
    try {
        // 🚀 Utilisation de l'endpoint d'historique confirmé par api.php
        const url = `${BASE_API_URL}/show_passed_exam/${currentUserId.value}`
        const res = await fetch(url)
        const data = await res.json()
        
        if (Array.isArray(data)) {
            examsData.value = data.map(item => {
                
                // ⚠️ ADAPTATION SELON LA RÉPONSE DE L'API :
                // item.user_grade si vous avez fait la modification BDD, 
                // sinon item.avg_grade si vous n'avez pas touché le PHP
                const gradeField = item.user_grade !== undefined ? item.user_grade : item.avg_grade;

                return {
                    nom: item.exam_name,
                    // Note individuelle mise à l'échelle (ex: si la note max est 20 et item.grade = 15, on fait 15 * 5 = 75%)
                    reussite: `${gradeField !== null ? (gradeField * 5).toFixed(1) : 0}%`, 
                    idExam: parseInt(item.idExam),
                    idQuizz: item.idQuizz,
                    date_exam: item.date_exam
                }
            })
            
            // Options de sélection (basées sur l'historique de l'utilisateur)
            examOptions.value = examsData.value
                .map(item => ({
                    label: item.nom,
                    value: item.idExam 
                }));
        } else {
             examsData.value = []
        }

    } catch (err) {
        console.error('Erreur chargement historique des examens:', err)
        examsData.value = []
    }
}


// --- Chargement initial ---
onMounted(async () => {
    await loadExamsForStats() 
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
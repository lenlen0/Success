<template>
  <q-page class="flex flex-center q-pa-lg" style="background-color: #FFF4FF;">

    <div class="column items-center q-gutter-xl full-width" style="max-width: 1200px;">

      <q-card class="q-pa-lg full-width" flat bordered>
        <q-card-section>
          <div class="text-h5 text-purple-12 text-weight-bold">Statistiques Clés</div>

          <div class="row q-gutter-xl q-mt-md justify-around">
            <div class="column items-center">
              <q-knob
                v-model="averageGrade"
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
                {{ averageGrade }}%
              </q-knob>
              <div class="text-subtitle1 text-grey-8 q-mt-sm">Moyenne Générale</div>
            </div>

            <div class="column items-center">
              <div class="text-h4 text-purple-7 text-weight-bold">{{ totalExams }}</div>
              <div class="text-subtitle1 text-grey-8">Évaluations Crées</div>
            </div>

            <div class="column items-center">
              <div class="text-h4 text-purple-7 text-weight-bold">{{ maxAvgGrade }}%</div>
              <div class="text-subtitle1 text-grey-8">Meilleure Réussite</div>
            </div>
          </div>
        </q-card-section>
      </q-card>

      <q-card class="q-pa-lg full-width" flat bordered>
        <q-card-section class="q-pb-none">
          <div class="text-h5 text-purple-12 text-weight-bold">Moyennes de réussite par Examen 📈</div>
          <div class="text-subtitle2 text-grey-7">Visualisation des performances sur l'ensemble des évaluations.</div>
        </q-card-section>

        <q-card-section class="row q-gutter-md q-pt-md">

          <q-select
            class="col-12 col-md-5"
            outlined
            dense
            v-model="selectedGroups"
            :options="groupOptions"
            label="Filtrer par Groupe"
            multiple
            emit-value
            map-options
            options-dense
            clearable
            use-chips
            @clear="selectedGroups = []"
          />

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
            Aucune donnée d'examen correspond aux filtres actuels.
          </div>
        </q-card-section>
      </q-card>

    </div>
  </q-page>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { verifyR } from '../composables/verification'

const { verifyRole } = verifyR()

// --- 1. États Réactifs et Données ---
const examsData = ref([])
const groupOptions = ref([])
const examOptions = ref([])

// --- États pour le Filtrage (Modèle des Q-Select) ---
const selectedGroups = ref([])
const selectedExams = ref([])

// --- 2. Fonctions Graphique et Statistiques (Basées sur les FILTRES) ---

// Mappage et FILTRAGE des données pour le graphique
const chartData = computed(() => {

    let filteredData = examsData.value;

    // 1. Filtrer par Groupe : Utilise des NOMBRES entiers pour la comparaison.
    if (selectedGroups.value.length > 0) {
        filteredData = filteredData.filter(item =>
            selectedGroups.value.includes(item.idGroup)
        );
    }

    // 2. Filtrer par Examen (Évaluation) : Utilise des NOMBRES entiers pour la comparaison.
    if (selectedExams.value.length > 0) {
        filteredData = filteredData.filter(item =>
            selectedExams.value.includes(item.idExam)
        );
    }

    // Transformation des données filtrées pour l'affichage du graphique
    return filteredData.map(item => ({
        label: item.nom,
        // Conversion de la chaîne "%" en nombre
        percentage: parseFloat(item.reussite.replace('%', '')) || 0
    }))
})

// Fonction pour varier la couleur des barres
function getColor(index) {
  const colors = [
    '#9C27B0', // purple
    '#4DB6AC', // teal
    '#FFB74D', // orange
    '#64B5F6', // light-blue
    '#E91E63' // pink
  ];
  return colors[index % colors.length];
}

// Calcule la moyenne générale (utilise les données filtrées si des filtres sont appliqués!)
const averageGrade = computed(() => {
    // Utilise chartData qui est déjà filtré
    const grades = chartData.value.map(item => item.percentage);

    if (grades.length === 0) return 0;

    const sum = grades.reduce((a, b) => a + b, 0);
    return (sum / grades.length).toFixed(1);
})

const totalExams = computed(() => chartData.value.length) // Compte les examens FILTRÉS

const maxAvgGrade = computed(() => {
  if (chartData.value.length === 0) return '0.0'

  const grades = chartData.value.map(item => item.percentage)
  return Math.max(...grades).toFixed(1)
})

// --- 3. Logique de chargement des données (Cohérence des NOMBRES) ---

async function loadGroups() {
    try {
        const res = await fetch('http://10.0.52.142/success/api.php/show_group')
        const data = await res.json()
        // S'assurer que le value du Q-Select est un NOMBRE
        groupOptions.value = data.map(g => ({ label: g.name, value: parseInt(g.idGroup) }))
    } catch (err) {
        console.error('Erreur chargement groupes:', err)
    }
}

async function loadExams() {
    try {
        const res = await fetch('http://10.0.52.142/success/api.php/show_exam')
        const data = await res.json()

        // --- FILTRE AJOUTÉ ICI ---
        // On exclut les entraînements en vérifiant le statut
        const cleanData = data.filter(item => {
            if (!item.status) return true;
            return !item.status.toLowerCase().includes('entrainement');
        });

        // Stockage des données brutes enrichies avec les IDs pour le filtrage
        examsData.value = cleanData.map(item => ({
            nom: item.exam_name,
            // Conversion de la moyenne en % (Assurez-vous que le calcul * 5 est correct selon votre API)
            reussite: `${item.avg_grade !== null ? (item.avg_grade * 5).toFixed(1) : 0}%`,
            // CORRIGÉ : S'assurer que les IDs sont des NOMBRES entiers
            idGroup: parseInt(item.idGroup),
            idExam: parseInt(item.idExam),
        }))

        // Mise à jour des options de sélection d'examen
        examOptions.value = examsData.value.map(item => ({
            label: item.nom,
            // La valeur est le NOMBRE idExam
            value: item.idExam
        }));

    } catch (err) {
        console.error('Erreur chargement examens pour stats:', err)
        examsData.value = []
    }
}

// Chargement initial
onMounted(async () => {
    verifyRole("admin", "/AccueilU")
    await loadGroups()
    await loadExams()
})
</script>

<style scoped>
/* COULEURS ET GÉNÉRAL */
.text-purple-12 { color: #8E24AA; }
.bg-rose { background-color: #FFF4FF; }

/* STYLE DU GRAPHIQUE À BARRES (CSS inchangé) */
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
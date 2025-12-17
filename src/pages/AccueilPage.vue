<template>
  <q-page class="flex flex-center q-pa-lg" style="background-color: #FFF4FF;">

    <div class="column items-start q-gutter-xl full-width" style="max-width: 1200px;">

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
                :min="0" :max="100" readonly
              >
                {{ averageGrade }}%
              </q-knob>
              <div class="text-subtitle1 text-grey-8 q-mt-sm">Moyenne Générale</div>
            </div>

            <div class="column items-center">
              <div class="text-h4 text-purple-7 text-weight-bold">{{ totalExams }}</div>
              <div class="text-subtitle1 text-grey-8">Évaluations Créées</div>
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
        </q-card-section>

        <q-card-section class="row q-gutter-md q-pt-md">
          <q-select
            class="col-12 col-md-5"
            outlined dense
            v-model="selectedGroups"
            :options="groupOptions"
            label="Filtrer par Groupe"
            multiple emit-value map-options options-dense clearable use-chips
            @clear="selectedGroups = []"
          />

          <q-select
            class="col-12 col-md-5"
            outlined dense
            v-model="selectedExams"
            :options="examOptions"
            label="Filtrer par Évaluation"
            multiple emit-value map-options options-dense clearable use-chips
            @clear="selectedExams = []"
          />
        </q-card-section>

        <q-card-section>
          <div v-if="chartData.length > 0" class="q-pa-md">
            <div class="bar-chart-wrapper">
              <div class="bar-chart-grid">
                <div
                  v-for="(item, index) in chartData"
                  :key="index"
                  class="chart-item"
                >
                  <div
                    class="chart-bar"
                    :style="{ 
                      height: Math.min(item.percentage, 100) + '%', 
                      backgroundColor: getColor(index) 
                    }"
                  >
                    <div class="bar-info-overlay">
                      <div class="text-weight-bolder text-purple-12" style="font-size: 0.75rem;">
                        {{ item.percentage }}%
                      </div>
                      <div class="text-grey-9 text-weight-medium text-caption label-truncate">
                        {{ item.label }}
                      </div>
                    </div>

                    <q-tooltip 
                      anchor="center middle" 
                      self="center middle" 
                      class="bg-purple-9 text-body2 text-weight-bold"
                    >
                      {{ item.label }}
                    </q-tooltip>
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

const examsData = ref([])
const groupOptions = ref([])
const examOptions = ref([])
const selectedGroups = ref([])
const selectedExams = ref([])

const chartData = computed(() => {
    let filteredData = examsData.value;
    if (selectedGroups.value.length > 0) {
        filteredData = filteredData.filter(item => selectedGroups.value.includes(item.idGroup));
    }
    if (selectedExams.value.length > 0) {
        filteredData = filteredData.filter(item => selectedExams.value.includes(item.idExam));
    }
    return filteredData.map(item => ({
        label: item.nom,
        percentage: parseFloat(item.reussite.replace('%', '')) || 0
    }))
})

function getColor(index) {
  const colors = ['#673AB7', '#4DB6AC', '#FF7043', '#2196F3', '#E91E63', '#FFC107', '#00BCD4', '#8BC34A'];
  return colors[index % colors.length];
}

const averageGrade = computed(() => {
    const grades = chartData.value.map(item => item.percentage);
    if (grades.length === 0) return 0;
    const sum = grades.reduce((a, b) => a + b, 0);
    return (sum / grades.length).toFixed(1);
})

const totalExams = computed(() => chartData.value.length)
const maxAvgGrade = computed(() => {
  if (chartData.value.length === 0) return '0.0'
  const grades = chartData.value.map(item => item.percentage)
  return Math.max(...grades).toFixed(1)
})

async function loadGroups() {
    try {
        const res = await fetch('http://10.0.52.142/success/api.php/show_group')
        const data = await res.json()
        groupOptions.value = data.map(g => ({ label: g.name, value: parseInt(g.idGroup) }))
    } catch (err) { console.error(err) }
}

async function loadExams() {
    try {
        const res = await fetch('http://10.0.52.142/success/api.php/show_exam')
        const data = await res.json()
        const cleanData = data.filter(item => {
            if (!item.status) return true;
            return !item.status.toLowerCase().includes('entrainement');
        });
        examsData.value = cleanData.map(item => ({
            nom: item.exam_name,
            reussite: `${item.avg_grade !== null ? (item.avg_grade * 5).toFixed(1) : 0}%`,
            idGroup: parseInt(item.idGroup),
            idExam: parseInt(item.idExam),
        }))
        examOptions.value = examsData.value.map(item => ({ label: item.nom, value: item.idExam }));
    } catch (err) { console.error(err); examsData.value = [] }
}

onMounted(async () => {
    verifyRole("admin", "/AccueilU")
    await loadGroups(); await loadExams()
})
</script>

<style scoped>
.text-purple-12 { color: #8E24AA; }

.bar-chart-wrapper {
  width: 100%;
  display: flex;
  justify-content: flex-start; 
}

.bar-chart-grid {
  display: flex;
  align-items: flex-end;
  height: 350px; 
  border-left: 2px solid #ddd;
  border-bottom: 2px solid #ddd;
  padding: 60px 10px 0 10px; 
  gap: 20px;
}

.chart-item {
  height: 100%;
  width: 65px; 
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
}

.chart-bar {
  width: 100%;
  border-top-left-radius: 4px;
  border-top-right-radius: 4px;
  position: relative;
  transition: height 0.8s ease-out;
  cursor: pointer;
}

.bar-info-overlay {
  position: absolute;
  top: -45px; 
  left: 50%;
  transform: translateX(-50%);
  width: 80px; 
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  pointer-events: none; /* Laisse le survol passer à la barre pour le tooltip */
}

.label-truncate {
  width: 100%;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  line-height: 1.2;
}

.chart-bar:hover {
  filter: brightness(1.1);
}
</style>
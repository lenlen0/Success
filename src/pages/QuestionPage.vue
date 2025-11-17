<template>
  <div class="q-pa-md q-gutter-sm" style="background-color: #FFF4FF; min-height: 100vh;">
    <!-- Titre -->
    <h4
      class="text-purple-12 text-weight-bold"
      style="margin-bottom: 8px;"
    >Question</h4>

    <!-- Bouton pour ouvrir la popup -->
    <q-btn round color="purple-7" icon="add" @click="popup = true" />

    <!-- Popup modale pour ajout/modif question/réponses -->
    <q-dialog v-model="popup">
      <q-card style="width:400px;">
        <q-card-section class="flex column justify-evenly items-center q-pa-none" id="formulaire-ajout-question">
          <q-card-section class="bg-purple-1 text-purple-10 q-mb-sm" style="width:100%;">
            <div class="text-h6">Nouvelle évaluation</div>
          </q-card-section>
          <q-input rounded outlined :model-value="editQuestion" label="Question" class="q-mb-sm"/>

          <!-- Réponses avec bouton pour enlever si > 2 -->
          <div
            v-for="(reponse, idx) in editReponses"
            :key="'reponse'+(idx+1)"
            class="q-mb-sm row items-center"
          >
            <q-input
              rounded
              outlined
              :model-value="editReponses[idx]"
              :label="'Réponse ' + (idx + 1)"
              class="col"
            />
            <q-btn
              v-if="editReponses.length > 2"
              round
              dense
              flat
              color="red"
              icon="remove"
              class="q-ml-xs"
              :disable="editReponses.length <= 2"
              :title="'Enlever cette réponse'"
            />
          </div>

          <!-- Bouton pour ajouter une réponse, max 5 -->
          <q-btn
            v-if="editReponses.length < 5"
            round
            color="purple-7"
            icon="add"
            class="q-mb-sm"
            :disable="editReponses.length>=5"
            :title="editReponses.length>=5 ? 'Nombre maximum de réponses atteint' : 'Ajouter une réponse'"
          />
        </q-card-section>
        <q-card-actions align="right">
          <q-btn rounded label="Fermer" color="purple-7" v-close-popup />
          <q-btn
            rounded
            v-if="!isEditMode"
            label="Ajouter"
            color="purple-7"
          />
          <q-btn
            v-if="isEditMode"
            label="Enregistrer"
            color="purple-7"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Tableau des questions ajoutées -->
    <div class="q-mt-md">
      <q-table
        flat
        bordered
        :rows="questions"
        :columns="tableColumns"
        :loading="loading"
        row-key="__rowKey"
        color="purple-7"
        card-class=""
        table-class=""
        table-header-class="bg-purple-1"
      >
        <template #body-cell-Num="props">
          <q-td :props="props">
            {{ props.rowIndex + 1 }}
          </q-td>
        </template>
        <template #body-cell-Nom="props">
          <q-td :props="props">
            {{ props.row.question || props.value || '-' }}
          </q-td>
        </template>
        <template #body-cell-reponses="props">
          <q-td :props="props">
            <ol style="margin: 0; padding-left: 18px;">
              <li v-for="(r, i) in props.row.reponses" :key="'ans'+i">{{ r }}</li>
            </ol>
          </q-td>
        </template>
        <template #body-cell-actions="props">
          <q-td :props="props">
            <q-btn
              icon="edit"
              color="purple-7"
              dense
              flat
              round
              size="sm"
              class="q-mr-xs"
              :title="'Modifier cette question'"
            />
            <q-btn
              icon="delete"
              color="purple-7"
              dense
              flat
              round
              size="sm"
              :title="'Supprimer cette question'"
            />
          </q-td>
        </template>
      </q-table>
    </div>
  </div>
</template>
<script setup>
import { ref, onMounted } from 'vue'

// État statique pour l'affichage visuel
const popup = ref(false)
const isEditMode = ref(false)
const editQuestion = ref('Exemple de question')
const editReponses = ref(['Réponse 1', 'Réponse 2', 'Réponse 3'])
const loading = ref(false)

// Données pour le tableau (chargées depuis l'API)
const questions = ref([])

// Fonction pour charger les questions depuis l'API
async function loadQuestions() {
  loading.value = true
  try {
    const response = await fetch('http://10.0.52.211/success/api.php/question')
    if (!response.ok) {
      throw new Error(`Erreur HTTP: ${response.status}`)
    }
    const data = await response.json()
    
    // Transformer les données de l'API pour correspondre au format attendu
    let questionsData = []
    
    if (Array.isArray(data)) {
      questionsData = data
    } else if (data.questions && Array.isArray(data.questions)) {
      questionsData = data.questions
    } else if (data.data && Array.isArray(data.data)) {
      questionsData = data.data
    } else {
      questions.value = []
      loading.value = false
      return
    }
    
    // Mapper les données selon la structure de la base de données
    // Question table: idQuestion, name, idQuizz
    // Answer table: idAnswer, name, isCorrect, idQuestion
    questions.value = questionsData.map((item, index) => {
      // Récupérer le texte de la question
      const questionText = item.name || item.question || item.texte || item.nom || item.libelle || ''
      
      // Récupérer les réponses (peuvent être dans différents formats)
      let reponses = []
      if (item.answers && Array.isArray(item.answers)) {
        reponses = item.answers.map(ans => ans.name || ans.text || ans.texte || ans)
      } else if (item.reponses && Array.isArray(item.reponses)) {
        reponses = item.reponses.map(rep => rep.name || rep.text || rep.texte || rep)
      } else if (item.Answer && Array.isArray(item.Answer)) {
        reponses = item.Answer.map(ans => ans.name || ans.text || ans.texte || ans)
      } else if (Array.isArray(item.reponse)) {
        reponses = item.reponse.map(rep => rep.name || rep.text || rep.texte || rep)
      }
      
      return {
        __rowKey: item.idQuestion || item.id || item.ID || index + 1,
        question: questionText,
        reponses: reponses
      }
    })
  } catch {
    // En cas d'erreur, on garde un tableau vide ou on affiche un message
    questions.value = []
  } finally {
    loading.value = false
  }
}

// Charger les questions au montage du composant
onMounted(() => {
  loadQuestions()
})

// Colonnes du q-table
const tableColumns = [
  {
    name: 'Num',
    required: true,
    label: 'Num',
    align: 'left',
    field: () => null,
    sortable: false
  },
  {
    name: 'Nom',
    required: true,
    label: 'Nom',
    align: 'left',
    field: row => row.question,
    sortable: false
  },
  {
    name: 'reponses',
    required: true,
    label: 'Réponses',
    align: 'left',
    field: row => row.reponses,
    sortable: false
  },
  {
    name: 'actions',
    required: true,
    label: 'Actions',
    align: 'left',
    field: () => null,
    sortable: false
  }
]
</script>

<style scoped>
.text-purple-12 {
  color: var(--q-color-purple-12);
}
</style>

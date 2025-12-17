<template>
  <q-page class="flex flex-center" style="background-color: #FFF4FF;">
    <div class="q-pa-md" style="width: 100%; max-width: 1000px;">

      <h1 class="text-h4 text-purple-12">Questionnaires</h1>

      <q-btn round color="purple-7" icon="add" style="margin: 8px" @click="openAddDialog" />
      <br/><br/>

      <div class="table-responsive-wrapper">
        <q-table
          color="primary"
          card-class="bg-white"
          table-header-class="bg-purple-1 text-purple-10"
          flat bordered
          :rows="rows"
          :columns="columns"
          row-key="id"
          class="sticky-header-table"
        >
          <template v-slot:body-cell-isEnableBoolean="props">
            <q-td :props="props" auto-width class="text-center">
              <div
                class="row items-center justify-center q-gutter-xs"
                @click="toggleStatus(props.row)"
                style="cursor: pointer;"
              >
                <div
                  class="status-box"
                  :style="{ backgroundColor: props.row.isEnableBoolean ? '#4CAF50' : '#f44336', width: '12px', height: '12px' }"
                />
                <span style="font-size: 13px; white-space: nowrap;">{{ props.row.isEnableBoolean ? 'Activé' : 'Désactivé' }}</span>
              </div>
            </q-td>
          </template>

          <template v-slot:body-cell-Action="props">
            <q-td :props="props" auto-width class="text-center">
              <q-btn flat color="purple-7" icon="edit" align="center" @click="editRow(props.row)" />
              <q-btn flat color="purple-7" icon="delete_outline" align="center" @click="deleteRow(props.row)" />
            </q-td>
          </template>
        </q-table>
      </div>
    </div>

    <q-dialog v-model="showDialog" persistent>
      <q-card style="min-width: 400px;">
        <q-card-section class="bg-purple-1 text-purple-10">
        </q-card-section>

        <q-card-section class="q-pt-sm">
          <div class="column q-gutter-md">
            <q-input
              v-model="newQuizz.name"
              rounded
              outlined
              label="Nom du Quizz"
            />

            <q-toggle
              v-model="newQuizz.isEnable"
              label="Activer le questionnaire (Visible)"
              color="purple-7"
            />
          </div>

        </q-card-section>

        <q-card-actions align="right">
          <q-btn unelevated rounded color="purple-7" label="Annuler" v-close-popup />
          <q-btn
            unelevated
            rounded
            color="purple-7"
            label="Ajouter"
            @click="addQuizz()"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Dialog d'edit-->
    <q-dialog v-model="showEditDialog" persistent>
      <q-card style="min-width: 400px;">
        <q-card-section class="bg-purple-1 text-purple-10">
        </q-card-section>

        <q-card-section class="q-pt-none q-pb-sm">
          <div class="row items-center q-gutter-md q-mt-md">
            <q-btn round color="purple-7" icon="assignment" aria-label="Questions" @click="goToQuestionPage"/>
            <span class="text-body2">Gérer les questions</span>
          </div>
        </q-card-section>

        <q-card-section class="q-pt-sm">
          <div class="column q-gutter-md">
            <q-input
              v-model="editingQuizz.name"
              rounded
              outlined
              label="Nom du Quizz"
            />

            <q-toggle
              v-model="editingQuizz.isEnable"
              label="Activer le questionnaire (Visible)"
              color="purple-7"
            />
          </div>

        </q-card-section>

        <q-card-actions align="right">
          <q-btn unelevated rounded color="purple-7" label="Annuler" v-close-popup />
          <q-btn
            unelevated
            rounded
            color="purple-7"
            label="Modifier"
            @click="editQuizz()"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { verifyR } from '../composables/verification'
import { Cookies } from 'quasar'

const { verifyRole } = verifyR()

// NOTE: J'ai déplacé columns ici pour qu'il soit accessible sans "return"
const columns = [
  { name: 'name', align: 'center', label: 'NOM', field: 'name', sortable: true },
  { name: 'Nombre_De_Questions', label: 'Nombre De Questions', field: 'Nombre_De_Questions', align: 'center'},
  { name: 'isEnableBoolean', label: 'Statut', field: 'isEnableBoolean', align: 'center'},
  { name: 'Action', label: 'Action', field: 'id', align: 'center' }
]

const rows = ref([])
const showDialog = ref(false)
const showEditDialog = ref(false)

const newQuizz = ref({
  name: '',
  isEnable: false
})

const editingQuizz = ref({
  name: '',
  isEnable: false,
  documentId: ''
})

async function loadQuizz() {
  const token_user = Cookies.get('token_user')

  const response = await fetch('http://10.0.52.187:1337/api/quizzes', {
    headers: {
      "Content-Type": "application/json",
      "Authorization": `Bearer ${token_user}`
    }
  })

  if (!response.ok) throw new Error('Erreur HTTP ' + response.status)
  const { data: quizzes } = await response.json()

  const responseQuestions = await fetch(
    'http://10.0.52.187:1337/api/questions?populate=*',
    {
      headers: {
        "Content-Type": "application/json",
        "Authorization": `Bearer ${token_user}`
      }
    }
  )

  if (!responseQuestions.ok) throw new Error('Erreur HTTP ' + responseQuestions.status)
  const { data: questions } = await responseQuestions.json()

  rows.value = quizzes.map(quizz => {
    const quizzId = quizz.id

    const nbQuestions = questions.filter(question =>
      question.idQuizz?.id === quizzId
    ).length

    return {
      Id: quizz.id,
      name: quizz.name,
      Nombre_De_Questions: nbQuestions,
      isEnableBoolean: !!(
        quizz.isEnable === true ||
        quizz.isEnable === 1 ||
        quizz.isEnable === '1' ||
        quizz.isEnable === 'true'
      ),
      documentId: quizz.documentId
    }
  })
}

async function addQuizz() {
  const token_user = Cookies.get('token_user')
  const response = await fetch("http://10.0.52.187:1337/api/quizzes", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      'Authorization': `Bearer ${token_user}`
    },
    body: JSON.stringify({
      data: {
        name: newQuizz.value.name,
        isEnable: newQuizz.value.isEnable
      },
    })
  });

  if (!response.ok) {
    throw new Error("Erreur API " + response.status);
  }
  const data = await response.json();

  await loadQuizz();
  showDialog.value = false
  newQuizz.value.name = ''
  newQuizz.value.isEnable = false
  return data;
}

async function editQuizz() {
  const token_user = Cookies.get('token_user')
  const documentId = editingQuizz.value.documentId;

  const response = await fetch(`http://10.0.52.187:1337/api/quizzes/${documentId}`, {
    method: "PUT",
    headers: {
      "Content-Type": "application/json",
      'Authorization': `Bearer ${token_user}`
    },
    body: JSON.stringify({
      data: {
        name: editingQuizz.value.name,
        isEnable: editingQuizz.value.isEnable
      },
    })
  });

  if (!response.ok) {
    throw new Error("Erreur API " + response.status);
  }

  showEditDialog.value = false
  await loadQuizz()
}

// Lifecycle hooks fonctionnent de la même manière
onMounted(() => {
  verifyRole("admin", "/AccueilU")
  loadQuizz()
});

async function toggleStatus(row) {
  try {
    const token_user = Cookies.get('token_user')
    const documentId = row.documentId;

    const response = await fetch(`http://10.0.52.187:1337/api/quizzes/${documentId}`, {
    method: "PUT",
    headers: {
      "Content-Type": "application/json",
      'Authorization': `Bearer ${token_user}`
    },
      body: JSON.stringify({
        data: {
          name: row.NOM,
          isEnable: !row.isEnableBoolean
        }
      })
    });

    if (!response.ok) {
      throw new Error("Erreur API " + response.status);
    }

      row.isEnableBoolean = !row.isEnableBoolean;
      console.log("Statut mis à jour")
  } catch(err) {
  console.log(err)
  }
}

async function deleteRow(row) {
  const token_user = Cookies.get('token_user')
  const documentId = row.documentId;

  const response = await fetch(`http://10.0.52.187:1337/api/quizzes/${documentId}`, {
    method: 'DELETE',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token_user}`
    }
  });

   if (!response.ok) {
    throw new Error(`Erreur DELETE ${response.status}`)
  }

  await loadQuizz();
}

function editRow(row) {
  editingQuizz.value = {
    name: row.name,
    isEnable: row.isEnableBoolean,
    documentId: row.documentId
  }
  showEditDialog.value = true
}

function openAddDialog() {
  showDialog.value = true
}

function goToQuestionPage() {
  if (editingQuizz.value && editingQuizz.value.documentId) {
    window.location.href = '/#/question?idQuizz=' + editingQuizz.value.documentId;
  } else {
    console.warn("Aucun ID de quizz trouvé.");
  }
}
</script>

<style>
body{
  background-color: #FFF4FF;
}

.status-box {
  width: 16px;
  height: 16px;
  border-radius: 2px;
}

@media (max-width: 600px) {
  .table-responsive-wrapper {
    width: 100%;
    overflow-x: auto;
  }

  .table-responsive-wrapper .q-table {
    min-width: 500px;
  }
}
</style>

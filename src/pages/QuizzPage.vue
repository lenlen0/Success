<template>
  <q-page class="flex flex-center" style="background-color: #FFF4FF;">
    <div class="q-pa-md" style="width: 100%; max-width: 1000px;">

      <h1 class="text-h4 text-purple-12">Questionnaires</h1>

      <q-btn round color="purple-7" icon="add" style="margin: 8px" @click="openAddDialog" />
      <q-btn round color="purple-7" icon="smart_toy" />
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
              <q-btn flat color="purple-7" icon="delete_outline" align="center" @click="deleteRow(props.row)" />
              <q-btn flat color="purple-7" icon="edit" align="center" @click="editRow(props.row)" />
            </q-td>
          </template>
        </q-table>
      </div>
    </div>

    <q-dialog v-model="showDialog" persistent>
      <q-card style="min-width: 400px;">
        <q-card-section class="bg-purple-1 text-purple-10">
        </q-card-section>

        <q-card-section class="q-pt-none q-pb-sm">
          <div class="row items-center q-gutter-md q-mt-md" v-if="isEditing">
            <q-btn round color="purple-7" icon="assignment" aria-label="Questions" @click="goToQuestionPage"/>
            <span class="text-body2">Gérer les questions</span>
          </div>
        </q-card-section>

        <q-card-section class="q-pt-sm">
          <div class="column q-gutter-md">
            <q-input
              v-model="currentQuizz.name"
              rounded
              outlined
              label="Nom du Quiz"
            />

            <q-toggle
              v-model="currentQuizz.isEnable"
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
            :label="isEditing ? 'Modifier' : 'Ajouter'"
            @click="addQuizz(currentQuizz.name, currentQuizz.isEnable)"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

// NOTE: J'ai déplacé columns ici pour qu'il soit accessible sans "return"
const columns = [
  { name: 'NOM', align: 'center', label: 'NOM', field: 'NOM', sortable: true },
  { name: 'Nombre_De_Questions', label: 'Nombre De Questions', field: 'Nombre_De_Questions', align: 'center'},
  { name: 'isEnableBoolean', label: 'Statut', field: 'isEnableBoolean', align: 'center'},
  { name: 'Action', label: 'Action', field: 'id', align: 'center' }
]

const rows = ref([])
const showDialog = ref(false)


const editedQuizz = ref(null)
const currentQuizz = ref({
  name: '',
  isEnable: false
})

const isEditing = computed(() => editedQuizz.value !== null)

async function addQuizz(name, isEnable) {
  try {
    const endpoint = isEditing.value ? "http://10.0.52.142/success/api.php/edit_quizz" : "http://10.0.52.142/success/api.php/add_quizz"

    const body = isEditing.value
      ? {
          idQuizz: editedQuizz.value.id,
          name: name,
          isEnable: isEnable
        }
      : {
          name: name,
          isEnable: isEnable,
          id_s11: 3
        }

    const response = await fetch(endpoint, {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify(body)
    });

    if (!response.ok) {
      throw new Error("Erreur API " + response.status);
    }

    const data = await response.json();
    console.log(isEditing.value ? "Quizz modifié :" : "Quizz ajouté :", data);

    if (data.status === "success") {
      showDialog.value = false
      currentQuizz.value = { name: '', isEnable: false }
      editedQuizz.value = null
      await loadQuizz()
    }

    return data;

  } catch (err) {
    console.error("Erreur", err);
  }
}

async function loadQuizz() {
  try {
    const url = 'http://10.0.52.142/success/api.php/show_quizz';

    const response = await fetch(url)
    if (!response.ok) throw new Error('Erreur HTTP ' + response.status)

    const data = await response.json()

    const payload = Array.isArray(data) ? data : (Array.isArray(data.data) ? data.data : [])

    rows.value = payload.map(item => ({
      id: item.idQuizz || item.id || null,
      NOM: item.name,
      Nombre_De_Questions: item.nb_questions || item.nb_question || 0,
      isEnableBoolean: !!(
        item.isEnable === true ||
        item.isEnable === 1 ||
        item.isEnable === '1' ||
        item.isEnable === 'true'
      )
    }))
  } catch (err) {
    console.error('Impossible de charger les questionnaires :', err)
    rows.value = []
  }
}

// Lifecycle hooks fonctionnent de la même manière
onMounted(() => {
  loadQuizz()
});

async function toggleStatus(row) {
  try {
    const response = await fetch("http://10.0.52.142/success/api.php/edit_quizz", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({
        idQuizz: row.id,
        name: row.NOM,
        isEnable: !row.isEnableBoolean
      })
    });

    if (!response.ok) {
      throw new Error("Erreur API " + response.status);
    }

    const data = await response.json();
    if (data.status === "success") {
      row.isEnableBoolean = !row.isEnableBoolean;
      console.log("Statut mis à jour :", data);
    }
  } catch (err) {
    console.error("Erreur lors de la mise à jour du statut", err);
  }
}

async function deleteRow(row) {
  try {
    const response = await fetch('http://10.0.52.142/success/api.php/del_quizz', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({idQuizz: row.id})
    });
    if (!response.ok) throw new Error('Erreur HTTP ' + response.status);

    const data = await response.json();

    if (data.status === 'success') {
      console.log('QUIZZ', row.id, 'supprimé avec succès');
      await loadQuizz();
    } else {
      console.error('Erreur lors de la suppression:', data.message);
    }
  } catch (err) {
    console.error('Impossible de supprimer le quizz :', err);
  }
}

function editRow(row) {
  editedQuizz.value = row
  currentQuizz.value = {
    name: row.NOM,
    isEnable: row.isEnableBoolean
  }
  showDialog.value = true
}

function openAddDialog() {
  editedQuizz.value = null
  currentQuizz.value = { name: '', isEnable: false }
  showDialog.value = true
}

function goToQuestionPage() {
  if (editedQuizz.value && editedQuizz.value.id) {
    window.location.href = '/#/question?idQuizz=' + editedQuizz.value.id;
  } else {
    console.warn("Aucun ID de quiz trouvé.");
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

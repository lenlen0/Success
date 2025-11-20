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
        >
          <template v-slot:body-cell-isEnableBoolean="props">
            <q-td :props="props" class="flex items-center justify-center">
              <div>
                {{ props.row.isEnable ? 'Ouvert' : 'Fermé' }}
              </div>
            </q-td>
          </template>
          <template v-slot:body-cell-Action="props">
            <q-td :props="props" class="flex items-center justify-center">
              <div class="row items-center q-gutter-sm no-wrap">
                <q-btn flat color="purple-7" dense icon="delete" aria-label="Supprimer" @click="deleteQuizz(props.row)"/>
                <q-btn flat color="purple-7" dense icon="edit" aria-label="Éditer" @click="openEditDialog(props.row)"/>
              </div>
            </q-td>
          </template>
        </q-table>
      </div>
    </div>

    <q-dialog v-model="showDialog" persistent>
      <q-card style="min-width: 400px;">
        <q-card-section class="bg-purple-1 text-purple-10">
          <div class="text-h6">{{ isEditing ? 'Éditer le Questionnaire' : 'Nouveau Questionnaire' }}</div>
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

<script>
import { ref, computed, onMounted } from 'vue'

const columns = [
  { name: 'NOM', align: 'center', label: 'NOM', field: 'NOM', sortable: true },
  { name: 'Nombre_De_Questions', label: 'Nombre De Questions', field: 'Nombre_De_Questions', align: 'center'},
  { name: 'Status', label: 'Statut', field: 'isEnableBoolean', align: 'center'},
  { name: 'Action', label: 'Action', field: 'Action' , align: 'center' }
]

export default {
  setup () {
    const rows = ref([])
    const showDialog = ref(false)

    const currentUserId = ref(1)

    const editedQuizz = ref(null)
    const currentQuizz = ref({
      name: '',
      isEnable: false
    })

    async function addQuizz(name, isEnable) {
      try {
        const response = await fetch("http://10.0.52.142/success/api.php/add_quizz", {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify({
            name: name,
            isEnable: isEnable,
            id_s11: 3
          })
        });

        if (!response.ok) {
          throw new Error("Erreur API " + response.status);
        }

        const data = await response.json();
        console.log("Quizz ajouté :", data);

        if (data.status === "success") {
          showDialog.value = false
          await loadQuizz()
        }

        return data;

      } catch (err) {
        console.error("Erreur", err);
      }
    }

    const isEditing = computed(() => editedQuizz.value !== null)

    async function loadQuizz() {
      try {
        const url = 'http://10.0.52.142/success/api.php/show_quizz';

        const response = await fetch(url)
        if (!response.ok) throw new Error('Erreur HTTP ' + response.status)

        const data = await response.json()


        const payload = Array.isArray(data) ? data : (Array.isArray(data.data) ? data.data : [])

        rows.value = payload.map(item => ({
          id: item.id,
          NOM: item.name,
          Nombre_De_Questions: item.nb_questions || 0,
          isEnableBoolean: Boolean(item.isEnable === true || item.isEnable === 'true' || item.isEnable === 1 || item.isEnable === '1')
        }))
      } catch (err) {
        console.error('Impossible de charger les questionnaires :', err)
        rows.value = []
      }
    }

    onMounted(() => {
      loadQuizz()
    });

    function openAddDialog() {
      editedQuizz.value = null
      currentQuizz.value = { name: '', isEnableBoolean: true }
      showDialog.value = true
    }

    function openEditDialog(row) {
      editedQuizz.value = row
      currentQuizz.value = {
        name: row.NOM,
        isEnableBoolean: row.isEnableBoolean
      }
      showDialog.value = true
    }

    function deleteQuizz(rowToDelete) {
      const index = rows.value.indexOf(rowToDelete)
      if (index > -1) rows.value.splice(index, 1)
    }

    function goToQuestionPage() {
      console.log("Navigation vers les questions")
    }

    return {
      columns,
      rows,
      showDialog,
      currentQuizz,
      isEditing,
      currentUserId,
      openAddDialog,
      deleteQuizz,
      openEditDialog,
      goToQuestionPage,
      addQuizz,
      loadQuizz
    }
  }
}
</script>

<style>
body{
  background-color: #FFF4FF;
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

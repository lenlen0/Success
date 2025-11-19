<template>
  <q-page class="bg-rose column items-center q-pa-lg" style="background-color: #FFF4FF;">
    <div class="q-pa-md full-width">
      <!-- Titre -->
      <h4 class="text-purple-12 text-weight-bold" style="margin-bottom: 8px;">Groupes</h4>

      <!-- Bouton pour ouvrir le dialog -->
      <q-btn round color="purple-7" icon="add" style="margin: 8px" @click="showDialog = true" />
      <br /><br />

      <!-- Tableau Quasar -->
      <q-table
        flat
        bordered
        color="primary"
        card-class="bg-white"
        table-header-class="bg-purple-1 text-purple-10"
        :rows="rows"
        :columns="columns"
        row-key="Id"
      >
        <!-- Slot pour customiser la colonne "action" -->
        <template v-slot:body-cell-action="props">
          <q-btn flat color="purple-7" icon="edit" align="center" @click="editRow(props.row)" />
          <q-btn flat color="purple-7" icon="delete_outline" align="center" @click="deleteRow(props.row)" />
        </template>
      </q-table>
    </div>

    <!-- Dialog pour ajouter un nouvel utilisateur -->
    <q-dialog v-model="showDialog" persistent>
      <q-card style="min-width: 400px;">
        <q-card-section class="bg-purple-1 text-purple-10">
          <div class="text-h6">Ajouter un groupe</div>
        </q-card-section>

        <q-card-section>
          <div class="row q-gutter-md">
            <q-input v-model="newGroup.Name" rounded outlined label="Nom" class="col-11" />
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn unelevated rounded color="purple-7" label="Annuler" v-close-popup />
          <q-btn
          unelevated
          rounded
          color="purple-7"
          label="Ajouter"
          @click="addGroup(newGroup.Name)"
        />

        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const showDialog = ref(false)
const rows = ref([])

// Colonnes du tableau
const columns = [
  { name: 'Nom', label: 'Nom', align: 'left', field: 'Nom' },
  { name: 'nb_user', label: 'Nombre d\'utilisateurs', align: 'left', field: 'nb_user' },
  {
    name: 'action',
    label: 'Actions',
    align: 'center',
    style: 'width: 120px; max-width: 120px;',
    headerStyle: 'width: 120px; max-width: 120px;'
  }
]

async function loadGroups() {
  try {
    const response = await fetch('http://10.0.52.142/success/api.php/show_group')
    if (!response.ok) throw new Error('Erreur HTTP ' + response.status)

    const data = await response.json()

    // 🧩 Adapter les données à tes colonnes
    rows.value = data.map(item => ({
      Nom: item.name,
      nb_user: item.nb_user
    }))
  } catch (err) {
    console.error('Impossible de charger les groupes :', err)
  }
}

// Nouvel utilisateur
const newGroup = ref({
  Nom: '',
  id_s11: 3
})

async function addGroup(name) {
  try {
    const response = await fetch("http://10.0.52.142/success/api.php/add_group", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({
        name: name,
        id_s11: 3
      })
    });

    if (!response.ok) {
      throw new Error("Erreur API " + response.status);
    }

    const data = await response.json();
    console.log("Groupe ajouté :", data);

    if (data.status === "success") {
      showDialog.value = false
      await loadGroups()
    }

    return data;

  } catch (err) {
    console.error("Erreur", err);
  }
}



function editRow(row) {
  console.log('Modifier :', row)
}

function deleteRow(row) {
  rows.value = rows.value.filter(r => r.Id !== row.Id)
}

// Chargement depuis ton API
onMounted(loadGroups)
</script>

<style scoped>
.text-purple-12 {
  color: var(--q-color-purple-12);
}
.bg-rose {
  background-color: #FFF4FF;
}
</style>

<template>
  <q-page class="bg-rose column items-center q-pa-lg" style="background-color: #FFF4FF;">
    <div class="q-pa-md full-width">
      <!-- Titre -->
      <h4 class="text-purple-12 text-weight-bold" style="margin-bottom: 8px;">Utilisateurs</h4>

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
          <div class="text-h6">Ajouter un utilisateur</div>
        </q-card-section>

        <q-card-section>
          <div class="row q-gutter-md">
            <q-input v-model="newUser.Id" rounded outlined label="Id" class="col-11" />
            <q-input v-model="newUser.PWD" rounded outlined label="Mot de passe" class="col-11" />
            <q-input v-model="newUser.Nom" rounded outlined label="Nom" class="col-11" />
            <q-input v-model="newUser.Prenom" rounded outlined label="Prénom" class="col-11" />
            <q-select
              rounded
              standout="bg-grey-1"
              v-model="newUser.status"
              :options="['Admin', 'Teacher', 'User']"
              label="Statut"
              class="col-11"
            />
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn unelevated rounded color="purple-7" label="Annuler" v-close-popup />
          <q-btn unelevated rounded color="purple-7" label="Ajouter" @click="addUser" />
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
  { name: 'Id', label: 'ID', align: 'left', field: 'Id' },
  { name: 'Nom', label: 'Nom', align: 'left', field: 'Nom' },
  { name: 'Prenom', label: 'Prénom', align: 'left', field: 'Prenom' },
  { name: 'PWD', label: 'Mot de passe', align: 'left', field: 'PWD' },
  { name: 'status', label: 'Statut', align: 'left', field: 'status' },
  {
    name: 'action',
    label: 'Actions',
    align: 'center',
    style: 'width: 120px; max-width: 120px;',
    headerStyle: 'width: 120px; max-width: 120px;'
  }
]

// Nouvel utilisateur
const newUser = ref({
  Id: '',
  Nom: '',
  Prenom: '',
  PWD: '',
  status: ''
})

// Fonction pour ajouter un utilisateur localement (client)
function addUser() {
  if (newUser.value.Nom && newUser.value.Prenom) {
    rows.value.push({ ...newUser.value })
    Object.keys(newUser.value).forEach(k => (newUser.value[k] = ''))
    showDialog.value = false
  }
}

function editRow(row) {
  console.log('Modifier :', row)
}

function deleteRow(row) {
  rows.value = rows.value.filter(r => r.Id !== row.Id)
}

// Chargement depuis ton API
onMounted(async () => {
  try {
    const response = await fetch('http://10.0.52.142/success/api.php/show_user')
    if (!response.ok) throw new Error('Erreur HTTP ' + response.status)

    const data = await response.json()

    // 🧩 Adapter les données à tes colonnes
    rows.value = data.map(item => ({
      Id: item.id_s11,
      Nom: item.lastname,
      Prenom: item.firstname,
      PWD: '********', // Masqué pour sécurité
      status: item.lastname.includes('admin') ? 'Admin'
        : item.lastname.includes('teacher') ? 'Teacher'
        : 'User'
    }))
  } catch (err) {
    console.error('Impossible de charger les utilisateurs :', err)
  }
})
</script>

<style scoped>
.text-purple-12 {
  color: var(--q-color-purple-12);
}
.bg-rose {
  background-color: #FFF4FF;
}
</style>

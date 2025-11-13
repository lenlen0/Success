<template>
  <div class="q-pa-md q-gutter-sm" style="background-color: #FFF4FF; min-height: 100vh;">
    <!-- Bouton pour ouvrir la popup -->
    <q-btn round color="purple-7" icon="add" @click="openPopup" />

    <!-- Popup modale pour ajout/modif groupe -->
    <q-dialog v-model="popup">
      <q-card style="width:400px;">
        <q-card-section class="flex column justify-evenly items-center q-pa-none" id="formulaire-ajout-groupe">
          <q-card-section class="bg-purple-1 text-purple-10 q-mb-sm" style="width:100%;">
            <div class="text-h6">{{ isEditMode ? 'Modifier le groupe' : 'Nouveau groupe' }}</div>
          </q-card-section>
          <q-input
            rounded
            outlined
            v-model="editNom"
            label="Nom du groupe"
            class="q-mb-sm"
            style="width: 90%;"
          />
        </q-card-section>
        <q-card-actions align="right">
          <q-btn rounded label="Fermer" color="purple-7" v-close-popup @click="closePopup" />
          <q-btn
            rounded
            v-if="!isEditMode"
            label="Ajouter"
            color="purple-7"
            @click="addGroup"
          />
          <q-btn rounded
            v-if="isEditMode"
            label="Enregistrer"
            color="purple-7"
            @click="saveGroup"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Tableau des groupes -->
    <div class="q-mt-md">
      <q-table
        flat
        bordered
        title="Liste des groupes"
        :rows="groupes"
        :columns="tableColumns"
        :loading="loading"
        row-key="__rowKey"
        color="purple-7"
        card-class=""
        table-class=""
        table-header-class="bg-purple-1"
      >
        <template #body-cell-Nom="props">
          <q-td :props="props">
            {{ props.row.Nom || props.value || '-' }}
          </q-td>
        </template>
        <template #body-cell-nbuser="props">
          <q-td :props="props">
            {{ props.row.nbuser || props.value || 0 }}
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
              :title="'Modifier ce groupe'"
              @click="editGroup(props.row)"
            />
            <q-btn
              icon="delete"
              color="purple-7"
              dense
              flat
              round
              size="sm"
              :title="'Supprimer ce groupe'"
              @click="deleteGroup(props.row)"
            />
          </q-td>
        </template>
      </q-table>
    </div>
  </div>
</template>
<script setup>
import { ref, onMounted } from 'vue'

// État pour la popup et l'édition
const popup = ref(false)
const isEditMode = ref(false)
const editNom = ref('')
const editIndex = ref(-1)
const loading = ref(false)

// Données pour le tableau (chargées depuis l'API)
const groupes = ref([])

// Fonction pour charger les groupes depuis l'API
async function loadGroupes() {
  loading.value = true
  try {
    const response = await fetch('http://10.0.52.212/success/api.php/groupe')
    if (!response.ok) {
      throw new Error(`Erreur HTTP: ${response.status}`)
    }
    const data = await response.json()

    // Transformer les données de l'API pour correspondre au format attendu
    let groupesData = []

    if (Array.isArray(data)) {
      groupesData = data
    } else if (data.groupes && Array.isArray(data.groupes)) {
      groupesData = data.groupes
    } else if (data.data && Array.isArray(data.data)) {
      groupesData = data.data
    } else {
      groupes.value = []
      loading.value = false
      return
    }

    // Mapper les données selon la structure de la base de données
    groupes.value = groupesData.map((item, index) => {
      // Récupérer le nom du groupe
      const nomGroupe = item.Nom || item.name || item.nom || item.libelle || ''

      // Récupérer le nombre d'utilisateurs
      const nbUsers = item.nbuser || item.nbUser || item.nb_users || item.nombreUtilisateurs || 0

      return {
        __rowKey: item.idGroupe || item.id || item.ID || index + 1,
        Nom: nomGroupe,
        nbuser: nbUsers
      }
    })
  } catch {
    // En cas d'erreur, on garde un tableau vide ou on affiche un message
    groupes.value = []
  } finally {
    loading.value = false
  }
}

// Fonction pour ouvrir la popup (mode ajout)
function openPopup() {
  isEditMode.value = false
  editNom.value = ''
  editIndex.value = -1
  popup.value = true
}

// Fonction pour fermer la popup
function closePopup() {
  popup.value = false
  isEditMode.value = false
  editNom.value = ''
  editIndex.value = -1
}

// Fonction pour ajouter un groupe
function addGroup() {
  if (editNom.value.trim()) {
    const newGroup = {
      __rowKey: groupes.value.length + 1,
      Nom: editNom.value.trim(),
      nbuser: 0
    }
    groupes.value.push(newGroup)
    closePopup()
  }
}

// Fonction pour modifier un groupe
function editGroup(group) {
  isEditMode.value = true
  editNom.value = group.Nom || ''
  editIndex.value = groupes.value.findIndex(g => g.__rowKey === group.__rowKey)
  popup.value = true
}

// Fonction pour enregistrer les modifications
function saveGroup() {
  if (editNom.value.trim() && editIndex.value >= 0) {
    groupes.value[editIndex.value].Nom = editNom.value.trim()
    closePopup()
  }
}

// Fonction pour supprimer un groupe
function deleteGroup(group) {
  const index = groupes.value.findIndex(g => g.__rowKey === group.__rowKey)
  if (index >= 0) {
    groupes.value.splice(index, 1)
  }
}

// Charger les groupes au montage du composant
onMounted(() => {
  loadGroupes()
})

// Colonnes du q-table
const tableColumns = [
  {
    name: 'Nom',
    required: true,
    label: 'Nom du groupe',
    align: 'left',
    field: row => row.Nom,
    sortable: false
  },
  {
    name: 'nbuser',
    required: true,
    label: 'Nombre d\'utilisateurs',
    align: 'left',
    field: row => row.nbuser,
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

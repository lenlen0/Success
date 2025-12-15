<template>
  <q-page class="bg-rose column items-center q-pa-lg" style="background-color: #FFF4FF;">
    <div class="q-pa-md full-width">
      <!-- Titre -->
      <h4 class="text-purple-12 text-weight-bold" style="margin-bottom: 8px;">Groupes</h4>

      <!-- Bouton pour ouvrir le dialog -->
      <q-btn round color="purple-7" icon="add" style="margin: 8px" @click="openAddDialog" />
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

    <!-- Dialog pour ajouter un groupe -->
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

    <!-- Dialog pour modifier un groupe -->
    <q-dialog v-model="showEditDialog" persistent>
      <q-card style="min-width: 400px;">
        <q-card-section class="bg-purple-1 text-purple-10">
          <div class="text-h6">Modifier un groupe</div>
        </q-card-section>

        <q-card-section>
          <div class="row q-gutter-md">
              <div class="row items-center q-gutter-md q-mt-md">
                <q-btn round color="purple-7" icon="assignment" aria-label="Questions" @click="goToMatchUser"/>
                <span class="text-body2">Gérer les utilisateurs</span>
              </div>
            <q-input v-model="editingGroup.Name" rounded outlined label="Nom" class="col-11"/>
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn unelevated rounded color="purple-7" label="Annuler" v-close-popup />
          <q-btn
            unelevated
            rounded
            color="purple-7"
            label="Modifier"
            @click="editGroup(editingGroup.documentId, editingGroup.Name)"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { verifyR } from '../composables/verification'
import { Cookies } from 'quasar';

const { verifyRole } = verifyR()

const showDialog = ref(false)
const showEditDialog = ref(false)
const rows = ref([])
const editingGroup = ref({
  Name: '',
  nb_user: 0,
  documentId: null
})

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
  const token_user = Cookies.get('token_user')

  const response = await fetch('http://10.0.52.187:1337/api/groups', {
    headers: {
      "Content-Type": "application/json",
      "Authorization": `Bearer ${token_user}`
    }
  })

  if (!response.ok) throw new Error('Erreur HTTP ' + response.status)
  const { data: groups } = await response.json()

  const responseBelong = await fetch(
    'http://10.0.52.187:1337/api/belonggroups?populate=*',
    {
      headers: {
        "Content-Type": "application/json",
        "Authorization": `Bearer ${token_user}`
      }
    }
  )

  if (!responseBelong.ok) throw new Error('Erreur HTTP ' + responseBelong.status)
  const { data: belongList } = await responseBelong.json()

  rows.value = groups.map(group => {
    const groupId = group.id

    const countUsers = belongList.filter(bg =>
      bg.id_group?.some(g => g.id === groupId)
    ).length

    return {
      Id: group.id,
      Nom: group.name,
      documentId: group.documentId,
      nb_user: countUsers
    }
  })
}


// Nouveau groupe
const newGroup = ref({
  Name: '',
  id_s11: 3
})

function openAddDialog() {
  newGroup.value.Name = ''
  showDialog.value = true
}

async function addGroup(name) {
  const token_user = Cookies.get('token_user')
  const response = await fetch("http://10.0.52.187:1337/api/groups", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      'Authorization': `Bearer ${token_user}`
    },
    body: JSON.stringify({
      data: {
        name: name
      },
    })
  });

  if (!response.ok) {
    throw new Error("Erreur API " + response.status);
  }
  const data = await response.json();

  await loadGroups();
  showDialog.value = false

  return data;
}

async function editGroup(documentId, name) {
  const token_user = Cookies.get('token_user')
  const response = await fetch(`http://10.0.52.187:1337/api/groups/${documentId}`, {
    method: "PUT",
    headers: {
      "Content-Type": "application/json",
      'Authorization': `Bearer ${token_user}`
    },
    body: JSON.stringify({
      data: {
        name: name
      },
    })
  });

  if (!response.ok) {
    throw new Error("Erreur API " + response.status);
  }

  showEditDialog.value = false
  await loadGroups()
}



function editRow(row) {
  editingGroup.value = {
    documentId: row.documentId,
    Name: row.Nom,
    nb_user: row.nb_user
  }
  showEditDialog.value = true
}

function goToMatchUser() {
  if (editingGroup.value && editingGroup.value.documentId) {
    window.location.href = '/#/matchUser?documentId=' + editingGroup.value.documentId;
  } else {
    console.warn("Aucun ID de groupe trouvé.");
  }
}

async function deleteRow(row) {
  const token_user = Cookies.get('token_user')
  const documentId = row.documentId;

  const response = await fetch(`http://10.0.52.187:1337/api/groups/${documentId}`, {
    method: 'DELETE',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token_user}`
    }
  });

   if (!response.ok) {
    throw new Error(`Erreur DELETE ${response.status}`)
  }

  await loadGroups();
}

// Chargement depuis ton API
onMounted(async () => {
  verifyRole("admin", "/AccueilU")
  await loadGroups();
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
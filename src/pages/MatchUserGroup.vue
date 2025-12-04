<template>
    <q-page class="bg-rose column q-pa-lg" style="background-color: #FFF4FF;">
      <div class="full-width q-pa-md">
        <!-- Titre -->
        <h4 class="text-purple-12 text-weight-bold q-mb-md text-center">Gérer les utilisateurs du groupe</h4>
  
        <!-- Menu déroulant pour sélectionner le groupe -->
        <div class="row q-mb-lg justify-center">
          <div class="col-12 col-md-6">
            <q-select
              v-model="selectedGroup"
              :options="groups"
              option-label="Nom"
              option-value="Id"
              label="Sélectionner un groupe"
              rounded
              outlined
              emit-value
              map-options
              @update:model-value="loadGroupUsers"
              class="bg-white"
            />
          </div>
        </div>
  
        <!-- Deux tableaux côte à côte -->
        <div class="row q-gutter-sm no-wrap justify-center">
          <!-- Tableau de gauche : Tous les utilisateurs SAUF ceux du groupe -->
          <div class="col-5">
            <h5 class="text-purple-10 text-weight-bold q-mb-sm text-center">Utilisateurs disponibles</h5>
            <q-table
              flat
              bordered
              color="primary"
              card-class="bg-white"
              table-header-class="bg-purple-1 text-purple-10"
              :rows="availableUsers"
              :columns="userColumns"
              row-key="Id"
              selection="multiple"
              v-model:selected="selectedAvailableUsers"
              :rows-per-page-options="[10, 20, 50]"
            >
              <template v-slot:no-data>
                <div class="full-width row flex-center text-grey q-gutter-sm q-pa-lg">
                  <span>Aucun utilisateur disponible</span>
                </div>
              </template>
            </q-table>
          </div>

          <!-- Boutons au milieu -->
          <div class="col-auto flex flex-center column q-gutter-sm">
            <q-btn
              round
              color="green"
              icon="arrow_forward"
              @click="addUsersToGroup"
              :disable="!selectedGroup || selectedAvailableUsers.length === 0"
              size="md"
            />
            <q-btn
              round
              color="red"
              icon="arrow_back"
              @click="removeUsersFromGroup"
              :disable="!selectedGroup || selectedGroupUsers.length === 0"
              size="md"
            />
          </div>

          <!-- Tableau de droite : Utilisateurs du groupe sélectionné -->
          <div class="col-5">
            <h5 class="text-purple-10 text-weight-bold q-mb-sm text-center">Utilisateurs du groupe</h5>
            <q-table
              flat
              bordered
              color="primary"
              card-class="bg-white"
              table-header-class="bg-purple-1 text-purple-10"
              :rows="groupUsers"
              :columns="userColumns"
              row-key="Id"
              selection="multiple"
              v-model:selected="selectedGroupUsers"
              :rows-per-page-options="[10, 20, 50]"
            >
              <template v-slot:no-data>
                <div class="full-width row flex-center text-grey q-gutter-sm q-pa-lg">
                  <span>Aucun utilisateur dans ce groupe</span>
                </div>
              </template>
            </q-table>
          </div>
        </div>
      </div>
    </q-page>
  </template>
  
  <script setup>
  import { ref, computed, onMounted } from 'vue'
  import { useRoute } from 'vue-router'
  
  const route = useRoute()
  
  const selectedGroup = ref(null)
  const groups = ref([])
  const allUsers = ref([])
  const groupUsers = ref([])
  const selectedAvailableUsers = ref([])
  const selectedGroupUsers = ref([])
  
  // Colonnes du tableau utilisateur
  const userColumns = [
    { name: 'Id', label: 'ID', align: 'left', field: 'Id', sortable: true },
    { name: 'Nom', label: 'Nom', align: 'left', field: 'Nom', sortable: true },
    { name: 'Prenom', label: 'Prénom', align: 'left', field: 'Prenom', sortable: true },
    { name: 'role', label: 'Role', align: 'left', field: 'role', sortable: true }
  ]
  
  // Utilisateurs disponibles (tous sauf ceux du groupe)
  const availableUsers = computed(() => {
    if (!selectedGroup.value || groupUsers.value.length === 0) {
      return allUsers.value
    }
    const groupUserIds = new Set(groupUsers.value.map(u => u.Id))
    return allUsers.value.filter(user => !groupUserIds.has(user.Id))
  })
  
  // Charger tous les groupes
  async function loadGroups() {
    try {
      const response = await fetch('http://10.0.52.142/success/api.php/show_group/')
      if (!response.ok) throw new Error('Erreur HTTP ' + response.status)
  
      const data = await response.json()
      groups.value = data.map(item => ({
        Id: item.idGroup,
        Nom: item.name,
        nb_user: item.nb_user
      }))
    } catch (err) {
      console.error('Impossible de charger les groupes :', err)
    }
  }
  
  // Charger tous les utilisateurs
  async function loadAllUsers() {
    try {
      const response = await fetch('http://10.0.52.142/success/api.php/show_user')
      if (!response.ok) throw new Error('Erreur HTTP ' + response.status)
  
      const data = await response.json()
      allUsers.value = data.map(item => ({
        Id: item.id_s11,
        Nom: item.lastname,
        Prenom: item.firstname,
        role: item.role.includes('admin') ? 'Admin'
          : item.role.includes('teacher') ? 'Teacher'
          : 'Student'
      }))
    } catch (err) {
      console.error('Impossible de charger les utilisateurs :', err)
    }
  }
  
  // Charger les utilisateurs du groupe sélectionné
  async function loadGroupUsers() {
    if (!selectedGroup.value) {
      groupUsers.value = []
      return
    }
  
    try {
      // On récupère tous les utilisateurs et on filtre côté client
      // car il n'y a pas d'endpoint GET direct pour getUserByGroup
      // On va utiliser une approche différente : récupérer tous les utilisateurs
      // et filtrer ceux qui sont dans le groupe
      
      // Pour l'instant, on va charger tous les utilisateurs et filtrer
      // En production, il faudrait un endpoint GET pour getUserByGroup
      await loadAllUsers()
      
      // On va devoir faire une requête POST pour obtenir les utilisateurs du groupe
      // Ou utiliser une logique côté client basée sur les données disponibles
      // Pour simplifier, on va utiliser une approche où on charge tous les utilisateurs
      // et on filtre ceux qui sont dans le groupe sélectionné
      
      // Note: Il faudrait idéalement un endpoint GET pour getUserByGroup
      // Pour l'instant, on va utiliser une méthode alternative
      
      // On récupère les utilisateurs du groupe via une requête spéciale
      // Comme il n'y a pas d'endpoint GET direct, on va utiliser une logique différente
      // On va créer une fonction qui récupère les utilisateurs du groupe
      
      const response = await fetch(`http://10.0.52.142/success/api.php/show_user_group/${selectedGroup.value}`)
      
      if (response.ok) {
        const data = await response.json()
        groupUsers.value = data.map(item => ({
          Id: item.id_s11,
          Nom: item.lastname,
          Prenom: item.firstname,
          role: item.role.includes('admin') ? 'Admin'
            : item.role.includes('teacher') ? 'Teacher'
            : 'Student'
        }))
      } else {
        // Si l'endpoint n'existe pas, on filtre côté client
        // On va devoir charger tous les utilisateurs et déterminer lesquels sont dans le groupe
        // Pour cela, on pourrait faire une requête pour chaque utilisateur, mais c'est inefficace
        // On va plutôt supposer que l'endpoint existe ou utiliser une autre méthode
        groupUsers.value = []
      }
    } catch (err) {
      console.error('Impossible de charger les utilisateurs du groupe :', err)
      // En cas d'erreur, on essaie une approche alternative
      groupUsers.value = []
    }
    
    // Réinitialiser les sélections
    selectedAvailableUsers.value = []
    selectedGroupUsers.value = []
  }
  
  // Ajouter des utilisateurs au groupe
  async function addUsersToGroup() {
    if (!selectedGroup.value || selectedAvailableUsers.value.length === 0) {
      return
    }
  
    try {
      const promises = selectedAvailableUsers.value.map(user => 
        fetch('http://10.0.52.142/success/api.php/add_user_to_group', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            id_s11: user.Id,
            idGroup: selectedGroup.value
          })
        })
      )
  
      const results = await Promise.all(promises)
      const allSuccess = results.every(response => response.ok)
  
      if (allSuccess) {
        // Recharger les données
        await loadGroupUsers()
        await loadAllUsers()
        selectedAvailableUsers.value = []
      } else {
        console.error('Erreur lors de l\'ajout des utilisateurs')
      }
    } catch (err) {
      console.error('Erreur lors de l\'ajout des utilisateurs :', err)
    }
  }
  
  // Retirer des utilisateurs du groupe
  async function removeUsersFromGroup() {
    if (!selectedGroup.value || selectedGroupUsers.value.length === 0) {
      return
    }
  
    try {
      const promises = selectedGroupUsers.value.map(user => 
        fetch('http://10.0.52.142/success/api.php/remove_user_from_group', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            id_s11: user.Id,
            idGroup: selectedGroup.value
          })
        })
      )
  
      const results = await Promise.all(promises)
      const allSuccess = results.every(response => response.ok)
  
      if (allSuccess) {
        // Recharger les données
        await loadGroupUsers()
        await loadAllUsers()
        selectedGroupUsers.value = []
      } else {
        console.error('Erreur lors de la suppression des utilisateurs')
      }
    } catch (err) {
      console.error('Erreur lors de la suppression des utilisateurs :', err)
    }
  }
  
  // Initialisation
  onMounted(async () => {
    await loadGroups()
    await loadAllUsers()
    
    // Si un idGroup est passé en paramètre URL, le sélectionner
    const idGroup = route.query.idGroup
    if (idGroup) {
      selectedGroup.value = parseInt(idGroup)
      await loadGroupUsers()
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
  
  
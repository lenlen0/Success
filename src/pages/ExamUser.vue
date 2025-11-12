<template>
  <q-page class="bg-rose column items-center q-pa-lg" style="background-color: #FFF4FF;">
    <div class="q-pa-md full-width">
      <!-- Titre -->
      <h4
        class="text-purple-12 text-weight-bold"
        style="margin-bottom: 8px;"
      >Mes Evaluations</h4>

      <!-- Bouton pour ouvrir le dialog -->
      <q-btn round color="purple-7" icon="add" style="margin: 8px" @click="showDialog = true" />
      <br/><br/>

      <!-- Tableau Quasar -->
      <q-table
        flat
        bordered
        color="primary"
        card-class="bg-white"
        table-header-class="bg-purple-1 text-purple-10"
        :rows="rows"
        :columns="columns"
        row-key="nom"
      >
        <!-- Slot pour customiser la colonne "action" -->
        <template v-slot:body-cell-action="props">
          <q-btn flat color="purple-7" icon="visibility" align="center" @click="editRow(props.row)" />
          <q-btn flat color="purple-7" icon="replay" align="center" @click="editRow(props.row)" />
        </template>
      </q-table>
    </div>

    
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'

// -----------------------------------------------------------
// Variables réactives
// -----------------------------------------------------------
const showDialog = ref(false)
const rows = ref([])


// -----------------------------------------------------------
// Colonnes du tableau
// -----------------------------------------------------------
const columns = [
  { name: 'nom', label: 'Nom', align: 'left', field: 'nom' },
  { name: 'date', label: 'Date', align: 'left', field: 'date' },
  { name: 'qcm', label: 'QCM', align: 'left', field: 'qcm' },
  { name: 'groupe', label: 'Groupe', align: 'left', field: 'groupe' },
  { name: 'status', label: 'Status', align: 'left', field: 'status' },
  { name: 'reussite', label: '% Réussite', align: 'left', field: 'reussite' },
  {
    name: 'action',
    label: 'Action',
    align: 'center',
    style: 'width: 120px; max-width: 120px;',
    headerStyle: 'width: 120px; max-width: 120px;'
  }
]

// -----------------------------------------------------------
// Simulation : utilisateur connecté
// -----------------------------------------------------------
const currentUserId = '1' // 🔥 ici tu choisis quel utilisateur "virtuel" tu veux simuler

// -----------------------------------------------------------
// Récupération des données depuis ton API distante
// -----------------------------------------------------------
onMounted(async () => {
  try {
    const response = await fetch('http://10.0.52.187/success/api.php/exam')
    if (!response.ok) throw new Error('Erreur HTTP ' + response.status)

    const data = await response.json()

    // 🧩 Filtrage pour ne garder que les examens de cet utilisateur
    const filtered = data.filter(item => item.id_s11 === currentUserId)

    // 🗂️ Mapping pour afficher correctement dans ton tableau Quasar
    rows.value = filtered.map(item => ({
      nom: item.nom,
      date: item.Date,
      qcm: item.QCM,
      groupe: item.Groupe,
      status: item.Status,
      reussite: item['%Reussite']
    }))

    console.log('Évaluations visibles pour id_s11 =', currentUserId, rows.value)
  } catch (err) {
    console.error('Impossible de charger les données depuis la VM :', err)
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

<template>
  <q-page class="bg-rose column items-center q-pa-lg" style="background-color: #FFF4FF;">
    <div class="q-pa-md full-width">
      <!-- Titre -->
      <h4
      class="text-purple-12 text-weight-bold"
      style="margin-bottom: 8px;"
      >Éval</h4>
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
          <!-- Bouton éditer la ligne -->
          <q-btn flat color="purple-7" icon="edit" aligne="center" @click="editRow(props.row)" />
          <!-- Bouton supprimer la ligne -->
          <q-btn flat color="purple-7" icon="delete_outline" aligne="center" @click="editRow(props.row)" />
        </template>
      </q-table>
    </div>

    <!-- Dialog pour ajouter une nouvelle évaluation -->
    <q-dialog v-model="showDialog" persistent>
      <!-- Carte du dialog avec largeur minimale -->
      <q-card style="min-width: 400px;">
        <!-- Section titre du dialog -->
        <q-card-section class="bg-purple-1 text-purple-10">
          <div class="text-h6">Nouvelle évaluation</div>
        </q-card-section>

        <!-- Section pour les inputs du formulaire -->
        <q-card-section>
          <div class="row q-gutter-md">
          <!-- Input pour le nom -->
          <q-input
              rounded
              outlined
              :label="'Nom'"
              class="col-11"
            />
            
          <!-- Input pour la date -->
          <q-input
              rounded
              outlined
              :label="'Date'"
              class="col-11"
            />
          <!-- Input pour le QCM -->
          <q-input
              rounded
              outlined
              :label="'QCM'"
              class="col-11"
            />
          <!-- Select pour choisir le groupe -->
          <q-select
            rounded
            standout="bg-grey-1"
            v-model="newEval.groupe"
            :options="['A', 'B', 'C']"
            label="Groupe"
            class="col-11"
          />
        </div>
        </q-card-section>

        <!-- Actions du dialog (boutons) -->
        <q-card-actions align="right">
          <!-- Bouton annuler (ferme le dialog) -->
          <q-btn unelevated rounded color="purple-7" label="Annuler" v-close-popup />
          <!-- Bouton ajouter (ajoute l'évaluation) -->
          <q-btn unelevated rounded color="purple-7" label="Ajouter" @click="addEval" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
  // Import de ref pour les variables réactives
  import { ref } from 'vue'

  // Booléen pour afficher/cacher le dialog
  const showDialog = ref(false)

  // Définition des colonnes du tableau
  const columns = [
    { name: 'nom', label: 'Nom', align: 'left', field: 'nom' },
    { name: 'date', label: 'Date', align: 'left', field: 'date' },
    { name: 'qcm', label: 'QCM', align: 'left', field: 'qcm' },
    { name: 'groupe', label: 'Groupe', align: 'left', field: 'groupe' },
    { name: 'reussite', label: '% Réussite', align: 'left', field: 'reussite' },
    // Colonne pour les actions (éditer/supprimer)
    {
    name: 'action',
    label: 'Action',
    align: 'center',
    style: 'width: 120px; max-width: 120px;',
    headerStyle: 'width: 120px; max-width: 120px;'
    }
]

  // Données initiales du tableau
  const rows = ref([
    { nom: 'Examen 1', date: '2025-10-10', qcm: 'QCM1', groupe: 'A', reussite: '85%' },
    { nom: 'Examen 2', date: '2025-10-12', qcm: 'QCM2', groupe: 'B', reussite: '76%' }
  ])

  // Objet pour stocker la nouvelle évaluation
  const newEval = ref({
    nom: '',
    date: '',
    qcm: '',
    groupe: ''
  })

  // Fonction pour ajouter une nouvelle évaluation
  function addEval() {
    // Vérifie que le nom n'est pas vide
    if (newEval.value.nom) {
      // Ajoute la nouvelle ligne au tableau
      rows.value.push({ ...newEval.value, reussite: `${newEval.value.reussite}%` })
      // Réinitialise le formulaire
      Object.keys(newEval.value).forEach(k => newEval.value[k] = '')
      // Ferme le dialog
      showDialog.value = false
    }
  }

  // Fonction pour éditer une ligne (console log temporaire)
  function editRow(row) {
    console.log('Modifier :', row)
  }
</script>

<style scoped>
  .text-purple-12 {
    color: var(--q-color-purple-12);
  }

  .bg-rose {
    background-color: #FFF4FF;
  }
</style>

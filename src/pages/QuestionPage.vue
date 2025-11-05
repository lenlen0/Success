<template>
  <div class="q-pa-md q-gutter-sm">
    <!-- Bouton pour ouvrir la popup -->
    <q-btn round color="purple-7" icon="add" @click="popup = true" />

    <!-- Popup modale pour ajout/modif question/réponses -->
    <q-dialog v-model="popup">
      <q-card style="width:400px;">
        <q-card-section class="flex column justify-evenly items-center q-pa-none" id="formulaire-ajout-question">
          <q-card-section class="bg-purple-1 text-purple-10 q-mb-sm" style="width:100%;">
            <div class="text-h6">Nouvelle évaluation</div>
          </q-card-section>
          <q-input rounded outlined :model-value="editQuestion" label="Question" class="q-mb-sm"/>

          <!-- Réponses avec bouton pour enlever si > 2 -->
          <div
            v-for="(reponse, idx) in editReponses"
            :key="'reponse'+(idx+1)"
            class="q-mb-sm row items-center"
          >
            <q-input
              rounded
              outlined
              :model-value="editReponses[idx]"
              :label="'Réponse ' + (idx + 1)"
              class="col"
            />
            <q-btn
              v-if="editReponses.length > 2"
              round
              dense
              flat
              color="red"
              icon="remove"
              class="q-ml-xs"
              :disable="editReponses.length <= 2"
              :title="'Enlever cette réponse'"
            />
          </div>

          <!-- Bouton pour ajouter une réponse, max 5 -->
          <q-btn
            v-if="editReponses.length < 5"
            round
            color="purple-7"
            icon="add"
            class="q-mb-sm"
            :disable="editReponses.length>=5"
            :title="editReponses.length>=5 ? 'Nombre maximum de réponses atteint' : 'Ajouter une réponse'"
          />
        </q-card-section>
        <q-card-actions align="right">
          <q-btn rounded label="Fermer" color="purple-7" v-close-popup />
          <q-btn
            rounded
            v-if="!isEditMode"
            label="Ajouter"
            color="purple-7"
          />
          <q-btn
            v-if="isEditMode"
            label="Enregistrer"
            color="purple-7"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Tableau des questions ajoutées -->
    <div class="q-mt-md">
      <q-table
        flat
        bordered
        title="Questions ajoutées"
        :rows="questions"
        :columns="tableColumns"
        row-key="__rowKey"
        color="purple-7"
        card-class=""
        table-class=""
        table-header-class="bg-purple-1"
      >
        <template #body-cell-reponses="props">
          <q-td :props="props">
            <ol style="margin: 0; padding-left: 18px;">
              <li v-for="(r, i) in props.row.reponses" :key="'ans'+i">{{ r }}</li>
            </ol>
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
              :title="'Modifier cette question'"
            />
            <q-btn
              icon="delete"
              color="purple-7"
              dense
              flat
              round
              size="sm"
              :title="'Supprimer cette question'"
            />
          </q-td>
        </template>
      </q-table>
    </div>
  </div>
</template>
<script setup>
import { ref } from 'vue'

// État statique pour l'affichage visuel
const popup = ref(false)
const isEditMode = ref(false)
const editQuestion = ref('Exemple de question')
const editReponses = ref(['Réponse 1', 'Réponse 2', 'Réponse 3'])

// Données statiques pour le tableau
const questions = ref([
  {
    __rowKey: 1,
    question: 'Question exemple 1',
    reponses: ['Réponse A', 'Réponse B', 'Réponse C']
  },
  {
    __rowKey: 2,
    question: 'Question exemple 2',
    reponses: ['Réponse 1', 'Réponse 2']
  }
])

// Colonnes du q-table
const tableColumns = [
  {
    name: 'Num',
    required: true,
    label: 'Num',
    align: 'left',
    field: (row, idx) => idx + 1,
    sortable: false
  },
  {
    name: 'Nom',
    required: true,
    label: 'Nom',
    align: 'left',
    field: row => row.question,
    sortable: false
  },
  {
    name: 'reponses',
    required: true,
    label: 'Réponses',
    align: 'left',
    field: row => row.reponses,
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

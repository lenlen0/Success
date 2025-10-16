<template>
  <q-page class="flex flex-center">
    <div class="q-pa-md">
      <h1>Questionnaires</h1>
      <q-btn round color="purple-7" icon="add" style="margin: 8px" />
      <q-btn round color="purple-7" icon="smart_toy" />
      <br/><br/>
      <q-table
        color="purple-12"
        title="QUIZZ"
        :rows="rows"
        :columns="columns"
        row-key="NOM"
      >
        <template v-slot:body-cell-Action="props">
          <q-td :props="props" class="flex items-center justify-center">
            <div class="row items-center q-gutter-sm">
              <q-btn round color="purple-7" dense icon="delete" aria-label="Supprimer"/>
              <q-btn round color="purple-7" dense icon="edit" aria-label="Éditer"/>
            </div>
          </q-td>
        </template>
      </q-table>
    </div>
  </q-page>
</template>

<script>
const columns = [
  { name: 'NOM', align: 'center', label: 'NOM', field: 'NOM', sortable: true },
  { name: 'Nombre_De_Questions', label: 'Nombre_De_Questions', field: 'Nombre_De_Questions', align: 'center'},
  { name: 'temps', label: 'temps', field: 'temps' , align: 'center' },
  { name: 'Action', label: 'Action', field: 'Action' , align: 'center' }
]

const rows = [
  {
    NOM: 'QCM 1',
    Nombre_De_Questions: 16,
    temps: 23
  },
  {
    NOM: 'QCM 2',
    Nombre_De_Questions: 15,
    temps: 15
  },
  {
    NOM: 'QCM 3',
    Nombre_De_Questions: 20,
    temps: 30
  },
  {
    NOM: 'QCM 4',
    Nombre_De_Questions: 16,
    temps: 17
  },
  {
    NOM: 'QCM 5',
    Nombre_De_Questions: 54,
    temps: 60
  },
  {
    NOM: 'QCM 6',
    Nombre_De_Questions: 15,
    temps: 20
  },
  {
    NOM: 'QCM 7',
    Nombre_De_Questions: 30,
    temps: 45
  },
  {
    NOM: 'QCM 8',
    Nombre_De_Questions: 26,
    temps: 45
  }
]

export default {
  setup () {
    return {
      columns,
      rows
    }
  }
}
</script>

<style>
body{
background-color: #FFF4FF;
}
</style>

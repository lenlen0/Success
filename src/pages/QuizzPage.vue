<template>
  <q-page class="flex flex-center">
    <div class="q-pa-md">
      <h1>QUIZZ PAGE</h1>
      <q-btn round color="purple-7" icon="add" />
      <q-btn round color="purple-7" icon="smart_toy" />
      <br/><br/>
      <q-table
        color="purple-12"
        title="QUIZZ"
        :rows="rows"
        :columns="columns"
        row-key="NOM"
      >
          <template v-slot:body-cell-Action="">
          <q-btn round color="purple-7" icon="delete" />
          <q-btn round color="purple-7" icon="edit" />
        </template>
        </q-table>
    </div>
  </q-page>
</template>

<script>
const columns = [
  { name: 'NOM', align: 'center', label: 'NOM', field: 'NOM', sortable: true },
  { name: 'Nombre_De_Questions', label: 'Nombre_De_Questions', field: 'Nombre_De_Questions', sortable: true },
  { name: 'temps', label: 'temps', field: 'temps' },
  { name: 'Action', label: 'Action', field: 'Action' }
]

const rows = [
  {
    NOM: 'QCM 1',
    Nombre_De_Questions: 16,
    temps: 23,
    Action: ''
  },
  {
    NOM: 'QCM 2',
    Nombre_De_Questions: 15,
    temps: 15,
    Action: ''
  },
  {
    NOM: 'QCM 3',
    Nombre_De_Questions: 20,
    temps: 30,
    Action: ''
  },
  {
    NOM: 'QCM 4',
    Nombre_De_Questions: 16,
    temps: 17,
    Action: ''
  },
  {
    NOM: 'QCM 5',
    Nombre_De_Questions: 54,
    temps: 60,
    Action: ''
  },
  {
    NOM: 'QCM 6',
    Nombre_De_Questions: 15,
    temps: 20,
    Action: ''
  },
  {
    NOM: 'QCM 7',
    Nombre_De_Questions: 30,
    temps: 45,
    Action: ''
  },
  {
    NOM: 'QCM 8',
    Nombre_De_Questions: 26,
    temps: 45,
    Action: ''
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

<template>
  <q-page class="bg-rose column items-center q-pa-lg" style="background-color: #FFF4FF;">
    <div class="q-pa-md full-width">
      <h4 class="text-purple-12 text-weight-bold" style="margin-bottom: 8px;">Évals</h4>

      <q-btn round color="purple-7" icon="add" style="margin: 8px" @click="openDialogAndGenerateCode" />
      <br/><br/>

      <q-table
        flat
        bordered
        color="primary"
        card-class="bg-white"
        table-header-class="bg-purple-1 text-purple-10"
        :rows="rows"
        :columns="columns"
        row-key="Code"
      >
        <template v-slot:body-cell-action="props">
          <q-btn flat color="purple-7" icon="edit" @click="openEditDialog(props.row)" />
          <q-btn flat color="purple-7" icon="delete_outline" @click="deleteRow(props.row)" />
        </template>
        <template v-slot:body-cell-reussite="props">
          <q-td :props="props">
            {{ props.row.reussite || '0%' }}
          </q-td>
        </template>
      </q-table>
    </div>

    <q-dialog v-model="showDialog" persistent>
      <q-card style="min-width: 450px;">
        <q-card-section class="bg-purple-1 text-purple-10">
          <div class="text-h6">Nouvelle Evaluation</div>
        </q-card-section>

        <q-card-section>
          <div class="row q-gutter-md">
            <q-input rounded outlined v-model="newEval.nom" label="Nom" class="col-11" />
            <q-input rounded outlined v-model.number="newEval.time" label="Time (min)" type="number" class="col-11" />

            <q-select
              rounded outlined
              v-model="newEval.qcm"
              :options="quizzOptions"
              label="QCM"
              class="col-11"
              emit-value
              map-options
            />

            <q-input
              rounded
              outlined
              v-model="newEval.Code"
              label="Code"
              class="col-11"
            >
              <template v-slot:append>
                <q-btn
                  icon="refresh"
                  round
                  flat
                  dense
                  @click="generateAndSetCode"
                  color="purple-7"
                />
              </template>
            </q-input>

            <q-select
              rounded outlined
              v-model="newEval.groupe"
              :options="groupOptions"
              label="Groupe"
              class="col-11"
              emit-value
              map-options
            />

            <q-select
              rounded outlined
              v-model="newEval.status"
              :options="['Ouvert', 'Ferme', 'Entrainement']"
              label="Status"
              class="col-11"
            />

            <q-card-actions align="center" class="q-gutter-md">
              <q-checkbox v-model="newEval.Barem" label="Barême" color="purple-7" keep-color />
              <q-checkbox v-model="newEval.Malus" label="Malus" color="purple-7" keep-color />
            </q-card-actions>
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn unelevated rounded color="purple-7" label="Annuler" v-close-popup @click="resetForm" />
          <q-btn
            unelevated
            rounded
            color="purple-7"
            label="Ajouter"
            @click="addEval"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <q-dialog v-model="showEditDialog" persistent>
      <q-card style="min-width: 450px;">
        <q-card-section class="bg-purple-1 text-purple-10">
          <div class="text-h6">Modifier Evaluation</div>
        </q-card-section>

        <q-card-section>
          <div class="row q-gutter-md">
            <q-input rounded outlined v-model="editEval.nom" label="Nom" class="col-11" />
            <q-input rounded outlined v-model.number="editEval.time" label="Time (min)" type="number" class="col-11" />

            <q-select
              rounded outlined
              v-model="editEval.qcm"
              :options="quizzOptions"
              label="QCM"
              class="col-11"
              emit-value
              map-options
            />

            <q-select
              rounded outlined
              v-model="editEval.groupe"
              :options="groupOptions"
              label="Groupe"
              class="col-11"
              emit-value
              map-options
            />

            <q-select
              rounded outlined
              v-model="editEval.status"
              :options="['Ouvert', 'Ferme', 'Entrainement']"
              label="Status"
              class="col-11"
            />

            <q-card-actions align="center" class="q-gutter-md">
              <q-checkbox v-model="editEval.Barem" label="Barême" color="purple-7" keep-color />
              <q-checkbox v-model="editEval.Malus" label="Malus" color="purple-7" keep-color />
            </q-card-actions>
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn unelevated rounded color="purple-7" label="Annuler" v-close-popup @click="resetForm" />
          <q-btn
            unelevated
            rounded
            color="purple-7"
            label="Modifier"
            @click="editEvalFunc"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { verifyR } from '../composables/verification'
import { Cookies } from 'quasar'

const { verifyRole } = verifyR()

const showDialog = ref(false)
const showEditDialog = ref(false)
const rows = ref([])
const editedEval = ref(null)

const newEval = ref({
  nom: '',
  time: 60,
  qcm: null,
  groupe: null,
  Code: '',
  status: '',
  Barem: false,
  Malus: false,
  idExam: null
})

const editEval = ref({
  nom: '',
  time: '',
  qcm: null,
  groupe: null,
  status: '',
  Barem: false,
  Malus: false,
  idExam: null,
  documentId: null
})

async function editEvalFunc() {
  const token_user = Cookies.get('token_user')
  const documentId = editEval.value.documentId;

  const response = await fetch(`http://10.0.52.187:1337/api/exams/${documentId}`, {
    method: "PUT",
    headers: {
      "Content-Type": "application/json",
      'Authorization': `Bearer ${token_user}`
    },
    body: JSON.stringify({
      data: {
        name: editEval.value.nom,
        role: editEval.value.status,
        scale: editEval.value.Barem,
        hasMalus: editEval.value.Malus,
        time: editEval.value.time,
        idQuizz: editEval.value.qcm,
        idGroup: editEval.value.groupe
      },
    })
  });

  if (!response.ok) {
    throw new Error("Erreur API " + response.status);
  }

  showEditDialog.value = false
  await loadExams()
}

const columns = [
  { name: 'nom', label: 'Nom', align: 'left', field: 'nom' },
  { name: 'date', label: 'Date', align: 'left', field: 'date' },
  { name: 'qcmLabel', label: 'QCM', align: 'left', field: 'qcmLabel' },
  { name: 'groupeLabel', label: 'Groupe', align: 'left', field: 'groupeLabel' },
  { name: 'status', label: 'Status', align: 'left', field: 'status' },
  { name: 'reussite', label: '%Réussite', align: 'left', field: 'reussite' },
  { name: 'Code', label: 'Code', align: 'left', field: 'Code' },
  { name: 'action', label: 'Action', align: 'center', style: 'width:120px', headerStyle: 'width:120px' }
]

const groupOptions = ref([])
const quizzOptions = ref([])

function generateCode() {
    const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let result = '';
    for (let i = 0; i < 6; i++) {
        const randomIndex = Math.floor(Math.random() * characters.length);
        result += characters.charAt(randomIndex);
    }
    return result;
}

function generateAndSetCode() {
    newEval.value.Code = generateCode();
}

function resetForm() {
    editedEval.value = null;
    newEval.value = {
        nom: '', time: 60, qcm: null, groupe: null, Code: '', idExam: null,
        status: '', Barem: false, Malus: false
    }
}

function openDialogAndGenerateCode() {
    resetForm();
    generateAndSetCode();
    showDialog.value = true;
}

async function loadGroups() {
  try {
    const token_user = Cookies.get('token_user')

    const res = await fetch('http://10.0.52.187:1337/api/groups', {
      headers: {
        "Content-Type": "application/json",
        "Authorization": `Bearer ${token_user}`
      }
    })
    const data = await res.json()
    groupOptions.value = data.data.map(item => ({
      label: item.name,
      value: item.id
    }))
  } catch (err) {
    console.error('Erreur chargement groupes:', err)
  }
}

async function loadQcm() {
  try {
    const token_user = Cookies.get('token_user')

    const res = await fetch('http://10.0.52.187:1337/api/quizzes', {
      headers: {
        "Content-Type": "application/json",
        "Authorization": `Bearer ${token_user}`
      }
    })
    const data = await res.json()
    quizzOptions.value = data.data.map(item => ({
      label: item.name,
      value: item.id
    }))
  } catch (err) {
    console.error('Erreur chargement groupes:', err)
  }
}

async function loadExams() {
  try {
    const token_user = Cookies.get('token_user')

    const response = await fetch('http://10.0.52.187:1337/api/exams?populate=*', {
      headers: {
        "Content-Type": "application/json",
        "Authorization": `Bearer ${token_user}`
      }
    })
    if (!response.ok) throw new Error('Erreur HTTP ' + response.status)
    const { data: exams } = await response.json()

    const responseTakeExam = await fetch(
    'http://10.0.52.187:1337/api/takeexams?populate=*',
    {
      headers: {
        "Content-Type": "application/json",
        "Authorization": `Bearer ${token_user}`
      }
    }
  )

  if (!responseTakeExam.ok) throw new Error('Erreur HTTP ' + responseTakeExam.status)
  const { data: TakeExams } = await responseTakeExam.json()

  rows.value = exams.map(exam => {
    const examID = exam.id

    const takeExamsForThisExam = TakeExams.filter(takeexam =>
      takeexam.idExam?.some(e => e.id === examID)
    )

    const nbParticipants = takeExamsForThisExam.length

    const moyennePourcent = nbParticipants > 0
      ? (
          takeExamsForThisExam.reduce((sum, t) => sum + (t.grade ?? 0), 0)
          / nbParticipants
          / 20
        ) * 100
      : 0

    return {
      Id: exam.id,
      nom: exam.name,
      date: exam.publishedAt,
      qcm: exam.idQuizz?.id ?? null,
      qcmLabel: exam.idQuizz?.name ?? '',
      groupe: exam.idGroup?.[0]?.id ?? null,
      groupeLabel: exam.idGroup?.map(g => g.name).join(', ') ?? '',
      status: exam.role,
      reussite: Math.round(moyennePourcent) + ' %',
      Code: exam.code,
      documentId: exam.documentId,
      time: exam.time
    }
  })
  } catch (err) {
    console.error('Impossible de charger les examens :', err)
  }
}

async function addEval() {
  const token_user = Cookies.get('token_user')
  const response = await fetch("http://10.0.52.187:1337/api/exams", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      'Authorization': `Bearer ${token_user}`
    },
    body: JSON.stringify({
      data: {
        name: newEval.value.nom,
        role: newEval.value.status,
        code: newEval.value.Code,
        scale: newEval.value.Barem,
        hasMalus: newEval.value.Malus,
        time: newEval.value.time,
        idQuizz: newEval.value.qcm,
        idGroup: newEval.value.groupe,
      },
    })
  });

  if (!response.ok) {
    throw new Error("Erreur API " + response.status);
  }
  const data = await response.json();

  await loadExams();
  showDialog.value = false
  console.log(newEval)

  return data;
}

function openEditDialog(row) {
    editEval.value = {
        idExam: row.id,
        nom: row.nom,
        time: row.time,
        qcm: row.qcm,
        groupe: row.groupe,
        status: row.status,
        Barem: row.Barem,
        Malus: row.Malus,
        documentId: row.documentId
    };

    showEditDialog.value = true;
}

async function deleteRow(row) {
  const token_user = Cookies.get('token_user')
  const documentId = row.documentId;

  const response = await fetch(`http://10.0.52.187:1337/api/exams/${documentId}`, {
    method: 'DELETE',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token_user}`
    }
  });

   if (!response.ok) {
    throw new Error(`Erreur DELETE ${response.status}`)
  }

  await loadExams();
}


onMounted(async () => {
  verifyRole("admin", "/AccueilU")
  await loadGroups()
  await loadQcm()
  await loadExams()
})
</script>


<style scoped>
.text-purple-12 { color: var(--q-color-purple-12); }
.bg-rose { background-color: #FFF4FF; }
</style>
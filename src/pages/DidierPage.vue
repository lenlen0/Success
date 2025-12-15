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
            <q-input v-model="newUser.Username" rounded outlined label="Username" class="col-11" />
            <q-input v-model="newUser.Email" rounded outlined label="Email" class="col-11" />
            <q-input v-model="newUser.PWD" rounded outlined label="Mot de passe" class="col-11" />
            <q-select
              rounded
              standout="bg-grey-1"
              v-model="newUser.role"
              :options="['admin', 'logged_user']"
              label="Role"
              class="col-11"
            />
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn unelevated rounded color="purple-7" label="Annuler" v-close-popup />
          <q-btn
          unelevated
          rounded
          color="purple-7"
          label="Ajouter"
          @click="addUser()"
        />

        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Dialog pour modifier un utilisateur -->
    <q-dialog v-model="showEditDialog" persistent>
      <q-card style="min-width: 400px;">
        <q-card-section class="bg-purple-1 text-purple-10">
          <div class="text-h6">Modifier un utilisateur</div>
        </q-card-section>

        <q-card-section>
          <div class="row q-gutter-md">
            <h4 class="text-purple-10 q-my-md text-h6">ID de l'utilisateur : {{editUser.Id}}</h4>
            <q-input v-model="editUser.PWD" rounded outlined label="Mot de passe" class="col-11" />
            <q-input v-model="editUser.Nom" rounded outlined label="Nom" class="col-11" />
            <q-input v-model="editUser.Prenom" rounded outlined label="Prénom" class="col-11" />
            <q-select
              rounded
              standout="bg-grey-1"
              v-model="editUser.role"
              :options="['admin', 'student']"
              label="Role"
              class="col-11"
            />
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn unelevated rounded color="purple-7" label="Annuler" v-close-popup />
          <q-btn
          unelevated
          rounded
          color="purple-7"
          label="Modifier"
          @click="editUserFunc(editUser.Id, editUser.PWD, editUser.role, editUser.Prenom, editUser.Nom)"
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

// Colonnes du tableau
const columns = [
  { name: 'Id', label: 'ID', align: 'left', field: 'Id' },
  { name: 'Username', label: 'Username', align: 'left', field: 'Username' },
  { name: 'Email', label: 'E-mail', align: 'left', field: 'Email' },
  { name: 'role', label: 'Role', align: 'left', field: 'role' },
  {
    name: 'action',
    label: 'Actions',
    align: 'center',
    style: 'width: 120px; max-width: 120px;',
    headerStyle: 'width: 120px; max-width: 120px;'
  }
]

async function loadUsers() {
  try {
    const token_user = Cookies.get('token_user')

    const response = await fetch('http://10.0.52.187:1337/api/users?populate=*', {
      headers: {
        "Content-Type": "application/json",
        "Authorization": `Bearer ${token_user}`
      }
    })
    if (!response.ok) throw new Error('Erreur HTTP ' + response.status)

    const data = await response.json()

    // 🧩 Adapter les données à tes colonnes
    rows.value = data.map(item => ({
      Id: item.id,
      Username: item.username,
      Email: item.email,
      role: item.role.name.includes('admin') ? 'Admin'
        : item.role.name.includes('logged_user') ? 'Utilisateur'
        : 'Non enregistré'
    }))
  } catch (err) {
    console.error('Impossible de charger les utilisateurs :', err)
  }
}

// Nouvel utilisateur
const newUser = ref({
  Username: '',
  Email: '',
  PWD: '',
  role: ''
})

const editUser = ref({
  Id: '',
  Nom: '',
  Prenom: '',
  PWD: '',
  role: ''
})

async function addUser() {
  try {
    const token_user = Cookies.get('token_user')
    let roleid = 0;

    if (newUser.value.role === 'admin') {
      roleid = 3;
    } if (newUser.value.role === 'logged_user') {
      roleid = 1;
    } else {
      throw new Error('Rôle invalide')
    }

    const response = await fetch("http://10.0.52.187:1337/api/users", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "Authorization": `Bearer ${token_user}`
      },
      body: JSON.stringify({
        username: newUser.value.Username,
        email: newUser.value.Email,
        password: newUser.value.PWD,
        confirmed: true,
        blocked: false,
        role: roleid
      })
    });

    if (!response.ok) {
      throw new Error("Erreur API " + response.status);
    }

    const data = await response.json();
    console.log("Utilisateur ajouté :", data);

    await loadUsers()

    return data;

  } catch (err) {
    console.error("Erreur", err);
  }
}

function editRow(row) {
  editUser.value = {
    Id: row.Id,
    Nom: row.Nom,
    Prenom: row.Prenom,
    PWD: row.PWD,
    role: row.role
  }
  showEditDialog.value = true
}

async function editUserFunc(id, pwd, role, prenom, nom) {
  try {
    const response = await fetch(`http://10.0.52.142/success/api.php/show_user/${id}`)
    if (!response.ok) throw new Error('Erreur HTTP ' + response.status)

    const data = await response.json();
    if(data[0].pwd === pwd) {
      // EditUser sans modification du mdp
      await EditUserNoPasswordEdit(id, role, prenom, nom);
    } else {
      //EditUser modification mdp
      await EditUserWPasswordEdit(id, pwd, role, prenom, nom);
    }

  } catch (err) {
    console.error('Impossible de charger les utilisateurs :', err)
  }
}

async function EditUserNoPasswordEdit(id, role, prenom, nom) {
  try {
    const response = await fetch("http://10.0.52.142/success/api.php/modify_user_with_no_password_change", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({
        id_s11: id,
        role: role,
        firstname: prenom,
        lastname: nom
      })
    });

    if (!response.ok) {
      throw new Error("Erreur API " + response.status);
    }

    const data = await response.json();
    console.log("Utilisateur modifié :", data);

    if (data.status === "success") {
      showEditDialog.value = false
      await loadUsers()
    }

    return data;

  } catch (err) {
    console.error("Erreur", err);
  }
}

async function EditUserWPasswordEdit(id, pwd, role, prenom, nom) {
  try {
    const response = await fetch("http://10.0.52.142/success/api.php/modify_user_with_password_change", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({
        id_s11: id,
        pwd: pwd,
        role: role,
        firstname: prenom,
        lastname: nom
      })
    });

    if (!response.ok) {
      throw new Error("Erreur API " + response.status);
    }

    const data = await response.json();
    console.log("Utilisateur modifié :", data);

    if (data.status === "success") {
      showEditDialog.value = false
      await loadUsers()
    }

    return data;

  } catch (err) {
    console.error("Erreur", err);
  }
}

async function deleteRow(row) {
  try {
    const token_user = Cookies.get('token_user')
    const id = row.Id;

    const response = await fetch(`http://10.0.52.187:1337/api/users/${id}`, {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token_user}`
      }
    });

    if (!response.ok) {
    throw new Error(`Erreur DELETE ${response.status}`)
  }
    console.log(response)

    await loadUsers();
  } catch (err) {
    console.error('Impossible de supprimer utilisateur :', err);
  }
}


// Chargement depuis ton API
onMounted(async () => {
  verifyRole("admin", "/AccueilU")
  await loadUsers();
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

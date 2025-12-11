<template>
  <q-page class="flex flex-center" style="background-color: #FFF4FF;">
      <div class="flex-center text-center">
        <h1 class="text-purple-12 q-mb-xs">Success</h1>
        <p class="q-mb-xl text-subtitle1 text-grey-9">Page de connexion</p>
        <div style="width: 400px;">
          <q-input rounded outlined v-model="login.username" label="Identifiant s11" class="col q-mb-md"/>
          <q-input rounded outlined v-model="login.password" type="password" label="Mot de passe" class="col q-mb-xl"/>
          <q-btn unelevated rounded color="purple-7" label="Connexion" class="q-px-xl q-py-sm" @click="loginFunction(login.username, login.password)"/>
        </div>
      </div>
  </q-page>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { Cookies } from 'quasar';
import { useRouter } from 'vue-router';

const router = useRouter();

const login = ref([])

async function loginFunction(username, password) {
  try {
    const response = await fetch("http://10.0.52.187:1337/api/auth/local", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({
        identifier: username,
        password: password
      })
    });

    if (!response.ok) {
      throw new Error("Erreur API " + response.status);
    }

    const data = await response.json();

    Cookies.set('token_user', data.jwt, { expires: 7 })
    const value = Cookies.get('token_user')
    console.log(value)

    const user = await getMyslfInfo();

    const role = user.role.name

    if(role == "logged_user") {
      router.push({
        path: '/AccueilU'
      });
    } else if (role == "admin") {
      router.push({
        path: '/Accueil'
      });
    } else {
      router.push({
        path: '/'
      });
    }

    return data;

  } catch (err) {
    console.error("Erreur", err);
  }
}

async function getMyslfInfo() {
  try {
    const token_user = Cookies.get('token_user')
    const response = await fetch('http://10.0.52.187:1337/api/users/me?populate=role', {
      headers: {
        'Authorization': `Bearer ${token_user}`,
      }
    });
    if (!response.ok) throw new Error('Erreur HTTP ' + response.status);

    const data = await response.json()
    return data;
  } catch (err) {
    console.error('Impossible de charger les utilisateurs :', err)
  }
}

onMounted(async () => {
  const existingCookie = Cookies.get('token_user')
  if(existingCookie) {
    Cookies.remove('token_user')
  }
})

</script>

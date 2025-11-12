<template>
    <q-layout view="lHh Lpr lFf">
      <q-header elevated class="bg-purple-7 glossy text-white">
        <q-toolbar>
          <q-btn
            flat
            dense
            round
            icon="menu"
            aria-label="Menu"
            @click="toggleLeftDrawer"
          />

          <q-toolbar-title>
            Success
          </q-toolbar-title>
        </q-toolbar>
      </q-header>

      <q-drawer
        v-model="leftDrawerOpen"
        show-if-above
        style="background-color: #FFF4FF;"
      >
        <q-list>
          <q-item-label class="text-bold text-purple-10"
            header
          >
            Success
          </q-item-label>

          <EssentialLink
            v-for="link in linksList"
            :key="link.title"
            v-bind="link"
          />
        </q-list>
      </q-drawer>

      <q-page-container>
        <router-view />
      </q-page-container>
    </q-layout>
</template>
<script setup>
import { ref } from 'vue'
import EssentialLink from 'components/EssentialLink.vue'

const linksList = [
  {
    title: 'Accueil',
    caption: "Page d'accueil",
    icon: 'home',
    link: 'http://localhost:9000/#/'
  },
  {
    title: 'Examens',
    caption: 'Voir historique examens',
    icon: 'checklist',
    link: 'http://localhost:9000/#/Exam'
  },
  {
    title: 'Questionnaires',
    caption: 'Voir et créer les QCM',
    icon: 'assignment',
    link: 'http://localhost:9000/#/Quizz'
  },
  {
    title: 'Groupes',
    caption: 'Gérer les groupes',
    icon: 'groups',
    link: 'http://localhost:9000/#/Group'
  },
  {
    title: 'Utilisateurs',
    caption: 'Gérer les utilisateurs',
    icon: 'account_circle',
    link: 'https://twitter.quasar.dev'
  },
  {
    title: 'Déconnexion',
    caption: 'Quitter vers le portail de connexion',
    icon: 'logout',
    link: 'https://www.urssaf.fr/accueil.html'
  }
]

const leftDrawerOpen = ref(false)

function toggleLeftDrawer () {
  leftDrawerOpen.value = !leftDrawerOpen.value
}
</script>

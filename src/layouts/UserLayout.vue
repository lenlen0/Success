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
    link: 'http://localhost:9000/#/AccueilU'
  },
  {
    title: 'Mes Evaluations',
    caption: 'Voir historique examens',
    icon: 'checklist',
    link: 'http://localhost:9000/#/ExamU'
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

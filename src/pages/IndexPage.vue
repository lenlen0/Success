<template>
  <q-page class="flex flex-center" style="background-color: #FFF4FF;">
      <div class="flex-center text-center">
        <h1 class="text-purple-12 q-mb-xs">Success</h1>
        <p class="q-mb-xl text-subtitle1 text-grey-9">Page de connexion</p>
        <div style="width: 400px;">
          <q-input rounded outlined v-model="text" label="Identifiant s11" class="col q-mb-md"/>
          <q-input rounded outlined v-model="text" label="Mot de passe" class="col q-mb-xl"/>
          <q-btn unelevated rounded color="purple-7" label="Connexion" class="q-px-xl q-py-sm"/>
        </div>
      </div>
  </q-page>
</template>

<script setup>

</script>

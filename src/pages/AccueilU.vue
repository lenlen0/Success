<template>
  <q-page class="flex flex-center" style="background-color: #FFF4FF;">
    <div class="flex justify-evenly" style="gap: 40px;">
      <q-card class="my-card q-py-xl" style="background-color: #FFF4FF;">
        <q-card-section>
          <div class="flex-left text-center">
            <p class="q-mb-md text-h4 text-purple-12">Entrer votre code</p>
            <div style="width: 400px;">
              <q-input rounded outlined v-model="text" label="Exemple : P9LN01" class="col q-mb-md"/>
              <q-btn unelevated rounded color="purple-7" label="Entrer" class="q-px-xl q-py-sm"/>
            </div>
          </div>
        </q-card-section>
      </q-card>
      <q-card class="my-card q-py-xl" style="background-color: #FFF4FF;">
        <q-card-section>
          <div class="flex-center text-center">
            <p class="q-mb-md text-h4 text-purple-12">Statistiques</p>
            <p class="q-mb-xl text-subtitle1 text-grey-9">A compléter...</p>
          </div>
        </q-card-section>
      </q-card>
    </div>
  </q-page>
</template>
<script setup>

</script>
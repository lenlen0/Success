<template>
  <q-page class="flex flex-center" style="background-color: #FFF4FF;">
    <div class="flex justify-evenly" style="gap: 40px;">
      <q-card class="my-card q-py-xl" style="background-color: #FFF4FF;">
        <q-card-section>
          <div class="flex-left text-center">
            <p class="q-mb-md text-h4 text-purple-12">Entrer votre code</p>
            <div style="width: 400px;">
              <q-input rounded outlined v-model="text" label="Exemple : P9LN01" class="col q-mb-md" @keyup.enter="verifyCode"/>
              <q-btn unelevated rounded color="purple-7" label="Entrer" class="q-px-xl q-py-sm" @click="verifyCode" :loading="loading"/>
            </div>
            <q-banner v-if="errorMessage" class="bg-negative text-white q-my-md rounded-borders">
              <template v-slot:avatar>
                <q-icon name="warning" />
              </template>
              {{ errorMessage }}
            </q-banner>
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
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const text = ref('');
const loading = ref(false);
const errorMessage = ref('');

const verifyCode = async () => {
  if (!text.value.trim()) {
    errorMessage.value = 'Veuillez entrer un code';
    return;
  }

  loading.value = true;
  errorMessage.value = '';

  try {
    const response = await fetch('http://10.0.52.142/success/api.php/show_exam', {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json'
      }
    });

    const data = await response.json();

    if (response.ok && Array.isArray(data)) {
      // Chercher l'examen avec le code saisi
      const exam = data.find(e => String(e.code) === text.value.trim());

      if (exam) {
        // Redirection vers la page Pexam avec les paramètres
        router.push({
          path: '/Pexam',
          query: {
            idExam: exam.idExam,
            idQuizz: exam.idQuizz
          }
        });
      } else {
        errorMessage.value = 'Code d\'examen invalide';
      }
    } else {
      errorMessage.value = 'Erreur lors de la récupération des examens';
    }
  } catch (error) {
    console.error('Erreur lors de la vérification du code:', error);
    errorMessage.value = 'Une erreur est survenue lors de la vérification du code';
  } finally {
    loading.value = false;
  }
};
</script>

const routes = [
  {
    path: '/',
    component: () => import('layouts/NavbarLayout.vue'),
    children: [
      { path: '', component: () => import('pages/IndexPage.vue') }
    ]
  },

  {
    path: '/exam',
    component: () => import('layouts/NavbarLayout.vue'),
    children: [
      { path: '', component: () => import('pages/ExamPage.vue') }
    ]
  },

  {
    path: '/quizz',
    component: () => import('layouts/NavbarLayout.vue'),
    children: [
      { path: '', component: () => import('pages/QuizzPage.vue') }
    ]
  },

  {
    path: '/question',
    component: () => import('layouts/NavbarLayout.vue'),
    children: [
      { path: '', component: () => import('pages/QuestionPage.vue') }
    ]
  },

  {
    path: '/group',
    component: () => import('layouts/NavbarLayout.vue'),
    children: [
      { path: '', component: () => import('pages/GroupPage.vue') }
    ]
  },


  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/ErrorNotFound.vue')
  }
]

export default routes

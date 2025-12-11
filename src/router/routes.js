const routes = [
  {
    path: '/',
    component: () => import('layouts/AdminLayout.vue'),
    children: [
      { path: '', component: () => import('pages/IndexPage.vue') }
    ]
  },

  {
    path: '/exam',
    component: () => import('layouts/AdminLayout.vue'),
    children: [
      { path: '', component: () => import('pages/ExamPage.vue') }
    ]
  },

  {
    path: '/quizz',
    component: () => import('layouts/AdminLayout.vue'),
    children: [
      { path: '', component: () => import('pages/QuizzPage.vue') }
    ]
  },

  {
    path: '/question',
    component: () => import('layouts/AdminLayout.vue'),
    children: [
      { path: '', component: () => import('pages/QuestionPage.vue') }
    ]
  },

  {
    path: '/group',
    component: () => import('layouts/AdminLayout.vue'),
    children: [
      { path: '', component: () => import('pages/GroupPage.vue') }
    ]
  },

  {
    path: '/matchUser',
    component: () => import('layouts/AdminLayout.vue'),
    children: [
      { path: '', component: () => import('pages/MatchUserGroup.vue') }
    ]
  },

  {
      path: '/Utilisateurs',
      component: () => import('layouts/AdminLayout.vue'),
      children: [
        { path: '', component: () => import('pages/DidierPage.vue') }
      ]
  },

  {
      path: '/ExamU',
      component: () => import('layouts/UserLayout.vue'),
      children: [
        { path: '', component: () => import('pages/ExamU.vue') }
      ]
  },

  {
      path: '/AccueilU',
      component: () => import('layouts/UserLayout.vue'),
      children: [
        { path: '', component: () => import('pages/AccueilU.vue') }
      ]
  },

  {
    path: '/Accueil',
    component: () => import('layouts/AdminLayout.vue'),
    children: [
      { path: '', component: () => import('pages/AccueilPage.vue') }
    ]
  },

  {
      path: '/Pexam',
      component: () => import('layouts/UserLayout.vue'),
      children: [
        { path: '', component: () => import('pages/Pexam.vue') }
      ]
    },

    {
      path: '/PexamE',
      component: () => import('layouts/UserLayout.vue'),
      children: [
        { path: '', component: () => import('pages/PexamE.vue') }
      ]
    },

  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/ErrorNotFound.vue')
  }
]

export default routes

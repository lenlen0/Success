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
    path: '/:catchAll(.*)*',
    component: () => import('pages/ErrorNotFound.vue')
  }
]

export default routes
